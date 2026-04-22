Changelog:
===========

# Validation des présences par le référent 

## Architecture

```
Session passée
    │
    ▼
cron quotidien  ─── php index.php cron send_ref_validation_mails
    │
    ├── pour chaque travaux sans ref_mail_sent_at
    │     ├── génère un token (validation_tokens)
    │     ├── insère mail (sendmail_model)
    │     └── marque la session comme notifiée
    │
    ▼
cron sendmail  ─── php index.php cron sendmail  (déjà existant)
    │
    ▼
Email reçu par le référent → clic sur le lien
    │
    ▼
/Admwork_controller/validate_by_token/abc123...
    │   (route GUEST, pas besoin de login)
    │   ↳ vérifie le token dans validation_tokens
    ▼
Vue validate_one_ref
    │   ↳ coche présents, saisit unités, commentaires, no-shows
    ▼
POST → écrit dans `infos` + marque le token comme utilisé
    │
    ▼
Validation finale par un `sys` via Units_controller/valid (flux existant)
```

## Points d'attention

### 🔑 Jointure famille ↔ membre de commission
J'utilise la jointure par email (`famille.e_mail = groupes_member.email`). **C'est fragile** :
- un parent avec 2 emails différents ne matchera pas
- toute typo casse le lien

Si vous avez déjà, ou pouvez ajouter, une colonne `groupes_member.id_fam` pointant sur `famille.id`, c'est **infiniment plus fiable**. Il suffit de remplacer dans `Admwork_model_additions.php` :
```sql
-- Actuel
LOWER(famille.e_mail) = LOWER(groupes_member.email)
-- Mieux
famille.id = groupes_member.id_fam
```

