# Documentation — `site_travaux`

> Application web de gestion des travaux associatifs et de la participation des familles, pour l'association ABCM Mulhouse-Lutterbach.

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Stack technique](#2-stack-technique)
3. [Architecture du projet](#3-architecture-du-projet)
4. [Concepts métier](#4-concepts-métier)
5. [Modèle de données](#5-modèle-de-données)
6. [Authentification & ACL](#6-authentification--acl)
7. [Modules fonctionnels](#7-modules-fonctionnels)
8. [Patterns techniques (factory, schémas JSON)](#8-patterns-techniques)
9. [API REST](#9-api-rest)
10. [Cron & tâches planifiées](#10-cron--tâches-planifiées)
11. [Configuration & environnements](#11-configuration--environnements)
12. [Déploiement (Git Flow)](#12-déploiement-git-flow)
13. [Annexes](#13-annexes)

---

## 1. Vue d'ensemble

### 1.1 Contexte métier

L'application gère les **travaux** auxquels les familles d'une école associative s'inscrivent pour valider des **unités de participation**. Elle couvre :

- l'inscription des familles à des sessions de travaux, ménage, goûter, lavage, déchetterie, etc. ;
- la validation a posteriori par un référent de la session ;
- la garde du midi (cantine) sous forme d'agenda hebdomadaire ;
- la gestion des commissions (organigramme), de leurs membres et de leurs candidats ;
- l'envoi d'emails (rappels, notifications) via une file d'attente et un cron.

Les utilisateurs ont l'un des trois types : **admin** (`sys`), **famille** (`fam`) ou **invité** (`none`).

### 1.2 Licence et auteurs

- Framework : CodeIgniter 3, MIT License (© British Columbia Institute of Technology)
- Surcouches maison : © Tmile, 2018
- Évolutions métier : association ABCM Mulhouse-Lutterbach

---

## 2. Stack technique

| Composant | Version / Détail |
|---|---|
| Langage | PHP 7.4+ |
| Framework | CodeIgniter 3 |
| Base de données | MySQL 5.7 (utf8 / latin1 selon les tables) |
| Auth | bcrypt + JWT (Firebase\JWT) + SSO Delta Enfance |
| Front | HTML/CSS/JS, framework "Nicdark" (templates) |
| Mail | CI_Email + SMTP, file d'envoi via cron |
| Build | Pas de build front (assets servis statiquement) |

---

## 3. Architecture du projet

### 3.1 Arborescence

```
site_travaux/
├── application/
│   ├── core/
│   │   └── MY_Controller.php       # CRUD générique (extension de CI_Controller)
│   ├── controllers/
│   │   ├── Home.php                # login, logout, tableau de bord
│   │   ├── Admwork_controller.php  # travaux : inscription, validation, gestion
│   │   ├── Cantine_controller.php  # garde du midi
│   │   ├── Units_controller.php    # validation des unités par admin
│   │   ├── Familys_controller.php  # gestion familles + édition profil
│   │   ├── Orgchart_controller.php # commissions et organigramme
│   │   ├── Cron.php                # tâches planifiées (sendmail, ref_validation)
│   │   ├── Api.php                 # API REST + JWT
│   │   └── Acl_*_controller.php    # admin des rôles, contrôleurs, actions
│   ├── models/
│   │   ├── Core_model.php          # base : get_one, get_all, post, put, delete
│   │   ├── *_model.php             # modèles métiers
│   │   └── json/
│   │       └── *.json              # schémas de table (champs, règles, dbforge)
│   ├── libraries/
│   │   ├── Acl.php                 # autorisations + cascade auth
│   │   ├── Auth.php                # factory d'auth (web + API + Delta SSO)
│   │   ├── PasswordAuthenticator.php # bcrypt + migration legacy
│   │   ├── Render_object.php       # rendu factory à partir des schémas JSON
│   │   ├── Bootstrap_tools.php     # helpers d'affichage (couleurs, design)
│   │   ├── Libpdf.php              # génération de PDF
│   │   └── elements/element_*.php  # éléments de formulaire (input, select, etc.)
│   ├── hooks/
│   │   └── Loginchecker.php        # hook ACL avant chaque action
│   ├── language/french/            # i18n (clés métier + clés CI)
│   ├── migrations/                 # SQL manuels (Migration.sql, mig_*.sql)
│   ├── config/
│   │   ├── app.php                 # config non-sensible (versionnée)
│   │   ├── secured.php             # SMTP, API_KEY, PASSWORD_SALT (non versionné)
│   │   └── development|production/ # surcharges par environnement
│   └── views/
│       ├── template/               # head.php, footer.php (layout)
│       ├── edition/                # formulaires
│       └── unique/                 # vues spécifiques par contrôleur
├── assets/
│   ├── css/                        # admwork_register.css, ...
│   ├── js/
│   └── vendor/                     # bibliothèques tierces
├── system/                         # CI3 (vendor framework)
├── public/files/                   # uploads (img/team, ...)
├── .gitignore
├── .htaccess
├── README.md
├── CHANGELOG.md
└── index.php
```

### 3.2 Points forts architecturaux

1. **Approche factory par schéma JSON.** Chaque table a un fichier JSON (`Infos.json`, `Acl_users.json`, …) qui décrit les champs, leurs règles de validation, leur type de rendu (input, select, select_database, hidden, table liée…) et leur définition `dbforge`. `Core_model` et `Render_object` exploitent ces schémas pour générer formulaires, listes, vues et validations sans code spécifique. Très DRY pour un back-office.

2. **CRUD générique via `MY_Controller`.** Les contrôleurs déclarent simplement `_controller_name`, `_model_name`, `_edit_view`, `_list_view`, `_autorize` puis appellent `init()`. Le routage `add` / `edit` / `list` / `delete` / `view` est géré par la classe mère.

3. **ACL centralisée et cachée en session.** Le hook `Loginchecker` appelle `acl->Route()` avant chaque action. Les permissions sont mises en cache par `role_id` dans la session pour éviter une requête SQL à chaque page.

4. **Auth multi-source.** Cascade `acl_users` → `famille`, plus connexion via SSO Delta Enfance qui synchronise le compte famille local. Mots de passe en bcrypt avec migration automatique des hashes legacy au prochain login.

5. **API REST séparée** avec JWT, gestion fine des verbes HTTP (GET/POST/PUT/DELETE) et codes de retour normalisés (201, 202, 400, 403…).

---

## 4. Concepts métier

### 4.1 Vocabulaire

| Terme | Signification |
|---|---|
| **Travail / session** | Un événement auquel une famille s'inscrit (ligne dans `travaux`). |
| **Type de travail** | `TRA` (travaux), `MEN` (ménage), `GOU` (goûter), `LAV` (lavage), `DEC` (déchetterie), `INF` (informatique), `URG` (urgence, pas de date), `can` (cantine). |
| **Inscription / Info** | Une ligne dans `infos` = une famille inscrite à une session, avec un nombre de participants et d'unités prévues. |
| **Unité (associative)** | Crédit de participation. Chaque session définit `nb_units` ; multiplié par `nb_participants` à la validation. |
| **Référent** | Famille en charge d'animer la session et de valider les présences. Lien via `travaux.referent_travaux → trombi.id`. |
| **Commission** | Groupe thématique (Bureau, Communication, Travaux, …). Table `groupes`, type `com` ou `org`. |
| **École** | Code `M` (Mulhouse), `L` (Lutterbach), `B` (les deux). |
| **Année civile (`civil_year`)** | Année scolaire au format `2025-2026`. Cloisonne les données par campagne. |

### 4.2 Cycle de vie d'une session de travaux

```
1. Création par admin (sys) ─── travaux INSERT, statut=0 (brouillon)
              │
              ▼
2. Publication ─── statut=1, visible côté famille
              │
              ▼
3. Inscription famille ─── infos INSERT (id_famille, id_travaux, nb_participants)
              │
              ▼
4. Date de la session
              │
              ├── Mail référent (cron, J-7) ──► token + lien email
              ▼
5. Validation par référent ─── infos UPDATE (nb_unites_valides_effectif, présent…)
              │
              ▼
6. Contrôle final par admin ─── Units_controller/valid
              │
              ▼
7. Archivage automatique (cron, J+30) ─── travaux SET archived=1
```

---

## 5. Modèle de données

### 5.1 Tables principales

#### `famille`

Compte famille (utilisateur de type `fam`).

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Clé interne |
| `idfamille` | VARCHAR | Référence Delta Enfance |
| `login` | VARCHAR | Login local |
| `password` | VARCHAR(255) | bcrypt (legacy crypt/MD5 migrés au login) |
| `e_mail` | VARCHAR | Email principal (= login Delta) |
| `e_mail_comp` | VARCHAR | Lien vers `emails` (table liée) |
| `nom`, `prenom`, `cp`, `ville`, `adresse` | VARCHAR | Coordonnées |
| `ecole` | CHAR(1) | M, L ou B |
| `capacity` | VARCHAR | Compétences |
| `nb_enfants` | INT | |
| `civil_year` | VARCHAR | Année civile en cours |
| `role_id` | INT | Rôle ACL (défaut 2 = `role_famille`) |
| `created`, `updated` | DATETIME | |

#### `travaux`

Une session de travaux/ménage/goûter/etc.

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | |
| `titre` | VARCHAR | Libellé |
| `type` | VARCHAR | TRA / MEN / GOU / LAV / DEC / INF / URG / can |
| `date_travaux` | DATE | Sauf URG |
| `heure_deb_trav`, `heure_fin_trav` | TIME | |
| `nb_units` | FLOAT | Unités créditées par participation |
| `nb_inscrits_max` | INT | Plafond |
| `accespar` | CHAR(1) | École cible (M/L/B) |
| `referent_travaux` | INT | FK → `trombi.id` |
| `description`, `txtmodel` | TEXT | |
| `statut` | INT | 0 brouillon, 1 publié |
| `archived` | INT | 0 actif, 1 archivé |
| `civil_year` | VARCHAR | |
| `ref_mail_sent_at` | DATETIME | Marque l'envoi du mail au référent |
| `created`, `updated` | DATETIME | |

#### `infos`

Une inscription famille à une session.

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | |
| `id_famille` | INT FK | → `famille.id` |
| `id_travaux` | INT FK | → `travaux.id` |
| `nb_participants` | INT | |
| `type_participant` | VARCHAR | `Mr` / `Mme` / `Both` |
| `heure_debut_prevue`, `heure_fin_prevue` | TIME | |
| `nb_unites_valides` | FLOAT | Prévues |
| `nb_unites_valides_effectif` | FLOAT | Validées (par référent + admin) |
| `type_session` | INT | |
| `civil_year` | VARCHAR | |
| `created`, `updated` | DATETIME | |

#### `unites`

Crédits supplémentaires hors session.

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | |
| `id_fam` | INT | |
| `unites` | FLOAT | Quantité |
| `unites_comm` | TEXT | Commentaire |
| `type_session` | INT | |
| `civil_year` | VARCHAR | |
| `archived` | INT | |

#### `validation_tokens` *(nouveau)*

Tokens à durée limitée pour la validation par lien email.

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | |
| `id_travaux` | INT | |
| `id_fam_ref` | INT | |
| `token` | VARCHAR(64) | bin2hex(random_bytes(32)) |
| `expires_at` | DATETIME | Création + 30 jours |
| `used_at` | DATETIME NULL | Marqué à la soumission finale |
| `created` | DATETIME | |

#### `cantine_config`, `cantine_inscriptions` *(module Cantine)*

Voir `application/migrations/mig_cantine.sql`.

### 5.2 Tables ACL

| Table | Rôle |
|---|---|
| `acl_users` | Comptes admin (sys) |
| `acl_roles` | Rôles (admin, famille, …) |
| `acl_controllers` | Contrôleurs déclarés |
| `acl_actions` | Actions par contrôleur |
| `acl_roles_controllers` | Matrice rôle × action × allow |

### 5.3 Tables organigramme

| Table | Rôle |
|---|---|
| `groupes` | Commissions (`type='com'`) ou structure (`type='org'`) |
| `groupes_member` | Personne avec id_fam, name, surname, email, phone, picture |
| `trombi` | Affectation : id_grp, ref (= groupes_member.id), classif (RT, ME, BU, PE, VP) |
| `candidatures` | Candidatures à intégrer une commission |

### 5.4 Tables emails / file d'envoi

| Table | Rôle |
|---|---|
| `sendmail` | File d'envoi (reference, email, object, message, statut 0/1/2) |
| `sendmail_statut` | Log d'envois (id_sen, date, sendstatut, error) |
| `emails` | E-mails complémentaires liés à `famille` (id_fam) |

### 5.5 Chaîne référent → famille

Cette chaîne est centrale pour identifier le référent d'une session et lui envoyer les notifications.

```
travaux.referent_travaux (INT)
   = trombi.id
trombi.ref (VARCHAR contenant un id)
   = groupes_member.id
groupes_member.id_fam (VARCHAR contenant un id)
   = famille.id (INT)
```

> ⚠️ MySQL gère la conversion implicite VARCHAR ↔ INT pour les égalités. Si une valeur non numérique est stockée par erreur dans `id_fam`, la jointure ne remontera simplement aucune ligne (comportement souhaitable).

---

## 6. Authentification & ACL

### 6.1 Cascade d'authentification

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

### 6.2 Object `connected_user`

Standard partagé entre web et API :

```php
stdClass {
    autorize : bool         // TRUE si auth réussie
    type     : 'sys'|'fam'|'none'
    login    : string
    name     : string       // nom affiché
    id       : int
    role_id  : int
    msg      : string       // message info / erreur
    token    : string       // JWT pour l'API
    expireAt : int          // timestamp d'expiration
}
```

### 6.3 Migration des mots de passe

Au prochain login de chaque utilisateur, `PasswordAuthenticator::verify()` détecte le format et migre :

- Hash legacy `crypt()` (avec `PASSWORD_SALT`) → bcrypt
- Hash legacy MD5 (table `famille` uniquement) → bcrypt
- Hash bcrypt à coût obsolète → rehash bcrypt

Pour lister les comptes encore en hash legacy :

```sql
SELECT id, login FROM acl_users
 WHERE password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%';

SELECT id, login FROM famille
 WHERE password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%';
```

### 6.4 ACL et pages publiques (`guestPages`)

Définies dans `Acl::$guestPages` :

```php
[
  'home/logout', 'home/login', 'home/no_right',
  'home/index', 'home/myaccount', 'home/about',
  'home/maintenance', 'home',
  'admwork_controller/validate_by_token',  // accès référent par lien email
  'cron/send_ref_validation_mails'         // exécutable en CLI
]
```

Toute autre route requiert une session valide et un droit ACL (`role_id` × `controller` × `action`).

### 6.5 Configuration sécurisée

`application/config/secured.php` (non versionné) doit définir :

```php
define('API_KEY', '...');                    // clé HMAC pour JWT
define('PASSWORD_SALT', '...');              // sel legacy crypt() — encore lu pour la migration

$config['smtp_host']      = 'smtp.example.com';
$config['smtp_port']      = 587;
$config['smtp_user']      = '...';
$config['smtp_pass']      = '...';
$config['smtp_crypto']    = 'tls';
$config['mail_from_email'] = 'noreply@abcm.fr';
$config['mail_from_name']  = 'ABCM Mulhouse-Lutterbach';
$config['mail_reply_to']   = 'bureau@abcm.fr';
```

---

## 7. Modules fonctionnels

### 7.1 Travaux (`Admwork_controller`)

| Action | Acteur | Description |
|---|---|---|
| `register` | sys / fam | Liste des sessions à venir + filtres + cartes/liste |
| `register_one/$id` | fam | S'inscrire à une session |
| `validate_one/$id` | fam (référent) | Valider les présences (mode connecté) |
| `validate_by_token/$token` | invité | Valider via lien email (token 30j, public) |
| `my_sessions` | fam | Liste des sessions où l'utilisateur est référent |
| `list`, `add`, `edit`, `delete` | sys | CRUD admin |
| `worker` | sys | Statistiques participants |

### 7.2 Cantine (`Cantine_controller`)

Module de garde du midi sous forme d'agenda hebdomadaire.

| Action | Acteur | Description |
|---|---|---|
| `register/$week_offset` | sys / fam | Agenda lundi-vendredi pour une semaine |
| `register_one/$id_work` | fam | S'inscrire à un créneau |
| `unregister_one/$id_work` | fam | Se désinscrire (refusé si validé) |
| `config` | sys | Paramétrage : jours actifs, nb_slots, école, nb_units |
| `save_config` | sys | Sauvegarde |
| `generate` | sys | Génère les sessions sur une période donnée |

Chaque session cantine = ligne `travaux` de `type='can'`. Une inscription = ligne `infos`. La validation est faite par le référent via le flux `Units_controller/valid` existant.

### 7.3 Validation des unités (`Units_controller`)

| Action | Acteur | Description |
|---|---|---|
| `valid` | sys | Liste des unités en attente, validation en lot |
| `valids` | sys | Confirmation après sélection |
| `list` | sys / fam | Unités complémentaires (hors session) |

### 7.4 Familles (`Familys_controller`)

| Action | Acteur | Description |
|---|---|---|
| `histo` | fam | Mon compte, mes unités, mes sessions à venir |
| `histo` | sys | Sélection d'une famille pour consultation |
| `list`, `add`, `edit`, `delete` | sys | CRUD admin |
| `skills` | sys | Filtre par compétence |
| `stats` | sys | Synthèse unités par famille |
| `units/$id_fam` | sys | Détail des unités d'une famille |
| `check/$id_fam` | sys | Gestion des chèques de caution |

### 7.5 Organigramme (`Orgchart_controller`)

| Action | Acteur | Description |
|---|---|---|
| `orga` | tous | Vue publique des commissions |
| `list` | sys | Liste des commissions |
| `featured/$id` | sys | Met une commission en avant |
| `add`, `edit`, `delete` | sys | CRUD admin |

### 7.6 Mon compte (`Home`)

| Action | Description |
|---|---|
| `login` | Formulaire de connexion (NORM ou DELTA) |
| `logout` | Détruit la session |
| `myaccount` | Profil, change password, infos Delta |
| `no_right` | Page d'accès refusé |
| `maintenance` | Page hors-service (config `maintenance=true`) |

---

## 8. Patterns techniques

### 8.1 Schéma JSON d'une table

Chaque table a un fichier dans `application/models/json/<Model>.json`. Exemple raccourci pour `Familys.json` :

```json
{
  "id": {
    "type": "hidden", "list": true, "search": false, "rules": null,
    "dbforge": { "type": "INT", "constraint": 11, "auto_increment": true }
  },
  "nom": {
    "sql": "ALTER TABLE famille ADD nom VARCHAR(255) NULL AFTER login;",
    "type": "input", "list": true, "search": true, "rules": "trim|required",
    "dbforge": { "type": "VARCHAR", "constraint": "255" }
  },
  "ecole": {
    "type": "select", "list": true, "search": true,
    "values": { "M": "Mulhouse", "L": "Lutterbach", "B": "Les deux" },
    "dbforge": { "type": "VARCHAR", "constraint": "1" }
  },
  "e_mail_comp": {
    "type": "table", "model": "Email_model",
    "ref": "email", "foreignkey": "id_fam"
  }
}
```

#### Champs reconnus

- `type` : `input` / `select` / `select_database` / `hidden` / `password` / `memo` / `html` / `table` / `typeahead` / `modelmemo` / `created` / `updated`
- `list` : visible dans la vue liste
- `search` : indexable pour la recherche globale
- `rules` : règles CodeIgniter form_validation (`trim|required|valid_email`…)
- `since` : version d'apparition du champ
- `values` : map clé → libellé (pour `select`)
- `query` : requête SQL préchargée (pour `select_database`, `typeahead`)
- `param` : alias `distinct(table,id:label)` pour générer la requête
- `dbforge` : définition pour `dbforge::create_table`
- `sql` : ALTER d'ajout du champ (utilisé par migrations)

### 8.2 Modèle minimal (`Core_model`)

```php
class Familys_model extends Core_model {
    function __construct() {
        parent::__construct();
        $this->_set('table',     'famille');
        $this->_set('key',       'id');
        $this->_set('order',     'login');
        $this->_set('direction', 'desc');
        $this->_set('json',      'Familys.json');
    }
}
```

Méthodes héritées disponibles : `get_one()`, `get_all()`, `post()`, `put()`, `delete()`, `delete_bulk()`, `get_distinct()`, `is_exist()`, `query()`, `truncate()`.

### 8.3 Contrôleur minimal (`MY_Controller`)

```php
class Familys_controller extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->_controller_name = 'Familys_controller';
        $this->_model_name      = 'Familys_model';
        $this->_edit_view       = 'edition/Familys_form';
        $this->_list_view       = 'unique/Familys_view.php';
        $this->_autorize        = ['list'=>true, 'add'=>true, 'edit'=>true, 'delete'=>true, 'view'=>true];
        $this->title           .= $this->lang->line('GESTION_'.$this->_controller_name);
        $this->init();
    }
}
```

Rien d'autre à écrire pour avoir un CRUD fonctionnel sur la table `famille`.

### 8.4 Rendu d'un élément

Dans une vue :

```php
echo $this->bootstrap_tools->label('nom');
echo $this->render_object->RenderFormElement('nom');
```

ou en mode liste / vue :

```php
echo $this->render_object->RenderElement('nom', $data->nom);
```

`Render_object` choisit l'élément approprié dans `application/libraries/elements/element_<type>.php` selon `defs[champ]->type`.

### 8.5 Menus

Définis dans `application/models/json/Menus.json`. Les entrées portent une `opt` (`sys`, `fam`, ou null pour tous) qui filtre selon le type d'utilisateur.

---

## 9. API REST

### 9.1 Authentification API

`POST /api/login`

```json
{
  "login": "user@example.com",
  "password": "...",
  "api-key": "...",
  "type_cnx": "NORM"
}
```

Réponse 200 :

```json
{
  "message": "Successful login.",
  "jwt": "eyJ0eXAi...",
  "id": "1",
  "role_id": "1",
  "type": "sys",
  "expireAt": 1687366618,
  "expireAtRender": "2023-06-21 16:56:58"
}
```

### 9.2 Endpoints `/api/mails`

| Verbe | URL | Description |
|---|---|---|
| `POST` | `/api/mails` | Crée un mail (statut 0) |
| `GET` | `/api/mails` | Liste tous les mails |
| `GET` | `/api/mails/$id` | Récupère un mail |
| `PUT` | `/api/mails/$id` | Met à jour |
| `DELETE` | `/api/mails/$id` | Supprime |

Header obligatoire : `Authorization: Bearer <jwt>`.

Exemple `POST` :

```json
{
  "reference": "test",
  "email": "test@example.com",
  "message": "Hello",
  "object": "Test"
}
```

Réponse 201 :

```json
{ "id": 42 }
```

### 9.3 Codes de retour

| Code | Sens |
|---|---|
| 200 | OK (GET / DELETE) |
| 201 | Created (POST) |
| 202 | Accepted (PUT) |
| 204 | No content |
| 400 | Bad request (validation, ID manquant) |
| 401 | Logged out |
| 403 | Forbidden |
| 500 | Server error |

---

## 10. Cron & tâches planifiées

Les commandes sont à lancer via PHP CLI depuis la racine du projet.

### 10.1 `cron sendmail`

Pool d'envoi des emails en file (`sendmail` table).

```bash
php index.php cron sendmail [size=10]
```

- Récupère jusqu'à `$size` mails en statut 0
- Envoie via SMTP (config `secured.php`)
- Met à jour le statut (1 envoyé / 2 erreur)
- Log dans `sendmail_statut`

**Crontab recommandé** :

```cron
*/10 * * * *  cd /var/www/site_travaux && php index.php cron sendmail
```

### 10.2 `cron send_ref_validation_mails`

Envoie aux référents un lien personnel pour la validation des présences.

```bash
php index.php cron send_ref_validation_mails [days_before=7]
```

- Cherche les `travaux` non archivés, sans `ref_mail_sent_at`, à venir dans `$days_before` jours
- Pour chacun :
  1. Identifie la famille référente via la chaîne `trombi → groupes_member → famille`
  2. Génère un token de 64 hex (32 bytes random, 30 jours d'expiration)
  3. Pousse un mail dans `sendmail` (cron `sendmail` se chargera de l'envoi)
  4. Marque `travaux.ref_mail_sent_at = NOW()`

**Crontab recommandé** :

```cron
0 6 * * *  cd /var/www/site_travaux && php index.php cron send_ref_validation_mails
```

### 10.3 Archivage automatique

Pas un vrai cron : déclenché à la première visite de `Admwork_controller/register` chaque jour, avec throttle via `application/cache/last_archive_run.txt`. Archive les travaux passés depuis plus de 30 jours (sauf type `URG`).

---

## 11. Configuration & environnements

### 11.1 Fichier `application/config/app.php` (versionné)

```php
$config['app_name']    = 'Site de l\'association ABCM...';
$config['slogan']      = 'Outil de gestion des travaux';
$config['debug_app']   = 'none';        // none, debug, profiler
$config['sidebar']     = 'on';
$config['unit_todo']   = 20;            // nb d'unités attendues par famille
$config['maintenance'] = false;
$config['civil_year']  = '2025-2026';
$config['role_famille'] = 2;            // role_id par défaut pour les familles

$config['protocol']    = 'smtp';
$config['charset']     = 'utf-8';
$config['mailtype']    = 'html';
$config['wordwrap']    = TRUE;
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
```

### 11.2 Fichier `application/config/secured.php` (NON versionné)

Voir [§ 6.5](#65-configuration-sécurisée).

### 11.3 Surcharges par environnement

```
application/config/development/config.php
application/config/development/database.php
application/config/production/config.php     (non versionné)
application/config/production/database.php   (non versionné)
```

L'environnement est déterminé par `$_SERVER['CI_ENV']` ou `define('ENVIRONMENT', '...')` dans `index.php`.

### 11.4 `.htaccess`

À la racine, route toutes les requêtes vers `index.php` :

```apache
RewriteBase /
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

---

## 12. Déploiement (Git Flow)

### 12.1 Branches et environnements

| Branche | Environnement | URL |
|---|---|---|
| `develop` | Recette | https://regio.dev-asso.fr |
| `main` | Production | https://mulhouse-travaux.abcmzwei.eu/ |

### 12.2 Workflow

**Feature :**

1. Créer `feature-xxx` depuis `develop`
2. Développer, commiter, pousser
3. Ouvrir une Merge Request → merge dans `develop`
4. Déployer sur regio (git deploy sur le serveur)
5. Validation fonctionnelle
6. Merge `develop` → `main`
7. Déployer en production

**Hotfix :**

1. Créer `hotfix-xxx` depuis `main`
2. Corriger, valider en local
3. Déployer directement en production
4. Cherry-pick sur `develop` pour synchroniser

### 12.3 Avant chaque mise en prod

- [ ] Vérifier `application/config/production/database.php` à jour sur le serveur
- [ ] Vérifier `application/config/secured.php` à jour sur le serveur
- [ ] Exécuter les migrations SQL en attente (voir `application/migrations/`)
- [ ] Vérifier l'écriture sur `application/cache/`, `application/logs/`, `public/files/`
- [ ] Tester une connexion admin et une connexion famille

---

## 13. Annexes

### 13.1 Migrations SQL principales

| Fichier | Contenu |
|---|---|
| `application/migrations/Migration.sql` | Migration initiale Joomla → CI3, ajout colonnes `created`/`updated`, `type_session`, `e_mail_comp` |
| `application/migrations/mig_0910.sql` | Ajout `civil_year` et `archived` sur `unites`, `infos`, `travaux` |
| `application/migrations/mig_cantine.sql` | Tables `cantine_config` et `cantine_inscriptions` |
| `application/migrations/groupes.sql` | Dump de la table `groupes` |
| `application/migrations/emails.sql` | Dump de la table `emails` |
| `application/migrations/Options.sql` | Dump des options (couleurs, types, classifications) |

### 13.2 Migration manuelle pour la release courante

```sql
-- Auth v3 (élargir la colonne password pour bcrypt)
ALTER TABLE acl_users MODIFY COLUMN password VARCHAR(255) NOT NULL;
ALTER TABLE famille   MODIFY COLUMN password VARCHAR(255) NOT NULL;

-- Validation référent
CREATE TABLE validation_tokens (
  id INT(11) NOT NULL AUTO_INCREMENT,
  id_travaux INT(11) NOT NULL,
  id_fam_ref INT(11) NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_token (token),
  KEY idx_travaux (id_travaux)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE travaux ADD ref_mail_sent_at DATETIME NULL AFTER updated;

-- E-mails complémentaires famille
ALTER TABLE famille ADD e_mail_comp VARCHAR(255) NULL AFTER e_mail;

-- Module cantine
SOURCE application/migrations/mig_cantine.sql;
```

### 13.3 Convention de nommage

- Contrôleurs : `Xxx_controller.php` (PascalCase + suffixe)
- Modèles : `Xxx_model.php` (PascalCase + suffixe)
- Vues d'édition : `application/views/edition/Xxx_form.php`
- Vues spécifiques : `application/views/unique/Xxx_controller_<action>.php`
- Schémas JSON : `application/models/json/Xxx.json` (PascalCase, sans `_model`)
- Langue : `application/language/<idiom>/<controller_lowercase>_lang.php`
- Cron : méthode publique de `Cron` controller, lock via `process.loc`

### 13.4 Bonnes pratiques observées

- **Secure by default** : `Acl::$DontCheck = FALSE` par défaut. Tout contrôleur public doit explicitement opt-in.
- **Cache permissions** : par `role_id` en session, invalidé à la déconnexion.
- **Migration password transparente** : pas de batch SQL à lancer, la migration se fait à la connexion.
- **Timing constant** : `PasswordAuthenticator::verify()` exécute `password_hash` même si le login n'existe pas, pour empêcher l'énumération d'utilisateurs.
- **Lock cron** : `Cron::_setLock()` empêche deux exécutions parallèles via `process.loc`.
- **Throttle archivage** : flag fichier journalier dans `application/cache/`.

### 13.5 Pièges connus

- `groupes.acteurs` et `trombi.ref` stockent des **IDs en VARCHAR** (héritage). Les jointures fonctionnent grâce à la conversion implicite MySQL.
- `Auth` est autoloadée → son constructeur ne doit pas charger de modèles (cela casserait l'init de `MY_Controller`). Le chargement est différé via `_requireDeps()`.
- Le format de date utilisé pour `updated` doit être `'H'` (24h) et non `'h'` (12h sans AM/PM) — bug historique corrigé.
- L'option `WidthType.PERCENTAGE` ne fonctionne pas dans Google Docs ; utiliser DXA pour les exports.
- Le placeholder `'2025-2026'` est codé en dur dans `Admwork_model::GetFiltered` — à mettre en config si les campagnes futures débordent.


