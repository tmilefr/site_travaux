# site_travaux — Documentation technique

Application web de gestion des travaux associatifs et de la participation des familles pour l'association **ABCM Mulhouse-Lutterbach**.
Construite avec CodeIgniter 3 (PHP), avec une couche maison de génération de CRUD à partir de schémas JSON. Licence MIT.

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Stack technique](#2-stack-technique)
3. [Architecture du projet](#3-architecture-du-projet)
4. [Concepts métier](#4-concepts-métier)
5. [Modèle de données](#5-modèle-de-données)
6. [Authentification & ACL](#6-authentification--acl)
7. [Modules fonctionnels](#7-modules-fonctionnels)
8. [Guide du développeur : le framework maison](#8-guide-du-développeur--le-framework-maison)
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
| Front | HTML/CSS/JS, Bootstrap, templates "Nicdark" |
| Mail | CI_Email + SMTP, file d'envoi via cron |
| Build | Aucun build front (assets servis statiquement) |

---

## 3. Architecture du projet

### 3.1 Arborescence

```
site_travaux/
├── application/
│   ├── core/
│   │   └── MY_Controller.php         # CRUD générique (extension de CI_Controller)
│   ├── controllers/
│   │   ├── Home.php                  # login, logout, tableau de bord
│   │   ├── Admwork_controller.php    # travaux : inscription, validation, gestion
│   │   ├── Cantine_controller.php    # garde du midi
│   │   ├── Units_controller.php      # validation des unités par admin
│   │   ├── Familys_controller.php    # gestion familles + édition profil
│   │   ├── Orgchart_controller.php   # commissions et organigramme
│   │   ├── Cron.php                  # tâches planifiées (sendmail, ref_validation)
│   │   ├── Api.php                   # API REST + JWT
│   │   └── Acl_*_controller.php      # admin des rôles, contrôleurs, actions
│   ├── models/
│   │   ├── Core_model.php            # base : get_one, get_all, post, put, delete
│   │   ├── *_model.php               # modèles métiers
│   │   └── json/
│   │       ├── *.json                # schémas de table (champs, règles, dbforge)
│   │       └── Menus.json            # définition des menus
│   ├── libraries/
│   │   ├── Acl.php                   # autorisations + cascade auth
│   │   ├── Auth.php                  # factory d'auth (web + API + Delta SSO)
│   │   ├── PasswordAuthenticator.php # bcrypt + migration legacy
│   │   ├── Render_object.php         # factory de rendu à partir des schémas JSON
│   │   ├── Form_validation.php       # surcharge du Form_validation CI
│   │   ├── Bootstrap_tools.php       # helpers d'affichage Bootstrap (labels, couleurs)
│   │   ├── Libpdf.php                # génération de PDF
│   │   └── elements/element_*.php    # éléments de formulaire (voir § 8.4)
│   ├── hooks/
│   │   └── Loginchecker.php          # hook ACL avant chaque action
│   ├── language/french/              # i18n (clés métier + clés CI)
│   ├── migrations/                   # SQL manuels (Migration.sql, mig_*.sql)
│   ├── config/
│   │   ├── app.php                   # config non sensible (versionnée)
│   │   ├── secured.php               # SMTP, API_KEY, PASSWORD_SALT (non versionné)
│   │   └── development|production/   # surcharges par environnement
│   └── views/
│       ├── template/                 # head.php, footer.php (layout)
│       ├── edition/                  # Xxx_form.php : formulaires d'édition
│       └── unique/                   # Xxx_view.php, list_view.php, vues spécifiques
├── assets/
│   ├── css/
│   ├── js/
│   └── vendor/                       # bibliothèques tierces
├── system/                           # CI3 (framework)
├── public/files/                     # uploads (img/team, …)
├── .htaccess
├── README.md
├── CHANGELOG.md
└── index.php
```

### 3.2 Points forts architecturaux

1. **Factory par schéma JSON.** Chaque table a un fichier JSON (`Infos.json`, `Acl_users.json`, …) qui décrit ses champs : règles de validation, type de rendu (input, select, select_database, hidden, table liée…) et définition `dbforge`. `Core_model` et `Render_object` exploitent ces schémas pour générer formulaires, listes, vues et validations sans code spécifique.
2. **CRUD générique via `MY_Controller`.** Un contrôleur déclare `_controller_name`, `_model_name`, `_edit_view`, `_list_view`, `_autorize` puis appelle `init()`. Le routage `add` / `edit` / `list` / `delete` / `view` est géré par la classe mère.
3. **ACL centralisée et cachée en session.** Le hook `Loginchecker` appelle `acl->Route()` avant chaque action. Les permissions sont mises en cache par `role_id` en session.
4. **Auth multi-source.** Cascade `acl_users` → `famille`, plus SSO Delta Enfance qui synchronise le compte famille local. Mots de passe en bcrypt avec migration automatique des hashes legacy au login.
5. **API REST séparée** (`Api.php`) avec JWT, gestion des verbes HTTP (GET/POST/PUT/DELETE) et codes de retour normalisés.

---

## 4. Concepts métier

### 4.1 Vocabulaire

| Terme | Signification |
|---|---|
| **Travail / session** | Un événement auquel une famille s'inscrit (ligne dans `travaux`). |
| **Type de travail** | `TRA` (travaux), `MEN` (ménage), `GOU` (goûter), `LAV` (lavage), `DEC` (déchetterie), `INF` (informatique), `URG` (urgence, sans date), `can` (cantine). |
| **Inscription / Info** | Une ligne dans `infos` = une famille inscrite à une session, avec un nombre de participants et d'unités prévues. |
| **Unité (associative)** | Crédit de participation. Chaque session définit `nb_units`, multiplié par `nb_participants` à la validation. |
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
7. Archivage automatique (J+30) ─── travaux SET archived=1
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

#### `validation_tokens`

Tokens à durée limitée pour la validation par lien email.

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | |
| `id_travaux` | INT | |
| `id_fam_ref` | INT | |
| `token` | VARCHAR(64) | `bin2hex(random_bytes(32))` |
| `expires_at` | DATETIME | Création + 30 jours |
| `used_at` | DATETIME NULL | Marqué à la soumission finale |
| `created` | DATETIME | |

#### `cantine_config`, `cantine_inscriptions` (module Cantine)

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
| `groupes_member` | Personne : id_fam, name, surname, email, phone, picture |
| `trombi` | Affectation : id_grp, ref (= `groupes_member.id`), classif (RT, ME, BU, PE, VP) |
| `candidatures` | Candidatures à intégrer une commission |

### 5.4 Tables emails / file d'envoi

| Table | Rôle |
|---|---|
| `sendmail` | File d'envoi (reference, email, object, message, statut 0/1/2) |
| `sendmail_statut` | Log d'envois (id_sen, date, sendstatut, error) |
| `emails` | Emails complémentaires liés à `famille` (id_fam) |

### 5.5 Chaîne référent → famille

Chaîne centrale pour identifier le référent d'une session et lui envoyer les notifications.

```
travaux.referent_travaux (INT)
   = trombi.id
trombi.ref (VARCHAR contenant un id)
   = groupes_member.id
groupes_member.id_fam (VARCHAR contenant un id)
   = famille.id (INT)
```

> ⚠️ MySQL gère la conversion implicite VARCHAR ↔ INT pour les égalités. Si une valeur non numérique est stockée par erreur dans `id_fam`, la jointure ne remonte simplement aucune ligne (comportement souhaitable).

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

### 6.2 Objet `connected_user`

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

Au prochain login, `PasswordAuthenticator::verify()` détecte le format et migre :

- hash legacy `crypt()` (avec `PASSWORD_SALT`) → bcrypt ;
- hash legacy MD5 (table `famille` uniquement) → bcrypt ;
- hash bcrypt à coût obsolète → rehash bcrypt.

Comptes encore en hash legacy :

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

### 6.5 Configuration sécurisée (`secured.php`)

`application/config/secured.php` (non versionné) doit définir :

```php
define('API_KEY', '...');          // clé HMAC pour JWT
define('PASSWORD_SALT', '...');    // sel legacy crypt() — encore lu pour la migration

// reCAPTCHA (element_captcha)
const SITE_CAPTCHA_KEY        = '';
const SITE_CAPTCHA_SECRET_KEY = '';
const SITE_CAPTCHA_URL        = 'https://www.google.com/recaptcha/api/siteverify';
$config['captcha'] = TRUE;         // ou FALSE

// SMTP
$config['smtp_host']       = 'smtp.example.com';
$config['smtp_port']       = 587;
$config['smtp_user']       = '...';
$config['smtp_pass']       = '...';
$config['smtp_crypto']     = 'tls';
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
| `validate_by_token/$token` | invité | Valider via lien email (token 30 j, public) |
| `my_sessions` | fam | Sessions où l'utilisateur est référent |
| `list`, `add`, `edit`, `delete` | sys | CRUD admin |
| `worker` | sys | Statistiques participants |

### 7.2 Cantine (`Cantine_controller`)

Garde du midi sous forme d'agenda hebdomadaire.

| Action | Acteur | Description |
|---|---|---|
| `register/$week_offset` | sys / fam | Agenda lundi-vendredi pour une semaine |
| `register_one/$id_work` | fam | S'inscrire à un créneau |
| `unregister_one/$id_work` | fam | Se désinscrire (refusé si validé) |
| `config` | sys | Paramétrage : jours actifs, nb_slots, école, nb_units |
| `save_config` | sys | Sauvegarde |
| `generate` | sys | Génère les sessions sur une période |

Chaque session cantine = ligne `travaux` de `type='can'` ; une inscription = ligne `infos`. La validation passe par le flux `Units_controller/valid` existant.

### 7.3 Validation des unités (`Units_controller`)

| Action | Acteur | Description |
|---|---|---|
| `valid` | sys | Unités en attente, validation en lot |
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
| `myaccount` | Profil, changement de mot de passe, infos Delta |
| `no_right` | Page d'accès refusé |
| `maintenance` | Page hors service (config `maintenance=true`) |

---

## 8. Guide du développeur : le framework maison

Pour ajouter un CRUD complet sur une table, il faut 4 fichiers : un **schéma JSON**, un **modèle**, un **contrôleur** et (optionnellement) des **vues** d'édition et de rendu.

### 8.1 Modèle (`Core_model`)

```php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

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

Méthodes héritées : `get_one()`, `get_all()`, `post()`, `put()`, `delete()`, `delete_bulk()`, `get_distinct()`, `is_exist()`, `query()`, `truncate()`.

### 8.2 Contrôleur (`MY_Controller`)

```php
class Familys_controller extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->_controller_name = 'Familys_controller';      // nom pour le routage
        $this->_model_name      = 'Familys_model';           // modèle de données
        $this->_edit_view       = 'edition/Familys_form';    // vue d'édition
        $this->_list_view       = 'unique/Familys_view.php'; // vue de rendu d'un élément
        $this->_autorize        = ['list'=>true, 'add'=>true, 'edit'=>true, 'delete'=>true, 'view'=>true];
        $this->title           .= ' - '.$this->lang->line('GESTION_'.$this->_controller_name);
        $this->init();
    }
}
```

Rien d'autre à écrire pour avoir un CRUD fonctionnel sur la table `famille`.

### 8.3 Schéma JSON d'une table

Un fichier par table dans `application/models/json/<Model>.json`. Exemple :

```json
{
  "id": {
    "type": "hidden", "list": true, "search": false, "rules": null, "since": 1,
    "dbforge": { "type": "INT", "constraint": 11, "unsigned": true, "auto_increment": true }
  },
  "nom": {
    "sql": "ALTER TABLE famille ADD nom VARCHAR(255) NULL AFTER login;",
    "type": "input", "list": true, "search": true,
    "rules": "trim|required|min_length[2]|max_length[255]", "since": 1,
    "dbforge": { "type": "VARCHAR", "constraint": "255" }
  },
  "ecole": {
    "type": "select", "list": true, "search": true,
    "values": { "M": "Mulhouse", "L": "Lutterbach", "B": "Les deux" },
    "dbforge": { "type": "VARCHAR", "constraint": "1" }
  },
  "family": {
    "type": "select_database", "list": true, "search": false, "since": 2,
    "param": "distinct(family,id:name)", "values": [],
    "dbforge": { "type": "INT", "constraint": "5" }
  },
  "e_mail_comp": {
    "type": "table", "model": "Email_model",
    "ref": "email", "foreignkey": "id_fam"
  }
}
```

#### Attributs reconnus

| Attribut | Rôle |
|---|---|
| `type` | Élément de rendu (voir § 8.4) |
| `list` | Visible dans la vue liste |
| `search` | Inclus dans la recherche globale |
| `rules` | Règles CodeIgniter `form_validation` (voir § 8.5) |
| `since` | Version d'apparition du champ |
| `values` | Map clé → libellé (`select`, `checkbox`) |
| `param` | Source de données `distinct(table,id:label[@label2][#filter=xxx])` (`select_database`, `typeahead`, `checkboxdb`) |
| `query` | Requête SQL préchargée |
| `model`, `ref`, `foreignkey` | Table liée (`table`, `checkboxdb`) |
| `alternate_field` | Champ d'affichage alternatif (`select_database`) |
| `dbforge` | Définition pour `dbforge::create_table` |
| `sql` | `ALTER` d'ajout du champ (migrations) |

### 8.4 Catalogue des éléments

Chaque type correspond à `application/libraries/elements/element_<type>.php`, qui étend `element` :

```php
class element_XXXX extends element
{
    private function RenderElement(){}
    public function PrepareForDBA($value){}   // transformation avant écriture en base
    public function RenderFormElement(){}     // rendu en formulaire
    public function Render(){}                // rendu en liste / vue
    public function AfterExec($datas){}       // hook après ADD/EDIT (CORE controller)
}
```

| Type | Description |
|---|---|
| `hidden` | Champ caché (clés) |
| `input` | Champ texte |
| `password` | Mot de passe |
| `memo` | Zone de texte (`rows`) |
| `html` | Éditeur WYSIWYG (CKEditor) |
| `select` | Liste déroulante sur `values` |
| `select_database` | Liste déroulante alimentée par une table (`param`) |
| `typeahead` | Recherche dynamique sur une table (`param`) |
| `checkbox` | Liste de cases sur `values` |
| `checkboxdb` | Cases liées à une table, avec table de liaison |
| `table` | Table dynamique liée (relation 1-n) |
| `date` | Popup de choix de date |
| `time` | Popup de choix d'heure |
| `month` | Choix de mois |
| `file` | Upload de fichier |
| `captcha` | reCAPTCHA Google (config dans `secured.php`, § 6.5) |
| `service` | — |
| `created` / `updated` | Horodatage automatique (DATETIME) |

#### Exemples de définition

**`memo`**

```json
"adresse": {
  "type": "memo", "rows": 1, "list": false, "search": true, "rules": null, "since": 1,
  "dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

**`select_database`**

```json
"id": {
  "type": "select_database", "param": "distinct(acl_controllers,id:controller)", "values": [],
  "list": false, "search": true, "rules": "trim|required", "since": 1,
  "dbforge": { "type": "INT", "constraint": "10" }
}
```

**`typeahead`**

```json
"referent_travaux": {
  "type": "typeahead", "param": "distinct(groupes,id:title)", "values": [],
  "list": true, "search": true, "rules": null, "since": 1,
  "dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

**`checkbox`**

```json
"checkbox": {
  "type": "checkbox", "list": true, "search": false, "rules": null, "since": 1,
  "values": { "1": "Valeur 1", "2": "Valeur 2", "3": "Valeur 3" },
  "dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

**`time`**

```json
"time": {
  "type": "time", "list": false, "search": false, "rules": "trim|required", "since": 1,
  "minTime": "08:00:00", "maxHour": 20, "maxMinutes": 30, "interval": 15, "startTime": 14,
  "dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

**`created` / `updated`**

```json
"created": { "type": "created", "list": false, "search": false, "rules": null, "since": 1, "dbforge": { "type": "DATETIME" } },
"updated": { "type": "updated", "list": false, "search": false, "rules": null, "since": 1, "dbforge": { "type": "DATETIME" } }
```

Les types `input`, `password`, `date`, `html` et `select` suivent le même gabarit (seul `type` change, plus `values` pour `select`).

#### Tables liées : `checkboxdb` et `table`

Exemple avec une table parente et une table de liaison (`Parent.id = Liaison.key`) :

| Parent.id | Field_1 | Field_2 |
|:---:|:---:|:---:|
| 1 | 1 | xxx |
| 2 | 2 | yyy |

| Liaison.id | key | Field_3 |
|:---:|:---:|:---:|
| 1 | 1 | aaaa |
| 2 | 1 | bbbb |

**`checkboxdb`** — cases à cocher alimentées par une table, avec filtre :

```jsonc
"checkboxdb": {
  "type": "checkboxdb", "list": false, "search": false, "rules": null, "since": 1,
  "param": "distinct(options,cle:value#filter=yyyy)",
  "values": [],
  "model": "xxx_model",   // modèle qui pilote la table de liaison
  "ref": "Field_1",       // champ de référence du formulaire
  "foreignkey": "key",    // clé étrangère dans la table de liaison
  "dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

**`table`** — table dynamique (relation 1-n), même principe :

```jsonc
"e_mail_comp": {
  "type": "table", "link": "", "list": false, "search": false, "rules": "trim", "since": 1,
  "sql": "ALTER TABLE `famille` ADD `e_mail_comp` VARCHAR(255) NULL AFTER `e_mail`;",
  "model": "xxx_model",   // modèle de la table liée
  "ref": "Field_1",       // champ de référence
  "foreignkey": "key",    // lien entre table mère et table secondaire
  "dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

Schéma JSON du modèle de liaison (`xxx_model.json`) :

```json
{
  "id": {
    "type": "hidden", "list": true, "search": false, "rules": null, "since": 1,
    "dbforge": { "type": "INT", "constraint": 11, "unsigned": true, "auto_increment": true }
  },
  "key": {
    "type": "select_database", "param": "distinct(Parent,id:Field_1@Field_2)",
    "alternate_field": "Field_1", "values": [],
    "list": true, "search": true, "rules": "trim|required", "since": 1,
    "dbforge": { "type": "INT", "constraint": "11" }
  },
  "created": { "type": "created", "list": false, "search": false, "rules": null, "since": 1, "dbforge": { "type": "DATETIME" } },
  "updated": { "type": "updated", "list": false, "search": false, "rules": null, "since": 1, "dbforge": { "type": "DATETIME" } }
}
```

### 8.5 Règles de validation (`rules`)

Règles CodeIgniter, combinables avec `|` (ex. `trim|required|valid_email`). Chacune renvoie FALSE si la condition n'est pas remplie.

| Règle | Param. | Échoue si… | Exemple |
|---|:---:|---|---|
| `required` | — | le champ est vide | |
| `matches` | oui | ≠ du champ indiqué | `matches[form_item]` |
| `differs` | oui | = au champ indiqué | `differs[form_item]` |
| `regex_match` | oui | ne correspond pas à la regex | `regex_match[/regex/]` |
| `is_unique` | oui | valeur déjà présente en table (Query Builder requis) | `is_unique[table.field]` |
| `min_length` | oui | plus court que N | `min_length[3]` |
| `max_length` | oui | plus long que N | `max_length[12]` |
| `exact_length` | oui | longueur ≠ N | `exact_length[8]` |
| `greater_than` | oui | ≤ N ou non numérique | `greater_than[8]` |
| `greater_than_equal_to` | oui | < N ou non numérique | `greater_than_equal_to[8]` |
| `less_than` | oui | ≥ N ou non numérique | `less_than[8]` |
| `less_than_equal_to` | oui | > N ou non numérique | `less_than_equal_to[8]` |
| `in_list` | oui | hors liste | `in_list[red,blue,green]` |
| `alpha` | — | autre chose que des lettres | |
| `alpha_numeric` | — | autre chose que lettres/chiffres | |
| `alpha_numeric_spaces` | — | autre chose que lettres/chiffres/espaces (utiliser après `trim`) | |
| `alpha_dash` | — | autre chose que lettres/chiffres/`_`/`-` | |
| `numeric` | — | non numérique | |
| `integer` | — | non entier | |
| `decimal` | — | non décimal | |
| `is_natural` | — | pas un entier naturel (0, 1, 2…) | |
| `is_natural_no_zero` | — | pas un entier naturel non nul | |
| `valid_url` | — | URL invalide | |
| `valid_email` | — | email invalide | |
| `valid_emails` | — | un email invalide dans une liste séparée par virgules | |
| `valid_ip` | option | IP invalide (`ipv4` / `ipv6`) | `valid_ip[ipv4]` |
| `valid_base64` | — | caractères hors Base64 | |

### 8.6 Vues

**Vue d'édition** (`application/views/edition/Xxx_form.php`) :

```php
<div class="container-fluid">
<?php
echo form_open('Users_controller/add', ['id' => 'edit'], ['form_mod' => $form_mod, 'id' => $id]);
echo form_error('name', '<div class="alert alert-danger">', '</div>');
?>
<div class="form-row">
  <div class="form-group col-md-4">
    <?php
      echo $this->bootstrap_tools->label('name');
      echo $this->render_object->RenderFormElement('name');
    ?>
  </div>
</div>
<button type="submit" class="btn btn-primary"><?php echo Lang($form_mod.'_'.$this->router->class); ?></button>
<?php echo form_close(); ?>
</div>
```

**Vue de rendu d'un élément** (`application/views/unique/Xxx_view.php`) :

```php
<div class="card">
  <div class="card-header">
    <?php echo $this->render_object->RenderElement('name').' '.$this->render_object->RenderElement('surname'); ?>
    / <?php echo $this->render_object->RenderElement('family'); ?>
  </div>
  <div class="card-body">
    <h5 class="card-title"><?php echo $this->render_object->RenderElement('email'); ?></h5>
  </div>
</div>
```

`Render_object` choisit l'élément dans `elements/element_<type>.php` selon `defs[champ]->type`. On peut aussi passer la valeur explicitement : `RenderElement('nom', $data->nom)`.

### 8.7 Menus

Définis dans `application/models/json/Menus.json`. Chaque entrée porte une `opt` (`sys`, `fam`, ou `null` pour tous) qui filtre selon le type d'utilisateur.

---

## 9. API REST

### 9.1 Authentification

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
  "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "id": "1",
  "role_id": "1",
  "type": "sys",
  "expireAt": 1687366618,
  "expireAtRender": "2023-06-21 16:56:58"
}
```

Toutes les autres requêtes portent le header `Authorization: Bearer <jwt>`.

### 9.2 Exposer un objet

Chaque objet exposé a besoin d'une méthode dans `Api.php` :

```php
class Api extends MY_Controller {
    /**
     * Point d'entrée pour Familys (exemple d'implémentation de référence).
     * Penser à déclarer les règles : Acl_controllers_controller/edit/14 (14 = id du contrôleur api)
     * et à gérer les rôles : Acl_roles_controller/set_rules/1 (1 = id du rôle admin)
     */
    public function Familys($id = null) {
        $this->_SetHeaders(['GET', 'OPTIONS']);
        $this->_getObject('Familys_model', $id);
    }
}
```

### 9.3 Consommer l'API

**Depuis un contrôleur PHP** (avec l'utilisateur connecté) :

```php
$api = [
    'base_url'   => base_url('API/'),
    'user_agent' => 'php',
    'headers'    => [
        'Authorization' => 'Bearer '.$this->auth->_get('connected_user')->token
    ]
];
$this->restclient->init($api);
$result = $this->restclient->get('Familys');
if ($result->error)
    echo debug($result->error);
echo debug(json_decode($result->response));
```

**Depuis une page en JS** :

```html
<div id="get_api_content"></div>
<script type="text/javascript">
  function GETAPI() {
    $.ajax({
      cache: false,
      dataType: "json",
      async: false,
      crossDomain: true,
      url: "<?php echo base_url('API/Familys'); ?>",
      method: "GET",
      headers: {
        "accept": "application/json",
        "Access-Control-Allow-Origin": "<?php echo base_url(); ?>",
        "Authorization": "Bearer <?php echo $this->auth->_get('connected_user')->token; ?>"
      }
    }).done(function (response) {
      $("#get_api_content").html(response[0].role_name.render);
    });
  }
  GETAPI();
</script>
```

Chaque champ renvoyé par l'API a la forme `{ "raw": ..., "render": ... }` (valeur brute + rendu via `Render_object`).

### 9.4 Endpoint `/api/mails`

| Verbe | URL | Description |
|---|---|---|
| `POST` | `/api/mails` | Crée un mail (statut 0, en file d'envoi) |
| `GET` | `/api/mails` | Liste tous les mails |
| `GET` | `/api/mails/$id` | Récupère un mail |
| `PUT` | `/api/mails/$id` (ou `id` dans le corps) | Met à jour |
| `DELETE` | `/api/mails/$id` | Supprime |

**Création** — `POST /api/mails` :

```json
{
  "reference": "envois",
  "email": "test@test.com",
  "message": "ceci est un test de message",
  "object": "test"
}
```

Réponse 201 : `{ "id": 42 }`

**Lecture** — `GET /api/mails/7` :

```json
{
  "id":        { "raw": "7", "render": "7" },
  "reference": { "raw": "envois", "render": "envois" },
  "email":     { "raw": "test@test.com", "render": "test@test.com" },
  "object":    { "raw": "test 2", "render": "test 2" },
  "message":   { "raw": "ceci est un test de message", "render": "ceci est un test de message" },
  "statut":    { "raw": "1", "render": "envoyé" },
  "error":     { "raw": "<pre>\n\n</pre>", "render": "<pre>\n\n</pre>" },
  "created":   { "raw": "2023-06-21 01:03:25", "render": "2023-06-21 01:03:25" },
  "updated":   { "raw": "2023-06-21 02:20:02", "render": "2023-06-21 02:20:02" }
}
```

**Modification** — `PUT /api/mails/7` (ou `PUT /api/mails/` avec `"id": 7` dans le corps) :

```json
{
  "reference": "envois",
  "email": "test@test.com",
  "message": "ceci est un test de message",
  "object": "test 2"
}
```

L'envoi effectif est assuré par le cron `sendmail` (§ 10.1).

### 9.5 Codes de retour

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

Commandes à lancer en PHP CLI depuis la racine du projet. Un verrou (`Cron::_setLock()`, fichier `process.loc`) empêche deux exécutions parallèles.

### 10.1 `cron sendmail`

Dépile la file d'envoi (table `sendmail`).

```bash
php index.php cron sendmail [size=10]
```

- récupère jusqu'à `$size` mails en statut 0 ;
- envoie via SMTP (config `secured.php`) ;
- met à jour le statut (1 envoyé / 2 erreur) ;
- journalise dans `sendmail_statut`.

```cron
*/10 * * * *  cd /var/www/site_travaux && php index.php cron sendmail
```

### 10.2 `cron send_ref_validation_mails`

Envoie aux référents un lien personnel de validation des présences.

```bash
php index.php cron send_ref_validation_mails [days_before=7]
```

Cherche les `travaux` non archivés, sans `ref_mail_sent_at`, à venir dans `$days_before` jours. Pour chacun :

1. identifie la famille référente (chaîne § 5.5) ;
2. génère un token de 64 hex (32 octets aléatoires, 30 jours de validité) ;
3. pousse un mail dans `sendmail` (envoyé par le cron `sendmail`) ;
4. marque `travaux.ref_mail_sent_at = NOW()`.

```cron
0 6 * * *  cd /var/www/site_travaux && php index.php cron send_ref_validation_mails
```

### 10.3 Archivage automatique

Pas un vrai cron : déclenché à la première visite quotidienne de `Admwork_controller/register`, avec throttle via `application/cache/last_archive_run.txt`. Archive les travaux passés depuis plus de 30 jours (sauf type `URG`).

---

## 11. Configuration & environnements

### 11.1 `application/config/app.php` (versionné)

```php
$config['app_name']     = 'Site de l\'association ABCM...';
$config['slogan']       = 'Outil de gestion des travaux';
$config['debug_app']    = 'none';        // none, debug, profiler
$config['sidebar']      = 'on';
$config['unit_todo']    = 20;            // nb d'unités attendues par famille
$config['maintenance']  = false;
$config['civil_year']   = '2025-2026';
$config['role_famille'] = 2;             // role_id par défaut des familles

$config['protocol']     = 'smtp';
$config['charset']      = 'utf-8';
$config['mailtype']     = 'html';
$config['wordwrap']     = TRUE;
$config['newline']      = "\r\n";
$config['crlf']         = "\r\n";
```

### 11.2 `application/config/secured.php` (non versionné)

Voir [§ 6.5](#65-configuration-sécurisée-securedphp).

### 11.3 Surcharges par environnement

```
application/config/development/config.php
application/config/development/database.php
application/config/production/config.php     (non versionné)
application/config/production/database.php   (non versionné)
```

L'environnement est déterminé par `$_SERVER['CI_ENV']` ou `define('ENVIRONMENT', '...')` dans `index.php`.

### 11.4 `.htaccess`

Route toutes les requêtes vers `index.php` :

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

1. Créer `feature-xxx` depuis `develop`.
2. Développer, commiter, pousser.
3. Ouvrir une Merge Request → merge dans `develop`.
4. Déployer sur regio (`git deploy` sur le serveur).
5. Validation fonctionnelle.
6. Merge `develop` → `main`.
7. Déployer en production.

**Hotfix :**

1. Créer `hotfix-xxx` depuis `main`.
2. Corriger, valider en local.
3. Déployer en production.
4. Après validation en production, cherry-pick sur `develop` pour synchroniser.

### 12.3 Checklist avant mise en prod

- [ ] `application/config/production/database.php` à jour sur le serveur
- [ ] `application/config/secured.php` à jour sur le serveur
- [ ] Migrations SQL en attente exécutées (`application/migrations/`, § 13.2)
- [ ] Droits d'écriture sur `application/cache/`, `application/logs/`, `public/files/`
- [ ] Test d'une connexion admin et d'une connexion famille

---

## 13. Annexes

### 13.1 Migrations SQL principales

| Fichier | Contenu |
|---|---|
| `Migration.sql` | Migration initiale Joomla → CI3, colonnes `created`/`updated`, `type_session`, `e_mail_comp` |
| `mig_0910.sql` | `civil_year` et `archived` sur `unites`, `infos`, `travaux` |
| `mig_cantine.sql` | Tables `cantine_config` et `cantine_inscriptions` |
| `groupes.sql` | Dump de la table `groupes` |
| `emails.sql` | Dump de la table `emails` |
| `Options.sql` | Dump des options (couleurs, types, classifications) |

Tous ces fichiers sont dans `application/migrations/`.

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

-- Emails complémentaires famille
ALTER TABLE famille ADD e_mail_comp VARCHAR(255) NULL AFTER e_mail;

-- Module cantine
SOURCE application/migrations/mig_cantine.sql;
```

### 13.3 Conventions de nommage

| Élément | Convention |
|---|---|
| Contrôleurs | `Xxx_controller.php` (PascalCase + suffixe) |
| Modèles | `Xxx_model.php` (PascalCase + suffixe) |
| Schémas JSON | `application/models/json/Xxx.json` (sans `_model`) |
| Vues d'édition | `application/views/edition/Xxx_form.php` |
| Vues spécifiques | `application/views/unique/Xxx_controller_<action>.php` |
| Langue | `application/language/<idiom>/<controller_lowercase>_lang.php` |
| Cron | méthode publique du contrôleur `Cron`, verrou via `process.loc` |

### 13.4 Bonnes pratiques

- **Secure by default** : `Acl::$DontCheck = FALSE` par défaut ; tout contrôleur public doit explicitement opt-in.
- **Cache des permissions** : par `role_id` en session, invalidé à la déconnexion.
- **Migration de mot de passe transparente** : aucun batch SQL, la migration se fait à la connexion.
- **Temps constant** : `PasswordAuthenticator::verify()` exécute `password_hash` même si le login n'existe pas (anti-énumération).
- **Verrou cron** : `Cron::_setLock()` empêche deux exécutions parallèles.
- **Throttle archivage** : flag fichier journalier dans `application/cache/`.

### 13.5 Pièges connus

- `groupes.acteurs` et `trombi.ref` stockent des **IDs en VARCHAR** (héritage) ; les jointures fonctionnent grâce à la conversion implicite MySQL.
- `Auth` est autoloadée : son constructeur ne doit pas charger de modèles (cela casserait l'init de `MY_Controller`). Le chargement est différé via `_requireDeps()`.
- Le format de date pour `updated` doit être `'H'` (24 h) et non `'h'` (12 h sans AM/PM) — bug historique corrigé.
- L'option `WidthType.PERCENTAGE` ne fonctionne pas dans Google Docs ; utiliser DXA pour les exports.
- `'2025-2026'` est codé en dur dans `Admwork_model::GetFiltered` — à passer en config pour les campagnes futures.