### 🔒 Sécurité du token
- Généré avec `random_bytes(32)` → 256 bits d'entropie, non devinable
- Unique en base (contrainte SQL)
- Expiration à 30 jours
- Le token n'est **consommé** qu'au POST final : le référent peut revenir plusieurs fois tant qu'il n'a pas soumis (pratique s'il veut corriger). À ajuster si vous préférez un usage strictement unique.

### 📨 Template d'email
Le template actuel (dans `Cron_additions.php`) est en texte brut. Si vous utilisez déjà `Templates_controller` pour vos emails, remplacez la construction `$subject`/`$message` par un appel à votre moteur de templates.

### ✉️ Bounce / mauvais email
Si l'email du référent est invalide, aujourd'hui le token reste en BDD mais la session est marquée `ref_mail_sent_at`. Vous pouvez :
- soit ne pas marquer la session si l'email échoue (le cron ré-essaiera)
- soit prévoir une alerte au bureau via `Sendmail_statut` en statut 2 (erreur) — votre code existant le gère déjà.

### 🧪 Test
Avant de passer en prod, testez sur `regio.dev-asso.fr` (votre env develop) :
1. Créez une session test avec une date passée
2. Lancez manuellement `php index.php cron send_ref_validation_mails`
3. Vérifiez la table `validation_tokens` et `sendmail`
4. Cliquez le lien dans le mail reçu
5. Vérifiez que le POST met bien à jour `infos`


# Framework CI3 — Optimisations v3 (sécurité d'authentification)

Cette version **corrige les régressions de la v2** sur l'authentification
multi-tables (admin + famille) et le SSO Delta.

## Fichiers modifiés / créés

| Fichier | Statut | Emplacement cible |
|---|---|---|
| `PasswordAuthenticator.php` | 🆕 Nouveau | `application/libraries/` |
| `Acl_users_model.php` | ✏️ Réécrit | `application/models/` |
| `Familys_model.php` | ✏️ Réécrit | `application/models/` |
| `Auth.php` | ✏️ Réécrit | `application/libraries/` |
| `Acl.php` | ✏️ Réécrit | `application/libraries/` |
| `element_password.php` | ✏️ Réécrit | `application/libraries/elements/` |

---

## Régressions v2 corrigées

### 🔴 R1 — La v2 cassait la connexion des familles

La v2 a migré `Acl_users_model::verifyLogin()` vers `password_verify()` mais
a laissé `Familys_model::verifyLogin()` sur son `crypt() + PASSWORD_SALT` /
fallback MD5 d'origine. Par ailleurs, `Acl::CheckLogin()` n'appelait que
`Acl_users_model::verifyLogin()` — donc sur l'interface web, **aucune
famille ne pouvait se connecter après la v2**.

**Correction v3** : la logique bcrypt + migration est mutualisée dans
`PasswordAuthenticator`. Les deux modèles l'utilisent, avec `allowMd5 = TRUE`
uniquement pour la table `famille` (compatibilité historique). `Acl::CheckLogin()`
délègue maintenant à `Auth::Login()` qui fait la cascade admin → famille.

### 🔴 R2 — Incohérence array/stdClass entre modèles et Auth::Login

La v2 `Acl_users_model::verifyLogin()` renvoyait un `stdClass` alors que
`Auth::Login` lisait `$row['role_id']`, `$row['id']`, `$row['login']` (syntaxe
array). Résultat : `if ($row)` retournait `TRUE` sur l'objet non vide, mais les
champs extraits étaient `NULL` → `connected_user->autorize = TRUE` avec `id` et
`role_id` à `NULL`.

**Correction v3** : signature unifiée en `stdClass` dans les deux modèles et
dans `verifyLoginAPI()`. `Auth::Login` consomme exclusivement ce format.

### 🔴 R3 — Le case DELTA sabotait la migration bcrypt

Chaque login Delta réinjectait en base un hash `crypt($password, PASSWORD_SALT)`,
annulant silencieusement la migration bcrypt qui venait d'avoir lieu lors du
login précédent.

**Correction v3** : `Auth::_syncFamilyFromDelta()` et `_createFamilyFromDelta()`
utilisent `PasswordAuthenticator::hash()` (bcrypt). Les deux branches (mise à
jour / création) sont factorisées.

### 🟠 R4 — role_famille hardcodé

La v2 hardcodait `$role_famille = 2` dans `Auth` sans équivalent dans
`Familys_model`. Si la config métier change, deux endroits à modifier.

**Correction v3** : les deux classes lisent `config_item('role_famille')` avec
fallback sur 2. Ajouter dans `application/config/config.php` :
```php
$config['role_famille'] = 2;
```

---

## Ce que v3 préserve de v2 (points toujours valides)

- `$DontCheck = FALSE` par défaut dans `Acl` (secure by default).
- Cache ACL en session par `role_id` (1 requête SQL au lieu d'une par page).
- Migration transparente des anciens hashes à la connexion (mais généralisée
  aux familles, y compris MD5).
- `element_password` en bcrypt.
- `verifyLogin()` de `Acl_users_model` résout réellement l'accès (bug
  originel corrigé).

---

## Cascade d'authentification après v3

```
Formulaire web (Home/login)
       │
       ▼
Acl::CheckLogin($data)
       │
       ▼
Auth::Login($data)
       │
       ├─ type_cnx = NORM ──► _loginNormal()
       │                        │
       │                        ├─ Acl_users_model::verifyLogin()  (sys)
       │                        │     └─ PasswordAuthenticator::verify('acl_users')
       │                        │
       │                        └─ Familys_model::verifyLogin()    (fam, fallback)
       │                              └─ PasswordAuthenticator::verify('famille', allowMd5=TRUE)
       │
       └─ type_cnx = DELTA ─► _loginDelta()
                                │
                                ├─ restclient::get(delta-enfance3.fr)
                                └─ Familys_model::verifyLoginAPI() puis
                                   _syncFamilyFromDelta() OU _createFamilyFromDelta()
                                   (mot de passe stocké en bcrypt)
```

`Auth::Login` retourne `$connected_user` (stdClass) et positionne le JWT.
`Acl::CheckLogin` synchronise `usercheck` en session pour l'interface web.

---

## Signature stdClass retournée par verifyLogin / verifyLoginAPI

```
stdClass {
    autorize : bool         // TRUE si auth réussie
    type     : 'sys'|'fam'|'none'
    login    : string       // login saisi (vide si échec)
    name     : string       // nom affiché (nom famille ou login admin)
    id       : int          // id en base
    role_id  : int          // role_id effectif (fallback default_family_role_id)
}
```

---

## Migration des mots de passe

Aucun script SQL obligatoire. La migration est **automatique** lors de la
prochaine connexion de chaque utilisateur (gérée dans
`PasswordAuthenticator::verify()`) :

- Hash legacy `crypt()` → bcrypt
- Hash legacy MD5 (familles uniquement) → bcrypt
- Hash bcrypt à coût obsolète → rehash bcrypt

**Prérequis** : la colonne `password` doit pouvoir stocker 60 caractères.

```sql
ALTER TABLE acl_users MODIFY COLUMN password VARCHAR(255) NOT NULL;
ALTER TABLE famille   MODIFY COLUMN password VARCHAR(255) NOT NULL;
```

Pour lister les comptes encore en hash legacy à un instant T :

```sql
SELECT id, login FROM acl_users
 WHERE password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%';

SELECT id, login FROM famille
 WHERE password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%';
```

---

## À tester manuellement avant déploiement

1. Connexion admin (acl_users) avec hash legacy → doit migrer en bcrypt
2. Connexion admin avec hash bcrypt → doit marcher et ne pas re-hasher
3. Connexion famille (famille) avec hash legacy crypt → doit migrer
4. Connexion famille avec hash MD5 historique → doit migrer
5. Connexion famille avec mauvais mot de passe → doit échouer (plus de bypass)
6. Connexion API POST `/api/login` avec `type_cnx=NORM` admin → JWT renvoyé
7. Connexion API POST `/api/login` avec `type_cnx=NORM` famille → JWT renvoyé
8. Connexion Delta famille existante → update, password stocké en bcrypt
9. Connexion Delta famille inexistante → création, password en bcrypt
10. Tentative d'accès à une page protégée sans session → redirect `/Home/login`

### UNRELEASED
* Json Schéma pour la Base de Données
* Feeders
* Object de rendu

