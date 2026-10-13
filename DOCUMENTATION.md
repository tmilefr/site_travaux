# Documentation — `site_travaux`

> Application web de gestion des travaux associatifs et de la participation des familles, pour l'association ABCM Mulhouse-Lutterbach.
> CodeIgniter 4.7 (PHP ≥ 8.2), MariaDB. Ce document regroupe la documentation fonctionnelle et technique, le guide de développement, l'exploitation,
> la migration CodeIgniter 3 → 4 (code et base de données) et le plan de test.

**Par où commencer ?**

| Je veux… | Voir |
|---|---|
| comprendre l'application | [§ 1](#1-vue-densemble) à [§ 6](#6-modules-fonctionnels) |
| développer (contrôleur, modèle, schéma JSON, élément de formulaire) | [§ 7](#7-développer-dans-le-projet) |
| utiliser l'API ou les tâches planifiées | [§ 8](#8-api-rest), [§ 9](#9-tâches-planifiées-spark) |
| configurer le `.env` / installer / déployer | [§ 10](#10-configuration-et-environnements), [§ 11](#11-installation-et-déploiement) |
| **migrer la base de production** | [§ 13](#13-migration-de-la-base-de-données) (script `database/sql/mig_prod_vers_ci4.sql`) |
| tester | [§ 14](#14-vérifications-automatisées), [§ 15](#15-plan-de-test) |

---

## Table des matières

- [1. Vue d'ensemble](#1-vue-densemble)
  - [1.1 Contexte métier](#11-contexte-métier)
  - [1.2 Licence et auteurs](#12-licence-et-auteurs)
  - [1.3 Stack technique](#13-stack-technique)
- [2. Architecture du projet](#2-architecture-du-projet)
  - [2.1 Arborescence](#21-arborescence)
  - [2.2 Points forts architecturaux](#22-points-forts-architecturaux)
  - [2.3 Conventions de l'application](#23-conventions-de-lapplication)
- [3. Concepts métier](#3-concepts-métier)
  - [3.1 Vocabulaire](#31-vocabulaire)
  - [3.2 Cycle de vie d'une session de travaux](#32-cycle-de-vie-dune-session-de-travaux)
- [4. Modèle de données](#4-modèle-de-données)
  - [4.1 Tables principales](#41-tables-principales)
  - [4.2 Tables ACL](#42-tables-acl)
  - [4.3 Tables organigramme](#43-tables-organigramme)
  - [4.4 Tables emails / file d'envoi](#44-tables-emails--file-denvoi)
  - [4.5 Chaîne référent → famille](#45-chaîne-référent--famille)
- [5. Authentification et ACL](#5-authentification-et-acl)
  - [5.1 Cascade d'authentification](#51-cascade-dauthentification)
  - [5.2 Object `connected_user`](#52-object-connected_user)
  - [5.3 Migration des mots de passe](#53-migration-des-mots-de-passe)
  - [5.4 ACL et pages publiques (`guestPages`)](#54-acl-et-pages-publiques-guestpages)
  - [5.5 Configuration sécurisée](#55-configuration-sécurisée)
- [6. Modules fonctionnels](#6-modules-fonctionnels)
  - [6.1 Travaux (`Admwork_controller`)](#61-travaux-admwork_controller)
  - [6.2 Cantine (`Cantine_controller`)](#62-cantine-cantine_controller)
  - [6.3 Validation des unités (`Units_controller`)](#63-validation-des-unités-units_controller)
  - [6.4 Familles (`Familys_controller`)](#64-familles-familys_controller)
  - [6.5 Organigramme (`Orgchart_controller`)](#65-organigramme-orgchart_controller)
  - [6.6 Mon compte (`Home`)](#66-mon-compte-home)
  - [6.7 Import CSV des familles (`Familys_controller`)](#67-import-csv-des-familles-familys_controller)
  - [6.8 Fichiers et images déposés](#68-fichiers-et-images-déposés)
  - [6.9 Administration](#69-administration)
- [7. Développer dans le projet](#7-développer-dans-le-projet)
  - [7.1 Schéma JSON d'une table](#71-schéma-json-dune-table)
  - [7.2 Modèle (`Core_model`)](#72-modèle-core_model)
  - [7.3 Contrôleur (`CrudController`)](#73-contrôleur-crudcontroller)
  - [7.4 Vues](#74-vues)
  - [7.5 Éléments de formulaire](#75-éléments-de-formulaire)
  - [7.6 Règles de validation](#76-règles-de-validation)
  - [7.7 Menus, gabarit de page et pagination](#77-menus-gabarit-de-page-et-pagination)
- [8. API REST](#8-api-rest)
  - [8.1 Authentification API](#81-authentification-api)
  - [8.2 Endpoints `/api/mails`](#82-endpoints-apimails)
  - [8.3 Codes de retour](#83-codes-de-retour)
  - [8.4 Exposer un objet par l'API](#84-exposer-un-objet-par-lapi)
  - [8.5 Appeler l'API](#85-appeler-lapi)
  - [8.6 Test manuel](#86-test-manuel)
- [9. Tâches planifiées (spark)](#9-tâches-planifiées-spark)
  - [9.1 `cron:sendmail`](#91-cronsendmail)
  - [9.2 `cron:ref-validation`](#92-cronref-validation)
  - [9.3 `cron:session-alerts`](#93-cronsession-alerts)
  - [9.4 Archivage automatique](#94-archivage-automatique)
  - [9.5 Planification sur Plesk / hébergement mutualisé](#95-planification-sur-plesk--hébergement-mutualisé)
- [10. Configuration et environnements](#10-configuration-et-environnements)
  - [10.1 `app/Config/Travaux.php` (versionné)](#101-appconfigtravauxphp-versionné)
  - [10.2 Le fichier `.env`](#102-le-fichier-env)
  - [10.3 Serveur web (`.htaccess`)](#103-serveur-web-htaccess)
  - [10.4 Prérequis PHP](#104-prérequis-php)
- [11. Installation et déploiement](#11-installation-et-déploiement)
  - [11.1 Installation](#111-installation)
  - [11.2 Branches et environnements](#112-branches-et-environnements)
  - [11.3 Workflow Git Flow](#113-workflow-git-flow)
  - [11.4 Avant chaque mise en production](#114-avant-chaque-mise-en-production)
- [12. Migration CodeIgniter 3 → CodeIgniter 4](#12-migration-codeigniter-3--codeigniter-4)
  - [12.1 Ce qui est natif CodeIgniter 4](#121-ce-qui-est-natif-codeigniter-4)
  - [12.2 Correspondance des dossiers](#122-correspondance-des-dossiers)
  - [12.3 Changements de comportement et corrections](#123-changements-de-comportement-et-corrections)
  - [12.4 Reste à faire et points d'attention](#124-reste-à-faire-et-points-dattention)
- [13. Migration de la base de données](#13-migration-de-la-base-de-données)
  - [13.1 Quel script appliquer](#131-quel-script-appliquer)
  - [13.2 Ce que fait la migration](#132-ce-que-fait-la-migration)
  - [13.3 Après la migration](#133-après-la-migration)
  - [13.4 Validation du script](#134-validation-du-script)
  - [13.5 Script complet](#135-script-complet)
  - [13.6 Scripts historiques (`database/sql/`)](#136-scripts-historiques-databasesql)
- [14. Vérifications automatisées](#14-vérifications-automatisées)
- [15. Plan de test](#15-plan-de-test)
  - [15.1 Objectifs et périmètre](#151-objectifs-et-périmètre)
  - [15.2 Stratégie de test](#152-stratégie-de-test)
  - [15.3 Environnement et prérequis](#153-environnement-et-prérequis)
  - [15.4 Jeux de données et comptes de test](#154-jeux-de-données-et-comptes-de-test)
  - [15.5 Campagne de smoke test (30 min)](#155-campagne-de-smoke-test-30-min)
  - [15.6 Cas de test détaillés](#156-cas-de-test-détaillés)
  - [15.7 Checklist de non-régression avant mise en production](#157-checklist-de-non-régression-avant-mise-en-production)
  - [15.8 Gestion des anomalies](#158-gestion-des-anomalies)
  - [15.9 Critères d'entrée et de sortie](#159-critères-dentrée-et-de-sortie)
  - [15.10 Automatisation](#1510-automatisation)
  - [15.11 Annexes](#1511-annexes)
- [16. Annexes](#16-annexes)
  - [16.1 Convention de nommage](#161-convention-de-nommage)
  - [16.2 Bonnes pratiques observées](#162-bonnes-pratiques-observées)
  - [16.3 Pièges connus](#163-pièges-connus)

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

- Framework : CodeIgniter 4, MIT License (© British Columbia Institute of Technology)
- Surcouches maison : © Tmile, 2018
- Évolutions métier : association ABCM Mulhouse-Lutterbach

### 1.3 Stack technique

| Composant | Version / Détail |
|---|---|
| Langage | PHP **≥ 8.2** (extensions `intl`, `mbstring`, `curl`, `mysqli`, `gd`, `json`) |
| Framework | **CodeIgniter 4.7.x** (installé par Composer, `vendor/` non versionné) |
| Base de données | MariaDB ≥ 10.3 / MySQL (tables en `utf8` ou `latin1` selon l'historique ; connexion en `utf8`) |
| Authentification | bcrypt + JWT (`firebase/php-jwt`) + SSO Delta Enfance (`service('curlrequest')`) |
| PDF | Dompdf |
| Front | HTML/CSS/JS, framework « Nicdark » + Bootstrap, aucun build (assets servis statiquement) |
| E-mail | `service('email')` (SMTP, configuration `.env`), file d'envoi traitée par commande spark |
| Planification | commandes spark (`app/Commands`) lancées par cron |

> L'application a été écrite pour CodeIgniter 3.1.13 puis **entièrement réécrite en CodeIgniter 4** (branche `ci4-natif`, sans couche de
> compatibilité). Les correspondances CI3 → CI4 et les changements de comportement sont au [§ 12](#12-migration-codeigniter-3--codeigniter-4).

## 2. Architecture du projet

### 2.1 Arborescence

```
site_travaux/
├── app/
│   ├── Cells/                      # View Cells : MenuCell (menus filtrés par les droits ACL) + menu.php (balisage)
│   ├── Commands/                   # commandes spark : cron:sendmail, cron:ref-validation, cron:session-alerts
│   ├── Config/                     # App, Database, Routes, Filters, Services, Session, Email, Pager, Validation, Events...
│   │   └── Travaux.php             # réglages applicatifs ; secrets via .env (travaux.apiKey, ...)
│   ├── Controllers/
│   │   ├── CrudController.php      # CRUD générique (contrôleur de base abstrait)
│   │   ├── Home.php                # login, logout, tableau de bord, mon compte
│   │   ├── Admwork_controller.php  # travaux : inscription, validation, gestion
│   │   ├── Cantine_controller.php  # garde du midi
│   │   ├── Units_controller.php    # validation des unités par l'admin
│   │   ├── Familys_controller.php  # familles, import CSV ABCM, statistiques
│   │   ├── Orgchart_controller.php # commissions et organigramme
│   │   ├── Api.php                 # API REST + JWT
│   │   └── Acl_*_controller.php    # administration des rôles, contrôleurs, actions
│   ├── Filters/AclFilter.php       # contrôle d'accès avant chaque action
│   ├── Helpers/app_helper.php      # tr(), open_form(), open_form_multipart()
│   ├── Language/fr/                # i18n : tableaux PHP (un fichier par contrôleur + Menu, Traduction, Validation...)
│   ├── Libraries/
│   │   ├── Acl.php                 # autorisations
│   │   ├── Auth.php                # authentification (web + API + SSO Delta)
│   │   ├── PasswordAuthenticator.php # bcrypt + migration des anciens hashes
│   │   ├── Render_object.php       # rendu « factory » à partir des schémas JSON
│   │   ├── Bootstrap_tools.php     # helpers d'affichage (feuilles de style, couleurs)
│   │   ├── Libpdf.php              # génération de PDF (Dompdf)
│   │   ├── Inscriptions.php        # règles d'inscription (capacité, doublons, dates)
│   │   ├── RefNotifier.php         # notifications aux référents
│   │   ├── CronJobs.php            # logique des tâches planifiées
│   │   └── Elements/               # éléments de formulaire element_*.php (+ UploadTrait)
│   ├── Models/
│   │   ├── Core_model.php          # base : CodeIgniter\Model + schéma JSON
│   │   ├── *_model.php             # modèles métiers
│   │   └── json/*.json             # schémas de table (champs, règles, dbforge) + Menus.json
│   └── Views/
│       ├── layouts/                # main.php (gabarit), page.php (View Layout : étend main)
│       ├── edition/                # formulaires
│       ├── unique/                 # vues spécifiques par contrôleur
│       ├── Pager/, Validation/     # gabarits de pagination et d'erreurs de formulaire
│       └── errors/                 # pages d'erreur
├── public/                         # seul dossier exposé par le serveur web
│   ├── index.php
│   ├── assets/                     # css, js, img, vendor
│   ├── files/                      # fichiers déposés (.htaccess : aucun script exécutable)
│   └── data/pdf/                   # PDF générés
├── database/sql/                   # scripts SQL (mig_prod_vers_ci4.sql, jeu_de_test/...)
├── writable/                       # cache, logs, sessions, cron.lock
├── tests/unit/                     # tests PHPUnit (structure, helpers)
├── tools/                          # smoke.sh, e2e.sh (+ e2e/), check_classes.php
├── vendor/                         # dépendances Composer (non versionné)
├── .env                            # environnement, base de données, secrets (non versionné ; modèle : env.example)
├── composer.json
├── spark                           # CLI CodeIgniter 4
├── CHANGELOG.md
├── DOCUMENTATION.md                # ce document
└── README.md
```

### 2.2 Points forts architecturaux

1. **Approche « factory » par schéma JSON.** Chaque table a un fichier JSON (`Infos.json`, `Acl_users.json`, …) qui décrit les champs, leurs
   règles de validation, leur type de rendu (input, select, select_database, hidden, table liée…) et leur définition `dbforge`.
   `Core_model` et `Render_object` exploitent ces schémas pour générer formulaires, listes, vues et validations sans code spécifique.
   Très DRY pour un back-office.
2. **CRUD générique via `CrudController`.** Un contrôleur déclare `_controller_name`, `_model_name`, `_edit_view`, `_list_view`, `_autorize`
   dans `boot()` puis appelle `init()`. Le routage `add` / `edit` / `list` / `delete` / `view` est géré par la classe mère.
3. **ACL centralisée et mise en cache en session.** Le filtre `AclFilter` (ancien hook `Loginchecker`) appelle `acl->Route()` avant chaque
   action. Les permissions sont mises en cache par `role_id` dans la session et invalidées à la déconnexion. *Secure by default* :
   `DontCheck = FALSE`, tout contrôleur public doit être déclaré explicitement.
4. **Authentification multi-source.** Cascade `acl_users` → `famille`, plus SSO Delta Enfance qui synchronise le compte famille local.
   Mots de passe en bcrypt avec migration automatique des anciens hashes à la connexion.
5. **API REST séparée** avec JWT, gestion des verbes HTTP (GET/POST/PUT/DELETE) et codes de retour normalisés (201, 202, 400, 403…).
6. **Natif CodeIgniter 4** : modèles `CodeIgniter\Model` (`find`/`insert`/`update`/`paginate`, horodatage automatique), validation
   `validateData()`, pager, View Cells, View Layouts, `CURLRequest`, `Response::download()`, commandes spark.

### 2.3 Conventions de l'application

* **`CrudController`** (`app/Controllers/CrudController.php`) : contrôleur de base abstrait (liste, ajout,
  édition, suppression, export CSV, actions groupées) piloté par les schémas JSON des modèles.
  Un contrôleur enfant surcharge `boot()` pour déclarer `$_controller_name`, `$_model_name`, `$_autorize`…
  puis appelle `$this->init()`.
* **Routage** : chaque contrôleur est listé dans `app/Config/Routes.php` (liste vérifiée par les tests) et reçoit
  ses URL `Controleur/action/p1/p2…` par `CrudController::dispatch()`, qui ne laisse passer que :
  les méthodes publiques déclarées par le contrôleur, et le CRUD générique seulement si le contrôleur déclare
  `$_autorize` (et uniquement pour `list/add/edit/delete/view` présents dans `$_autorize`).
  Les helpers internes (`init`, `LoadModel`, méthodes `_xxx`…) ne sont jamais joignables. Les URL gardent la casse
  d'origine ou le minuscule (compatible avec les droits ACL et les liens des e-mails).
* **Vues** : les services utiles arrivent comme variables (`$render_object`, `$bootstrap_tools`, `$acl`) ;
  traductions par `tr('CLE')`, erreurs de formulaire par `validation_show_error('champ', 'alert')`,
  formulaires par `open_form()` (variante de `form_open()` qui accepte des identifiants non textuels).
* **Modèles** : propriétés `$table`, `$primaryKey`, `$order`, `$direction`, `$json` ; les requêtes utilisent le Query
  Builder natif. Les noms de classes (`Familys_model`, `Admwork_controller`…) sont conservés : ils sont stockés dans les
  droits ACL et dans les URL.
* **Traductions** : `tr()` cherche la clé dans `<Controleur>.php`, `Cantine.php`, `Inscriptions.php`, `Menu.php`, puis
  `Traduction.php`. Une clé absente s'affiche `<i>CLE</i>`. L'écran « Traductions » édite ces tableaux.
* **Avertissements PHP** : l'application historique émet des avertissements (clé/propriété absente) sans conséquence ;
  `Config\Events` les journalise (niveau `warning`) au lieu de lever une exception, comme avec `error_reporting()` de CI3.

---

## 3. Concepts métier

### 3.1 Vocabulaire

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

### 3.2 Cycle de vie d'une session de travaux

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

## 4. Modèle de données

### 4.1 Tables principales

#### `famille`

Compte famille (utilisateur de type `fam`).

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | Clé interne |
| `idfamille` | INT | Référence Delta Enfance |
| `code_famille_abcm` | VARCHAR(20) | Identifiant ABCM (clé de rapprochement de l'import CSV) |
| `login` | VARCHAR | Login local |
| `password` | VARCHAR(**255**) | bcrypt (60 caractères) ; hashes `crypt()` / MD5 migrés à la connexion |
| `e_mail` | VARCHAR | E-mail principal (= login Delta) |
| `e_mail_comp` | VARCHAR | Lien vers `emails` (table liée) |
| `nom`, `prenom`, `cp`, `ville`, `adresse` | VARCHAR | Coordonnées |
| `ecole` | CHAR(1) | M, L ou B |
| `capacity` | VARCHAR | Compétences |
| `alert_types` | VARCHAR | Types de travaux pour lesquels la famille veut une alerte e-mail |
| `nb_enfants` | INT | |
| `civil_year` | VARCHAR | Année civile en cours |
| `to_deactivate` | TINYINT | 1 = absente du dernier CSV ABCM (l'admin décide de la suite) |
| `type_session` | INT | |
| `role_id` | INT | Rôle ACL (défaut 2 = `role_famille`) |
| `created`, `updated` | DATETIME | Posés par le modèle |

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
| `alert_sent_at` | DATETIME | Marque l'envoi de l'alerte « nouvelle session » aux familles abonnées |
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
| `unites_id_famille` | INT | → `famille.id` |
| `unites_desc` | LONGTEXT | Description |
| `unites_referent_travaux` | VARCHAR | Référent |
| `unites_date`, `unites_heure_debut`, `unites_heure_fin` | DATE / TIME | |
| `unites_valides` | FLOAT | Quantité d'unités |
| `unites_comm` | VARCHAR | Commentaire |
| `type_session` | INT | |
| `civil_year` | VARCHAR | |
| `archived` | INT | |

#### `members`

Enfants rattachés à une famille.

| Colonne | Type | Description |
|---|---|---|
| `id` | INT PK | |
| `id_fam` | INT | → `famille.id` |
| `code_membre_abcm` | VARCHAR(50) | Identifiant ABCM (import CSV) |
| `type`, `nom`, `prenom` | VARCHAR | |
| `classe` | VARCHAR(100) | Ex. `2025 2026 MUL CL07` |

#### `validation_tokens`

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

#### Autres tables

| Table | Rôle |
|---|---|
| `family_import` | Journal des imports CSV ABCM (fichier, volumétrie créées / mises à jour / marquées / erreurs, rapport JSON, opérateur) |
| `famille_alerts` | Préférences d'alerte e-mail par famille (`id_fam`, `id_type`) |
| `parameters` | Paramétrage applicatif à ligne unique (nom, slogan, SMTP…) |
| `cantine_config` | Configuration des jours de garde : 1 ligne par jour (1 = lundi … 5 = vendredi), école et année (`active`, `nb_slots`, `nb_units`, `id_referent`, horaires) |
| `cantine_inscriptions` | 1 ligne = 1 famille inscrite à 1 date de garde (`id_info`, `id_travaux`) |
| `cantine_generation` | Journal des générations de créneaux (`date_deb`, `date_fin`, `ecole`, `nb_created`, `nb_skipped`) |
| `ci_sessions` | Sessions (driver base de données de CodeIgniter 4, colonne `timestamp` de type TIMESTAMP) |
| `options`, `templates`, `events`, `files` | Options (types, couleurs, classifications), modèles de message, évènements, fichiers publics |

### 4.2 Tables ACL

| Table | Rôle |
|---|---|
| `acl_users` | Comptes admin (sys) |
| `acl_roles` | Rôles (admin, famille, …) |
| `acl_controllers` | Contrôleurs déclarés |
| `acl_actions` | Actions par contrôleur |
| `acl_roles_controllers` | Matrice rôle × action × allow |

### 4.3 Tables organigramme

| Table | Rôle |
|---|---|
| `groupes` | Commissions (`type='com'`) ou structure (`type='org'`) |
| `groupes_member` | Personne avec id_fam, name, surname, email, phone, picture |
| `trombi` | Affectation : id_grp, ref (= groupes_member.id), classif (RT, ME, BU, PE, VP) |
| `candidatures` | Candidatures à intégrer une commission |

### 4.4 Tables emails / file d'envoi

| Table | Rôle |
|---|---|
| `sendmail` | File d'envoi (reference, email, object, message, statut 0/1/2) |
| `sendmail_statut` | Log d'envois (id_sen, date, sendstatut, error) |
| `emails` | E-mails complémentaires liés à `famille` (id_fam) |

### 4.5 Chaîne référent → famille

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

---

## 5. Authentification et ACL

### 5.1 Cascade d'authentification

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
                                ├─ service('curlrequest') → delta-enfance3.fr
                                └─ Familys_model::verifyLoginAPI() puis
                                   _syncFamilyFromDelta() OU _createFamilyFromDelta()
                                   (mot de passe stocké en bcrypt)
```

### 5.2 Object `connected_user`

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

### 5.3 Migration des mots de passe

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

### 5.4 ACL et pages publiques (`guestPages`)

Définies dans `Acl::$guestPages` :

```php
[
  'home/logout', 'home/login', 'home/no_right',
  'home/index', 'home/myaccount', 'home/about',
  'home/maintenance', 'home',
  'admwork_controller/validate_by_token',  // accès référent par lien e-mail
  'translations_controller/switch_lang'
]
```

Les tâches planifiées ne passent plus par une URL : ce sont des commandes spark ([§ 9](#9-tâches-planifiées-spark)), il n'y a donc plus
de page `cron/*` dans la liste des pages invitées.

Toute autre route requiert une session valide et un droit ACL (`role_id` × `controller` × `action`).

### 5.5 Configuration sécurisée

Les secrets (`travaux.apiKey`, `travaux.passwordSalt`, clés reCAPTCHA, SMTP…) ne sont plus dans un fichier PHP mais dans `.env`
(non versionné, modèle : `env.example`). Voir le détail au [§ 10.2](#102-le-fichier-env).

## 6. Modules fonctionnels

### 6.1 Travaux (`Admwork_controller`)

| Action | Acteur | Description |
|---|---|---|
| `register` | sys / fam | Liste des sessions à venir + filtres + cartes/liste |
| `register_one/$id` | fam | S'inscrire à une session |
| `validate_one/$id` | fam (référent) | Valider les présences (mode connecté) |
| `validate_by_token/$token` | invité | Valider via lien email (token 30j, public) |
| `my_sessions` | fam | Liste des sessions où l'utilisateur est référent |
| `list`, `add`, `edit`, `delete` | sys | CRUD admin |
| `worker` | sys | Statistiques participants |

### 6.2 Cantine (`Cantine_controller`)

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

### 6.3 Validation des unités (`Units_controller`)

| Action | Acteur | Description |
|---|---|---|
| `valid` | sys | Liste des unités en attente, validation en lot |
| `valids` | sys | Confirmation après sélection |
| `list` | sys / fam | Unités complémentaires (hors session) |

### 6.4 Familles (`Familys_controller`)

| Action | Acteur | Description |
|---|---|---|
| `histo` | fam | Mon compte, mes unités, mes sessions à venir |
| `histo` | sys | Sélection d'une famille pour consultation |
| `list`, `add`, `edit`, `delete` | sys | CRUD admin |
| `skills` | sys | Filtre par compétence |
| `stats` | sys | Synthèse unités par famille |
| `units/$id_fam` | sys | Détail des unités d'une famille |
| `check/$id_fam` | sys | Gestion des chèques de caution |

### 6.5 Organigramme (`Orgchart_controller`)

| Action | Acteur | Description |
|---|---|---|
| `orga` | tous | Vue publique des commissions |
| `list` | sys | Liste des commissions |
| `featured/$id` | sys | Met une commission en avant |
| `add`, `edit`, `delete` | sys | CRUD admin |

### 6.6 Mon compte (`Home`)

| Action | Description |
|---|---|
| `login` | Formulaire de connexion (NORM ou DELTA) |
| `logout` | Détruit la session |
| `myaccount` | Profil, change password, infos Delta |
| `no_right` | Page d'accès refusé |
| `maintenance` | Page hors-service (config `maintenance=true`) |

---

### 6.7 Import CSV des familles (`Familys_controller`)

Synchronisation des familles et des enfants à partir de l'export ABCM (CSV `;`, CP1252, 57 colonnes minimum). Accessible aux administrateurs.

| Action | Description |
|---|---|
| `import` | Formulaire d'envoi. **POST** : le fichier est stocké sous `public/files/imports/`, analysé, puis un **aperçu** (différentiel) est affiché sans rien écrire en base |
| `import_apply` | Applique le différentiel après confirmation : le fichier est **relu** (rien n'est repris du navigateur), puis familles et enfants sont créés / mis à jour dans une transaction et l'import est journalisé dans `family_import` |
| `import_history` | Historique des imports (fichier, date, opérateur, volumétrie) |

Règles de rapprochement : famille par `code_famille_abcm`, à défaut par e-mail ; enfant par `code_membre_abcm`, à défaut par (nom, prénom).
L'école est déduite du CSV (`MUL` → `M`, `LUT` → `L`, `B` si la fratrie est répartie). Les familles qui ont un code ABCM mais n'apparaissent plus
dans le CSV sont marquées `to_deactivate = 1` (à traiter manuellement ; leur historique est conservé) et sont réactivées si elles reviennent.
Les lignes invalides (colonnes manquantes, e-mail ou code absent) sont listées et ignorées. Les unités, inscriptions et mots de passe locaux ne sont jamais écrasés.

### 6.8 Fichiers et images déposés

Les champs de type `file` et `img` des schémas JSON (`Files.path`, `GroupesMembers.picture` / `thumbnail`) passent par `UploadTrait` :

- seules les extensions d'une **liste blanche** sont acceptées (documents, archives, images) — jamais `.php`, `.phtml`, `.phar`… ;
- une image est vérifiée (`getimagesize`) puis **redimensionnée à la largeur du schéma** (`width`) par `service('image')` : JPEG, PNG, GIF et WebP ;
- le nom est assaini (lettres, chiffres, `-`, `_`), un fichier existant n'est **jamais écrasé** (préfixe horodaté) ;
- un refus est signalé par un bandeau rouge (flashdata `upload_error`) et l'ancienne valeur du champ est conservée ;
- `public/files/.htaccess` interdit en plus l'exécution de scripts dans ce dossier.

Les fichiers sont servis par `https://<site>/files/...` (la racine web est `public/`).

### 6.9 Administration

| Contrôleur | Rôle |
|---|---|
| `Acl_users_controller`, `Acl_roles_controller` | Comptes administrateurs, rôles et matrice des droits (`set_rules/<id_role>`) |
| `Acl_controllers_controller`, `Acl_actions_controller` | Contrôleurs et actions déclarés ; `scan` détecte ceux du code absents de l'ACL ; `bulk_add_action` ajoute une action à plusieurs contrôleurs |
| `Sendmail_controller`, `Templates_controller` | File d'envoi des e-mails (statuts 0 / 1 / 2) et modèles de message |
| `Options_controller`, `Parameters` | Options (types de travaux, couleurs, classifications) et paramétrage applicatif |
| `Translations_controller` | Édition des tableaux de langue `app/Language/<langue>/` (sauvegarde préalable, protection contre la traversée de répertoire) |
| `Files_controller`, `Event_controller`, `Publics` | Fichiers publics, évènements, vue publique des fichiers |
| `Candidatures_controller`, `GroupesMembers_controller` | Candidatures aux commissions, membres des commissions |

> Après chaque ajout de contrôleur ou d'action, lancer **Droits > Contrôleurs > scan** puis cocher les droits pour les rôles concernés
> (*secure by default* : un contrôleur non déclaré est inaccessible).

---

## 7. Développer dans le projet


### 7.1 Schéma JSON d'une table

Chaque table a un fichier dans `app/Models/json/<Model>.json`. Exemple raccourci pour `Familys.json` :

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
- `rules` : règles de validation CodeIgniter 4 (`trim|required|valid_email`… voir [§ 7.6](#76-règles-de-validation))
- `since` : version d'apparition du champ
- `values` : map clé → libellé (pour `select`)
- `query` : requête SQL préchargée (pour `select_database`, `typeahead`)
- `param` : alias `distinct(table,id:label)` pour générer la requête
- `dbforge` : définition de colonne pour `Forge` (création / mise à jour de la table via `Api/SetTable`)
- `sql` : ALTER d'ajout du champ (utilisé par migrations)

### 7.2 Modèle (`Core_model`)

Un modèle déclare ses propriétés ; les champs autorisés et l'horodatage sont déduits des **colonnes réelles** de la table.

```php
namespace App\Models;

class Files_model extends Core_model
{
    protected $table      = 'files';
    protected $primaryKey = 'id';
    protected $order      = 'name';
    protected $direction  = 'desc';
    protected $json       = 'Files.json';   // schéma des champs (app/Models/json)

    public function GetFilesByType($type = null, $statut = 'P', $limit = 5)
    {
        return $this->db->table('files')->select('*')->where('type', $type)->where('statut', $statut)
                        ->orderBy('id', 'DESC')->limit($limit)->get()->getResult();
    }
}
```

`Core_model` étend `CodeIgniter\Model` : `find`, `findAll`, `first`, `insert`, `update`, `delete` et `paginate` sont ceux du framework.
Les méthodes du projet sont de fines surcouches pilotées par le schéma JSON :

| Méthode | Rôle |
|---|---|
| `get_one()` | La ligne dont la clé vaut `key_value` (`find`) |
| `get_all()` | Toutes les lignes avec filtre, recherche globale et tri simple |
| `get()` | Page courante de la liste (filtre, recherche, tri) par `Model::paginate()` ; le total est lu depuis le pager |
| `get_all_filtered()` | Comme `get()` sans pagination (export CSV) |
| `get_pagination()` | Nombre total de lignes de la liste |
| `post($data)` / `put($id)` | Insertion (retourne l'identifiant) / mise à jour de la ligne courante, limitée aux champs du schéma |
| `delete($id)` / `delete_bulk($ids)` | Suppression d'une ligne / de plusieurs lignes |
| `is_exist($champ, $valeur)` | La ligne correspondante ou `false` |
| `distinct($opt)`, `get_distinct($champ)` | Valeurs distinctes pour alimenter les listes déroulantes |
| `_init_def()` | Instancie les éléments de formulaire depuis le schéma JSON |

**Horodatage** : si la table a des colonnes `created` / `updated`, le modèle pose `created` à l'insertion et `updated` à chaque écriture
(`$useTimestamps`). Ne jamais les envoyer depuis un formulaire ni les poser à la main.

### 7.3 Contrôleur (`CrudController`)

```php
namespace App\Controllers;

class Files_controller extends CrudController
{
    protected function boot(): void
    {
        $this->_controller_name = 'Files_controller';          // nom utilisé pour le routage et les droits
        $this->_model_name      = 'Files_model';
        $this->_edit_view       = 'edition/Files_form';
        $this->_list_view       = 'unique/Files_view.php';
        $this->_autorize        = ['list' => true, 'add' => true, 'edit' => true, 'delete' => true, 'view' => true];
        $this->title           .= tr('GESTION_' . $this->_controller_name);
        $this->init();
    }
}
```

Rien d'autre à écrire pour un CRUD complet. Un nouveau contrôleur doit en plus être **ajouté à la liste de `app/Config/Routes.php`** (vérifié par
`StructureTest`), puis déclaré dans l'ACL (scan). Le cycle d'un contrôleur est `initController()` → `boot()` ; `goTo()` redirige,
`render_view()` rend la vue dans le gabarit (ou seule pour une requête AJAX), `abort()` renvoie une erreur HTTP.

### 7.4 Vues

Formulaire d'édition (extrait de `app/Views/edition/Files_form.php`) :

```php
<?php
echo open_form_multipart($render_object->_getCi('_controller_name') . '/' . $render_object->_get('form_mod'),
    ['class' => '', 'id' => 'edit'], ['form_mod' => $render_object->_get('form_mod'), 'id' => $id]);

foreach ($required_field as $name) {
    echo validation_show_error($name, 'alert');          // erreur de validation du champ
}
?>
<div class="form-group col-md-4">
    <?php echo $render_object->label('name'); ?>
    <?php echo $render_object->RenderFormElement('name'); ?>
</div>
<button type="submit" class="btn btn-primary"><?php echo tr($form_mod . '_' . $render_object->_getCi('_controller_name')); ?></button>
<?php echo form_close(); ?>
```

Rendu d'un élément en liste ou en vue : `echo $render_object->RenderElement('name', $data->name);`.
Dans une vue, les services arrivent comme variables (`$render_object`, `$bootstrap_tools`, `$acl`). Traductions par `tr('CLE')`.
`open_form()` / `open_form_multipart()` sont des variantes de `form_open()` qui acceptent des identifiants non textuels.

### 7.5 Éléments de formulaire

#### element.php

##### Événement exploité par le CORE Controller sur Action (ADD/EDIT)
```php
namespace App\Libraries\Elements;

class element_XXXX extends element
{
	private function RenderElement(){}
	public function PrepareForDBA($value){}
	public function RenderFormElement(){}
	public function Render(){}
	public function AfterExec($datas){}
}

```

##### Param Json

```json
	"name": {
		"type": "element",
		"list": true/false, // in list view
		"search": true/false,// integrate in global search 
		"rules": "trim|required|min_length[2]|max_length[255]", //voir § 7.6
		"since": 1,
		"dbforge": {
			"sql":"sql to make this field in case of migration",
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

#### element_captcha.php

Champ reCAPTCHA du formulaire de connexion. Clés dans `.env` (voir [§ 10.2](#102-le-fichier-env)) :

```ini
travaux.captcha = true                       # false : captcha désactivé
travaux.siteCaptchaKey = '...'               # clé publique
travaux.siteCaptchaSecretKey = '...'         # clé secrète
```


#### element_checkbox.php
Liste de chechbox sur la base des valeurs dans "values"
```json
	"checkbox": {
		"type": "checkbox",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"values": {
			"1": "valeur 1",
			"2": "Valeur 2",
			"3": "Vakeur 3"
		},		
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

#### element_checkboxdb.php
Un objet checkbox en relation avec une liste dans une table.
ex de la table option avec un filtre sur la table.

Parent
|id|Field_1|Field_2| 
| :---:|:---:|:---:|
|1|1|xxx|
|2|2|yyy|

liaison
| id | key | Field_3 | 
| :---:|:---:|:---:|
|1|1|aaaa|
|2|1|bbbb|

Relation : Parent.id = Liaison.key

```json
"checkboxdb":{
		"sql" : "",
		"type": "checkboxdb",
		"list": false,
		"search": false,
		"rules": null,
		"param":"distinct(options,cle:value#filter=yyyy)",
		"values":[],
		"model": "xxx_model", //model qui pilote la modification sur la table de liaison
		"ref":"Field_1", //référence formulaire
		"foreignkey":"key",	//clé étrangère dans la table de laison	
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

xxx_model.json
```json
{
	"id": {
		"type": "hidden",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "INT",
			"constraint": 11,
			"unsigned": true,
			"auto_increment": true
		}
	},
	"key": {
		"type": "select_database",
		"param":"distinct(Parent,id:Field_1@Field_2)",
		"alternate_field":"Field_1",
		"values":[],
		"list": true,
		"search": true,
		"rules": "trim|required",
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "INT",
			"constraint": "11"
		}
	},
	"created": {
		"type": "created",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "DATETIME"
		}
	},
	"updated": {
		"type": "updated",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"sql":"",
			"type": "DATETIME"
		}
	}						
}
```

#### element_date.php
Popup avec choix de la date
```json
"date": {
		"type": "date",
		"list": true,
		"search": true,
		"rules": "trim|required",
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
#### element_file.php

Document déposé (colonne `VARCHAR` qui stocke le nom du fichier). Le dossier de destination est donné par `path` dans le schéma JSON.
Réception par `UploadTrait` : liste blanche d'extensions, nom assaini, aucun écrasement (voir [§ 6.8](#68-fichiers-et-images-déposés)).

```json
"path": {
	"type": "file",
	"path": "public/files/doc/",
	"list": false,
	"search": false,
	"rules": "",
	"since": 1,
	"dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

#### element_img.php

Image déposée : mêmes protections que `element_file`, plus vérification du contenu (`getimagesize`) et redimensionnement à la largeur `width`
(JPEG, PNG, GIF, WebP). Sans valeur, l'image de remplacement `assets/img/team/empty.jpg` est affichée.

```json
"picture": {
	"type": "img",
	"path": "public/files/img/team/",
	"width": 450,
	"list": false,
	"search": true,
	"rules": null,
	"since": 1,
	"dbforge": { "type": "VARCHAR", "constraint": "255" }
}
```

#### element_html.php
Élément WSYWIG (ckeditor)
```json
"html": {
		"type": "html",
		"list": false,
		"search": true,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
#### element_memo.php
```json
	"adresse": {
		"type": "memo",
		"rows" : 1,
		"list": false,
		"search": true,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},	
```
#### element_month.php
```json
```
#### element_password.php
```json
	"password": {
		"type": "password",
		"list": true,
		"search": true,
		"rules": "trim|required|min_length[2]|max_length[255]",
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
#### element_select_database.php
```json
	"id": {
		"type": "select_database",
		"param":"distinct(acl_controllers,id:controller)",
		"values":[],
		"list": false,
		"search": true,
		"rules": "trim|required",
		"since": 1,
		"dbforge": {
			"type": "INT",
			"constraint": "10"
		}
	},
```
#### element_select.php
```json
	"select": {
		"type": "select",
		"list": true,
		"search": false,
		"rules": null,
		"since": 1,
		"values": {
			"1": "valeur 1",
			"2": "Valeur 2",
			"3": "Vakeur 3"
		},		
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
#### element_service.php
```json
```
#### element_table.php
Un objet table dynamique en relation avec une liste dans une table.
ex de la table option avec un filtre sur la table.

voir element_checkboxdb.php

```json
"table": {
		"type": "table",
		"link" : "",
		"sql":"ALTER TABLE `famille` ADD `e_mail_comp` VARCHAR(255) NULL AFTER `e_mail`;", //infos
		"list": false,
		"search": false,
		"rules": "trim",
		"since": 1,
		"model": "xxx_model", //model à utiliser pour la table liaison
		"ref":"Field_1", //champ de référence
		"foreignkey":"key", //lien entre les tables maire et secondaire.
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},	
```

#### element_time.php
Popup avec choix de l'heure
```json
	"time": {
		"type": "time",
		"list": false,
		"search": false,
		"rules": "trim|required",
		"since": 1,
		"minTime": "08:00:00",
		"maxHour": 20,
		"maxMinutes": 30,
		"interval": 15,
		"startTime":14,		
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```
#### element_typeahead.php
Moteur de recherche dynamique qui pointe sur une table
```json
	"referent_travaux": {
		"type": "typeahead",
		"list": true,
		"search": true,
		"rules": null,
		"param": "distinct(groupes,id:title)",
		"values": [],
		"since": 1,
		"dbforge": {
			"type": "VARCHAR",
			"constraint": "255"
		}
	},
```

#### element_created.php
Date de création, posée par le modèle à l'insertion (`Model::$useTimestamps`) : ce champ n'est plus envoyé par le formulaire.
```json
	"created": {
		"type": "created",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "DATETIME"
		}
	},
```

#### element_updated.php
Date de dernière modification, posée par le modèle à chaque écriture. Le champ n'apparaît pas dans le formulaire et une valeur postée est ignorée.
```json
	"updated": {
		"type": "updated",
		"list": false,
		"search": false,
		"rules": null,
		"since": 1,
		"dbforge": {
			"type": "DATETIME"
		}
	}
```

### 7.6 Règles de validation

Les règles du champ `rules` d'un schéma JSON sont celles de **CodeIgniter 4** (`Controller::validateData()`), séparées par `|`.
`trim` est appliqué par l'application avant la validation (CodeIgniter 4 n'a pas de règle qui modifie la valeur) ; un champ vide non requis
n'est pas soumis aux autres règles (`permit_empty` ajouté automatiquement).

| Règle | Paramètre | Description | Exemple |
|---|---|---|---|
| `required` | non | Échoue si le champ est vide | |
| `permit_empty` | non | Autorise une valeur vide sans appliquer les autres règles | |
| `matches` / `differs` | oui | Égal / différent d'un autre champ | `matches[password]` |
| `regex_match` | oui | Correspond à l'expression régulière | `regex_match[/^[A-Z]+$/]` |
| `is_unique` | oui | Valeur absente de `table.champ` | `is_unique[famille.login]` |
| `min_length`, `max_length`, `exact_length` | oui | Longueur minimale, maximale ou exacte | `max_length[255]` |
| `greater_than`, `greater_than_equal_to`, `less_than`, `less_than_equal_to` | oui | Comparaisons numériques | `greater_than[8]` |
| `in_list` | oui | Valeur dans une liste fixe | `in_list[red,blue,green]` |
| `alpha`, `alpha_numeric`, `alpha_numeric_space`, `alpha_dash` | non | Caractères alphabétiques / alphanumériques / avec espaces / avec `_` et `-` | |
| `numeric`, `integer`, `decimal`, `is_natural`, `is_natural_no_zero` | non | Nombres | |
| `valid_url`, `valid_email`, `valid_emails`, `valid_ip`, `valid_base64` | non / `ipv4`, `ipv6` | Formats | |

Les messages, en français, sont dans `app/Language/fr/Validation.php` ; les erreurs s'affichent par `validation_show_error($champ, 'alert')`
(gabarits `app/Views/Validation/`).

### 7.7 Menus, gabarit de page et pagination

- **Menus** : définis dans `app/Models/json/Menus.json` (`sysmenu`, `optionmenu`, `mainmenu`). Chaque entrée porte une `opt` (`sys`, `fam` ou
  null) ; les liens sont filtrés par les droits ACL. Le rendu est une **View Cell** :
  `<?= view_cell('App\Cells\MenuCell', ['position' => 'mainmenu']) ?>` ; la logique est dans `app/Cells/MenuCell.php`, le balisage dans
  `app/Cells/menu.php`. Aucun contenu n'est produit si l'utilisateur n'a accès à aucune entrée.
- **Gabarit** : `layouts/page.php` étend `layouts/main.php` (`extend` / `section('content')` / `renderSection`) autour de la vue de contenu
  choisie par le contrôleur. Une requête AJAX reçoit la vue seule.
- **Pagination** : `Model::paginate()` + gabarit `app/Views/Pager/app_bootstrap.php` ; le numéro de page est le 4e segment de l'URL
  (`Controleur/list/page/N`).

---

## 8. API REST

### 8.1 Authentification API

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

### 8.2 Endpoints `/api/mails`

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

### 8.3 Codes de retour

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

### 8.4 Exposer un objet par l'API

Chaque objet exposé a son point d'entrée dans le contrôleur `Api` (exemple de `Familys`). Penser à déclarer le contrôleur et ses actions dans l'ACL
(`Acl_controllers_controller/edit/<id du contrôleur Api>`) puis à ouvrir les droits du rôle (`Acl_roles_controller/set_rules/<id du rôle>`).

```php
public function Familys($id = null)
{
    if ($early = $this->cors(['GET', 'OPTIONS'])) {   // en-têtes CORS, pré-vol OPTIONS, méthode refusée
        return $early;
    }

    return $this->_getObject('Familys_model', $id);   // JSON : { champ: { raw, render } }
}
```

### 8.5 Appeler l'API

**Depuis un contrôleur PHP**, avec l'utilisateur connecté (client HTTP natif `CURLRequest`) :

```php
$client = service('curlrequest', [
    'baseURI'     => base_url('Api/'),
    'headers'     => ['Authorization' => 'Bearer ' . service('auth')->_get('connected_user')->token],
    'http_errors' => false,
]);
$result = $client->get('Familys');
$familles = json_decode((string) $result->getBody());
```

**Depuis une page (JavaScript)** :

```html
<div id="get_api_content"></div>
<script>
    $.ajax({
        cache: false, dataType: 'json', crossDomain: true, method: 'GET',
        url: "<?php echo base_url('Api/Familys'); ?>",
        headers: {
            accept: 'application/json',
            Authorization: "Bearer <?php echo service('auth')->_get('connected_user')->token; ?>"
        }
    }).done(function (response) {
        $('#get_api_content').html(response[0].role_name.render);
    });
</script>
```

> L'URL est sensible à la casse : `Api/...` ou `api/...`.

### 8.6 Test manuel

`POST /api/login`

```json
{ "login": "xxxxxxx", "password": "xxxxxxx", "api-key": "xxxxxxx", "type_cnx": "NORM" }
```

La réponse contient le JWT (`jwt`), `id`, `role_id`, `type`, `expireAt` (voir [§ 8.1](#81-authentification-api)).

**Créer un e-mail** — `POST /api/mails` avec l'en-tête `Authorization: Bearer <jwt>` :

```json
{ "reference": "envois", "email": "test@test.com", "message": "ceci est un test de message", "object": "test" }
```

**Lire** — `GET /api/mails` (tous) ou `GET /api/mails/7`. Chaque champ est renvoyé en `raw` et `render` :

```json
{
    "id":     { "raw": "7", "render": "7" },
    "email":  { "raw": "test@test.com", "render": "test@test.com" },
    "statut": { "raw": "1", "render": "envoyé" },
    "created": { "raw": "2023-06-21 01:03:25", "render": "2023-06-21 01:03:25" }
}
```

**Modifier** — `PUT /api/mails/7` (ou `PUT /api/mails` avec l'`id` dans le corps) :

```json
{ "reference": "envois", "email": "test@test.com", "message": "ceci est un test de message", "object": "test 2" }
```

L'envoi effectif est fait par `php spark cron:sendmail` ([§ 9](#9-tâches-planifiées-spark)).

> `api/login` est dans la liste des pages invitées de l'ACL (`Acl::$guestPages`) : un client externe peut s'y connecter sans session. Sans cela, `AclFilter` redirigeait l'appel vers la page de connexion HTML. Les autres actions de l'API s'authentifient avec le JWT renvoyé par ce login, en en-tête `Authorization: Bearer <jwt>` (lu par `Acl`, sans session ; durée de vie 6000 s). La session web reste acceptée.

**Jeu de test de recette** — `GET|POST /Api/jeu_de_test` (recette uniquement). L'action recharge le jeu de `database/sql/jeu_de_test/` sans accès à MySQL :

```
GET  /Api/jeu_de_test                                      → nombre de lignes du jeu, par table (plage 9000–9999)
POST /Api/jeu_de_test  {"action":"reload","password":"…"}  → purge puis recharge (mot de passe commun des comptes de test)
POST /Api/jeu_de_test  {"action":"purge"}                  → retire le jeu de test
```

Garde-fous : répond **404** tant que `travaux.allowTestData = true` n'est pas posé dans le `.env` (désactivé par défaut) ; **403** hors rôle administrateur (rôle 1) ; le droit `api/jeu_de_test` se donne à ce rôle dans l'ACL, comme pour les autres actions de l'API.
L'année civile (`@CY`) est reprise de `civilYear`, et les hashes sont calculés à la volée : le mot de passe n'est stocké nulle part. Le script est exécuté dans une transaction (`App\Libraries\JeuDeTest`).

## 9. Tâches planifiées (spark)

Les tâches sont des commandes spark (`app/Commands`, groupe « Travaux ») à lancer depuis la racine du projet (`php spark list`). La logique est dans `App\Libraries\CronJobs` ; un verrou `flock` (`writable/cron.lock`) empêche deux exécutions simultanées et disparaît seul si le processus est tué.

### 9.1 `cron:sendmail`

Pool d'envoi des emails en file (`sendmail` table).

```bash
php spark cron:sendmail [taille=10]
```

- Récupère jusqu'à `$size` mails en statut 0
- Envoie via SMTP (config `Config\Email`, variables `email.*` du `.env`)
- Met à jour le statut (1 envoyé / 2 erreur)
- Log dans `sendmail_statut`

**Crontab recommandé** :

```cron
*/10 * * * *  cd /var/www/site_travaux && php spark cron:sendmail
```

### 9.2 `cron:ref-validation`

Envoie aux référents un lien personnel pour la validation des présences.

```bash
php spark cron:ref-validation [jours=7]
```

- Cherche les `travaux` non archivés, sans `ref_mail_sent_at`, à venir dans `$days_before` jours
- Pour chacun :
  1. Identifie la famille référente via la chaîne `trombi → groupes_member → famille`
  2. Génère un token de 64 hex (32 bytes random, 30 jours d'expiration)
  3. Pousse un mail dans `sendmail` (cron `sendmail` se chargera de l'envoi)
  4. Marque `travaux.ref_mail_sent_at = NOW()`

**Crontab recommandé** :

```cron
0 6 * * *  cd /var/www/site_travaux && php spark cron:ref-validation
```

### 9.3 `cron:session-alerts`

Alerte les familles abonnées (Home/myaccount) de chaque nouvelle session publiée.

```bash
php spark cron:session-alerts [jours=0]
```

- Nécessite la colonne `travaux.alert_sent_at` (`database/sql/mig_alert_sent_at.sql`), posée à l'envoi pour ne pas notifier deux fois
- `jours` : ne notifie que les sessions des N prochains jours (0 = sans borne)

### 9.4 Archivage automatique

Pas un vrai cron : déclenché à la première visite de `Admwork_controller/register` chaque jour, avec throttle via `writable/cache/last_archive_run.txt`. Archive les travaux passés depuis plus de 30 jours (sauf type `URG`).

---

### 9.5 Planification sur Plesk / hébergement mutualisé

- Utiliser le **PHP CLI de la version choisie pour le domaine**, avec le chemin complet : le `php` du shell n'a souvent pas l'extension `intl`
  (erreur `Class "Locale" not found`). Exemple :
  `/opt/plesk/php/8.3/bin/php /var/www/vhosts/<domaine>/spark cron:sendmail` (« Tâches planifiées » > « Exécuter une commande »).
- Contrôle : `/opt/plesk/php/8.3/bin/php -m | grep -i intl`.
- Crontab recommandé :
  ```cron
  */10 * * * *  cd /chemin/du/site && php spark cron:sendmail
  0 6 * * *     cd /chemin/du/site && php spark cron:ref-validation
  0 7 * * *     cd /chemin/du/site && php spark cron:session-alerts
  ```
- Les mails en erreur (statut 2) sont repris à chaque passage ; le détail de l'échec SMTP est dans `sendmail_statut.error` (colonne `TEXT`).

---

## 10. Configuration et environnements

### 10.1 `app/Config/Travaux.php` (versionné)

Réglages applicatifs, avec valeurs par défaut surchargeables dans `.env` (préfixe `travaux.`) :
`appName`, `slogan`, `about`, `debugApp` (`none` | `debug`), `sidebar`, `unitTodo` (unités attendues par famille), `maintenance`,
`civilYear` (année scolaire courante, ex. `2025-2026`), `allowTestData` (active `Api/jeu_de_test`, recette uniquement), `roleFamille` (`role_id` par défaut des familles), `captcha`, `siteCaptchaKey`,
`siteCaptchaSecretKey`, `apiKey`, `passwordSalt`, `mailFromEmail`, `mailFromName`, `mailReplyTo`, `deltaBaseUrl`, `deltaUserAgent`.

### 10.2 Le fichier `.env`

Non versionné (modèle : `env.example`). Il porte l'environnement, la base, les secrets et le SMTP :

```ini
CI_ENVIRONMENT = production              # development | production | testing
app.baseURL = 'https://mon-domaine.fr/'  # avec le / final, même protocole que le site (sinon CSS/JS bloqués : contenu mixte)

database.default.hostname = localhost
database.default.database = nom_de_la_base
database.default.username = utilisateur
database.default.password = '...'

# Application (Config\Travaux)
travaux.civilYear = '2025-2026'
travaux.allowTestData = false             # true en RECETTE seulement : active Api/jeu_de_test (rechargement du jeu de test)
travaux.apiKey = '...'                    # clé de signature des JWT
travaux.passwordSalt = '...'              # sel historique crypt() : NE PAS CHANGER (sert à lire les anciens mots de passe)
travaux.captcha = false                   # true pour activer le reCAPTCHA au login
travaux.siteCaptchaKey = ''
travaux.siteCaptchaSecretKey = ''
travaux.mailFromEmail = 'noreply@mon-domaine.fr'   # obligatoire pour l'envoi ; adresse de la boîte SMTP utilisée
travaux.mailFromName = 'ABCM Mulhouse-Lutterbach'
travaux.mailReplyTo = ''

# E-mail (Config\Email)
email.protocol = smtp
email.SMTPHost = mail.mon-domaine.fr
email.SMTPPort = 587
email.SMTPUser = 'noreply@mon-domaine.fr'  # adresse complète de la boîte
email.SMTPPass = '...'
email.SMTPCrypto = 'tls'
email.SMTPTimeout = 30
email.mailType = 'html'

# Barre de debug (développement uniquement) : écrit plusieurs Mo par requête dans writable/debugbar/
# app.debugToolbar = false
```

Règles à connaître :

- les valeurs avec espaces ou caractères spéciaux (`#`, `$`, `"`…) s'écrivent entre **guillemets simples** ; pas de `;` en fin de ligne ;
- une clé inconnue (absente de `Config\Travaux`) est ignorée silencieusement ;
- `SMTPUser` est l'adresse complète de la boîte et `mailFromEmail` doit appartenir au même domaine, sinon le serveur refuse l'expéditeur (`535`, `553`) ;
- ne **jamais** versionner ce fichier ni coller ses valeurs dans un ticket ou une conversation : en cas de fuite, changer le mot de passe SMTP,
  la clé reCAPTCHA secrète et `apiKey` (pas `passwordSalt`) ;
- en production, laisser `CI_ENVIRONMENT = production` : en développement, chaque requête écrit des journaux de debug et, si la barre de debug est
  active, un fichier JSON volumineux dans `writable/debugbar/` (ralentissements, disque saturé).

### 10.3 Serveur web (`.htaccess`)

Le document root du serveur web doit pointer sur **`public/`** (Plesk : *Paramètres d'hébergement > Racine du document* = `…/public`).
`public/.htaccess` route toutes les requêtes vers `index.php` et transmet l'en-tête `Authorization` (API JWT) ; il ne contient pas de directive
`Options` (refusée par certains hébergeurs : erreur 500 « Option FollowSymlinks not allowed here »). Le `.htaccess` de la racine redirige vers
`public/` pour les hébergements mutualisés dont le document root ne peut pas être changé ; dans ce cas, protéger aussi `.env`, `app/`, `writable/`,
`vendor/` et `database/` :

```apache
RedirectMatch 404 ^/(\.env|\.git|app|writable|vendor|database|tests|tools)(/|$)
```

Pas de `RewriteBase` quand le site est à la racine du domaine ; dans un sous-dossier, le renseigner (`RewriteBase /site_travaux/`).

### 10.4 Prérequis PHP

PHP ≥ 8.2 avec `intl` (classe `Locale`), `mbstring`, `curl`, `mysqli`, `gd` (redimensionnement des images), `json`. Le **PHP en ligne de commande**
(cron, `spark`) doit avoir les mêmes extensions que celui du site ([§ 9.5](#95-planification-sur-plesk--hébergement-mutualisé)).

## 11. Installation et déploiement

### 11.1 Installation

```bash
composer install --no-dev -o
cp env.example .env     # CI_ENVIRONMENT, app.baseURL, database.default.*, travaux.*, email.*
chmod -R u+rwX writable public/files public/data
```

1. **Document root** du serveur web : `public/` (un `.htaccess` racine redirige vers `public/` en mutualisé).
2. **Base de données** : voir le [§ 13](#13-migration-de-la-base-de-données) ; après une sauvegarde (`mysqldump`), exécuter **un seul script**, rejouable sans risque :
   ```bash
   mysql -u USER -p NOM_DE_LA_BASE < database/sql/mig_prod_vers_ci4.sql
   ```
   Il remplace `mig_family_import.sql`, `mig_cantine.sql`, `mig_alert_sent_at.sql`, `mig_cantine_generation_cols.sql`,
   `mig_sendmail_statut_error_text.sql` et `ci4_ci_sessions.sql`, et ajoute ce qu'aucun script ne créait :
   `famille.password` en `VARCHAR(255)` (le hash bcrypt fait 60 caractères, la colonne en faisait 50 : la connexion d'une famille
   dont le mot de passe est migré aurait échoué), `famille.alert_types` / `type_session`, `infos.civil_year`,
   `travaux.ref_mail_sent_at` / `alert_sent_at`, les tables `validation_tokens`, `famille_alerts`, `parameters`,
   `family_import`, `cantine_*`, et la table des sessions au format CI4 (**les sessions en cours sont perdues** : tout le monde
   se reconnecte). Aucune donnée n'est supprimée ; validé sur le schéma de production (MariaDB 10.11) avec smoke test et `tools/e2e.sh`.
   Ensuite, ouvrir *Droits > Contrôleurs* et lancer le scan pour déclarer les contrôleurs/actions ajoutés
   (`Cantine_controller`, import CSV des familles, `Parameters`…), puis les cocher pour les rôles concernés.
3. **Secrets** : reporter l'ancien `application/config/secured.php` dans le `.env` (voir `env.example`) :
   `API_KEY` → `travaux.apiKey`, `PASSWORD_SALT` → `travaux.passwordSalt`, `SITE_CAPTCHA_KEY`/`SITE_CAPTCHA_SECRET_KEY` →
   `travaux.siteCaptchaKey`/`travaux.siteCaptchaSecretKey`, `mail_from_*` → `travaux.mailFrom*`, `smtp_*` → `email.SMTP*`.
4. **Cron** : commandes spark, à planifier ainsi (le contrôleur `Cron` et ses URL n'existent plus) :
   ```cron
   */10 * * * *  cd /chemin/du/site && php spark cron:sendmail
   0 6 * * *     cd /chemin/du/site && php spark cron:ref-validation
   0 7 * * *     cd /chemin/du/site && php spark cron:session-alerts
   ```
   Appliquer aussi `database/sql/mig_alert_sent_at.sql` (colonne `travaux.alert_sent_at`, absente des migrations d'origine).
5. **Traductions** : les langues sont désormais identifiées par leur code (`fr`) et non par `french`.

### 11.2 Branches et environnements

| Branche | Environnement | URL |
|---|---|---|
| `develop` | Recette | https://regio.dev-asso.fr |
| `main` | Production | https://mulhouse-travaux.abcmzwei.eu/ |

### 11.3 Workflow Git Flow

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

### 11.4 Avant chaque mise en production

- [ ] Sauvegarde de la base de production effectuée
- [ ] `.env` à jour sur le serveur (`CI_ENVIRONMENT = production`, `app.baseURL`, `database.default.*`, `travaux.*`, `email.*`)
- [ ] Migrations SQL en attente exécutées ([§ 13](#13-migration-de-la-base-de-données))
- [ ] `composer install --no-dev -o` exécuté, PHP ≥ 8.2 avec `intl`
- [ ] Écriture possible sur `writable/`, `public/files/`, `public/data/`
- [ ] Tâches planifiées en place ([§ 9.5](#95-planification-sur-plesk--hébergement-mutualisé))
- [ ] Droits ACL à jour (scan des contrôleurs puis cases à cocher par rôle)
- [ ] Connexion admin et connexion famille testées ; liste de contrôle du [§ 15.7](#157-checklist-de-non-régression-avant-mise-en-production)

## 12. Migration CodeIgniter 3 → CodeIgniter 4

Le site tourne sur **CodeIgniter 4.7.x** (PHP ≥ 8.2) avec un code réécrit « à la CI4 » : plus de couche de compatibilité CI3, plus de `get_instance()`,
plus de `$this->load`. Cette réécriture (branche `ci4-natif`) remplace l'approche « shim » de la branche `develop`, qui reproduisait l'API CI3 au-dessus de CI4.

### 12.1 Ce qui est natif CodeIgniter 4

| Domaine | CI3 | CI4 (cette branche) |
|---|---|---|
| Cycle d'un contrôleur | constructeur + `parent::__construct()` | `initController()` puis `boot()` (réglages du contrôleur enfant) |
| Requête | `$this->input->post()` | `$this->request->getPost()` / `getFile()` / `isAJAX()` / `is('post')` |
| Session | `$this->session->userdata()` | `session()->get()/set()/setFlashdata()` (driver base de données) |
| Redirections | `redirect()` (exit) | `goTo()` : exception `RedirectException` + `redirect()->to()` |
| Réponses | `echo`, `header()`, `die` | objets `Response` (CSV, PDF, JSON), vues rendues avec `view()` |
| Base de données | `$this->db->select()->from()->get()->result()` | Query Builder `table()->select()->get()->getResult()` |
| Modèles | `CI_Model` + `$this->load->model()` | `CodeIgniter\Model` (`Core_model`) + `model()` |
| Bibliothèques | `$this->load->library()` | services partagés `service('acl' \| 'auth' \| 'renderObject' ...)` (`app/Config/Services.php`) |
| Hook ACL | `post_controller_constructor` | filtre `App\Filters\AclFilter` (`Config\Filters`) |
| Routage | automatique (`controleur/methode`) | `app/Config/Routes.php`, auto-routage désactivé |
| Validation | `CI_Form_validation` | `Controller::validateData()` (règles issues des schémas JSON) ; erreurs affichées par `validation_show_error($champ, 'alert')` (gabarits `app/Views/Validation`) |
| Client HTTP (SSO Delta) | `RestClient` (bibliothèque tierce) | `service('curlrequest')` |
| CSV / PDF | `header()` + `echo` | `$this->response->download()` |
| E-mail | `CI_Email` | `service('email')`, configuration `Config\Email` |
| Configuration | `config/app.php`, `secured.php` | `Config\Travaux` + variables du `.env` |
| Langues | `$lang['CLE']` + `$this->lang->line()` | tableaux `return [...]` dans `app/Language/fr/` + helper `tr()` |
| Pagination | `CI_Pagination` | `Model::paginate()` (pager partagé, segment 4) + gabarit `app/Views/Pager/app_bootstrap.php` (URL `.../list/page/N`) |
| Accès aux données | `$this->db->get()` + méthodes maison | `Core_model` : `find` / `insert` / `update` / `delete` / `first` / `findAll` / `paginate` natifs, `allowedFields` déduit des colonnes de la table |
| Horodatage | champs `created` / `updated` postés par le formulaire | `Model::$useTimestamps` : `created` à l'insertion, `updated` à chaque écriture, jamais fournis par le client |
| Tâches planifiées | contrôleur `Cron` (`index.php cron sendmail`) | commandes spark `cron:sendmail`, `cron:ref-validation`, `cron:session-alerts` (`app/Commands`) + `App\Libraries\CronJobs` |
| Menus | bibliothèque `Render_menu` (HTML dans le PHP) | View Cell `App\Cells\MenuCell` (droits ACL) + `app/Cells/menu.php` (balisage) |
| Gabarit de page | `template/head` + vue + `template/footer` | View Layouts : `layouts/page` étend `layouts/main` (`extend` / `section` / `renderSection`) |
| Journal d'erreurs | `MY_Exceptions` | `abort()` (404/400...), gestionnaire d'erreurs CI4 |

### 12.2 Correspondance des dossiers

| CI3 | CI4 |
|---|---|
| `system/` | `vendor/codeigniter4/framework` (Composer) |
| `index.php`, `assets/` | `public/index.php`, `public/assets/` (document root = `public/`) |
| `application/controllers`, `models`, `libraries`, `views`, `helpers` | `app/Controllers`, `Models`, `Libraries`, `Views`, `Helpers` |
| `application/language/french/*_lang.php` | `app/Language/fr/*.php` (tableaux) |
| `application/config/app.php`, `secured.php`, `<env>/` | `app/Config/Travaux.php` + `.env` |
| `application/hooks/Loginchecker` | `app/Filters/AclFilter.php` |
| `application/migrations/*.sql` | `database/sql/*.sql` |
| `application/cache`, `application/logs` | `writable/cache`, `writable/logs` |

### 12.3 Changements de comportement et corrections

* **Envois de fichiers** (`element_file`, `element_img`) : seules les extensions d'une liste blanche sont acceptées (jamais de `.php`),
  le nom est assaini, un fichier existant n'est jamais écrasé, une image est vérifiée (`getimagesize`) puis redimensionnée par le
  gestionnaire d'images natif (JPEG, PNG, GIF, WebP). Avant : un `.php` envoyé par le formulaire était exécutable depuis
  `public/files/` et un PNG provoquait une erreur 500. `public/files/.htaccess` interdit en plus l'exécution de scripts.
* Génération cantine : le journal utilisait des colonnes absentes de certaines bases (`date_deb`, `date_fin`, `nb_skipped`) ; appliquer
  `database/sql/mig_cantine_generation_cols.sql` (rejouable).

* `Familys_controller::import_history` n'avait jamais chargé `FamilyImport_model` (erreur 500) — corrigé.
* `Admwork_controller::MakePdf` visait une vue inexistante — corrigé ; le PDF est renvoyé par la réponse HTTP.
* `Api::login` s'appuyait sur `Acl::CheckLogin()` (qui retourne un message) : il appelle maintenant `Auth::Login()` et renvoie le JWT.
  `Api/login` est désormais une page invitée de l'ACL (appel possible sans session).
* Le CRUD générique n'est plus joignable pour `Home`, `Cantine_controller`, `Parameters`… (contrôleurs sans `$_autorize`) :
  c'était possible mais sans objet. `MY_Controller`/`BaseController` ne sont plus routables (404).
* Les champs « créé/modifié » sont posés par le modèle (24 h) et non plus par des champs cachés de formulaire ; `updated` est aussi renseigné à la création.
* Un champ vide non requis n'est plus soumis aux autres règles (comportement CI3 conservé explicitement : `permit_empty`).

### 12.4 Reste à faire et points d'attention

* Recette de bout en bout rejouable : `tools/e2e.sh <url> <login> <mot_de_passe>` (base de recette uniquement) couvre les envois de
  fichiers et d'images, l'import CSV des familles et la génération cantine (30 contrôles). Reste à recetter : envoi SMTP vers des
  adresses réelles et le SSO Delta (non joignables depuis le bac à sable).
* Les actions renvoient encore souvent le HTML par `echo` dans `render_view()` (CI4 le capture comme corps de réponse) ; elles
  peuvent progressivement `return view(...)`.
* Les identifiants de l'ancien `.env` versionné (`DB_DEV_*`, `PWD`) figurent dans l'historique git : à changer.
* Fichiers CI3 sans utilité (`readme.rst`, `contributing.md`, `license.txt`, dossier vide `application/`) : à supprimer.

---

## 13. Migration de la base de données

### 13.1 Quel script appliquer

Sur une base de **production existante** (schéma CodeIgniter 3), appliquer **un seul script**, `database/sql/mig_prod_vers_ci4.sql`.
Il est **idempotent** (rejouable sans risque : `IF NOT EXISTS` / `IF EXISTS`, MariaDB ≥ 10.3), ne supprime aucune donnée — à l'exception de la table
`ci_sessions`, recréée au format CodeIgniter 4 (**les sessions en cours sont perdues : tout le monde devra se reconnecter**) — et remplace les scripts
`mig_family_import.sql`, `mig_cantine.sql`, `mig_alert_sent_at.sql`, `mig_cantine_generation_cols.sql`, `mig_sendmail_statut_error_text.sql` et
`ci4_ci_sessions.sql`, qu'il ne faut donc **pas** appliquer en plus.

```bash
mysqldump -u USER -p NOM_DE_LA_BASE > sauvegarde_avant_migration.sql      # 1. sauvegarde obligatoire
mysql -u USER -p NOM_DE_LA_BASE < database/sql/mig_prod_vers_ci4.sql      # 2. migration
```

Avec phpMyAdmin : onglet *Importer*, choisir le fichier, format SQL. La base doit déjà contenir les tables de la version CodeIgniter 3
(`famille`, `travaux`, `infos`, `members`, `sendmail`, `sendmail_statut`…) : sur une base vide, charger d'abord le schéma d'origine.

### 13.2 Ce que fait la migration

| Domaine | Changement |
|---|---|
| `famille` | `password` élargi en `VARCHAR(255)` (un hash bcrypt fait 60 caractères, la colonne en faisait 50 : la réécriture du hash à la connexion échouait) ; ajout de `code_famille_abcm` (+ index), `to_deactivate`, `alert_types`, `type_session` |
| `members` | ajout de `code_membre_abcm` (+ index) et `classe` |
| `travaux` | ajout de `ref_mail_sent_at` et `alert_sent_at` (suivi des mails automatiques) |
| `infos` | ajout de `civil_year`, reprise de l'année de la session pour les inscriptions existantes |
| `sendmail`, `sendmail_statut` | `sendmail.error` accepte `NULL` ; `sendmail_statut.error` en `TEXT` (le journal SMTP dépassait 255 caractères et l'erreur réelle était tronquée) |
| Nouvelles tables | `validation_tokens`, `famille_alerts`, `parameters`, `family_import`, `cantine_config`, `cantine_inscriptions`, `cantine_generation` |
| `ci_sessions` | recréée au format CodeIgniter 4 (colonne `timestamp` de type TIMESTAMP, clé `id` + `ip_address`) |

Les jeux de caractères des tables (beaucoup de `latin1`) ne sont **pas** modifiés : la connexion applicative reste en `utf8`, comme avant la migration.

### 13.3 Après la migration

1. **Droits ACL** : *Droits > Contrôleurs > scan* pour déclarer les contrôleurs et actions ajoutés (`Cantine_controller`, import CSV des familles,
   `Parameters`…), puis cocher les droits pour les rôles concernés ; sans cela, les familles obtiennent « droits insuffisants » sur la cantine.
2. **Cantine** : renseigner la configuration des jours (`Cantine_controller/config`) avant de générer les créneaux.
3. **Tâches planifiées** : crons spark ([§ 9.5](#95-planification-sur-plesk--hébergement-mutualisé)).
4. **Contrôle** : `tools/smoke.sh` puis connexion admin et connexion famille ([§ 14](#14-vérifications-automatisées)).

### 13.4 Validation du script

Le script a été validé sur le schéma de production réel (MariaDB 10.11) : application deux fois de suite sans erreur, comparaison table par table avec le schéma
attendu par l'application (aucune table ni colonne manquante), puis exécution sur cette structure du test de fumée, de `tools/e2e.sh` (30 contrôles) et de la
connexion d'une famille dont le mot de passe est encore en MD5 (réécrit en bcrypt de 60 caractères).

### 13.5 Script complet

```sql
-- =====================================================================
-- Migration de la base de PRODUCTION (schema CodeIgniter 3) vers la version CodeIgniter 4.
-- Cible : MariaDB >= 10.3 (ADD COLUMN IF NOT EXISTS / CREATE TABLE IF NOT EXISTS).
-- IDEMPOTENT : peut etre rejoue sans risque. Aucune donnee n'est supprimee,
-- sauf la table ci_sessions (sessions en cours : tout le monde devra se reconnecter).
--
-- A FAIRE AVANT : sauvegarde complete (mysqldump) de la base.
-- Usage       : mysql -u USER -p NOM_DE_LA_BASE < database/sql/mig_prod_vers_ci4.sql
--
-- Cette migration remplace l'application manuelle de : mig_family_import.sql, mig_cantine.sql,
-- mig_alert_sent_at.sql, mig_cantine_generation_cols.sql, mig_sendmail_statut_error_text.sql,
-- ci4_ci_sessions.sql, et ajoute les tables/colonnes qu'aucun script du depot ne creait.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- 1. famille
--    * password : le hash bcrypt fait 60 caracteres (la colonne etait en VARCHAR(50) :
--      la reecriture du hash a la connexion echouait).
--    * colonnes de l'import CSV ABCM et des alertes e-mail.
-- ---------------------------------------------------------------------
ALTER TABLE `famille` MODIFY `password` VARCHAR(255) NOT NULL DEFAULT '';
ALTER TABLE `famille`
  ADD COLUMN IF NOT EXISTS `code_famille_abcm` VARCHAR(20) NULL AFTER `idfamille`,
  ADD COLUMN IF NOT EXISTS `to_deactivate` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `alert_types` VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS `type_session` INT(10) NULL;
ALTER TABLE `famille` ADD INDEX IF NOT EXISTS `idx_famille_code_abcm` (`code_famille_abcm`);

-- ---------------------------------------------------------------------
-- 2. members : identifiants ABCM et classe
-- ---------------------------------------------------------------------
ALTER TABLE `members`
  ADD COLUMN IF NOT EXISTS `code_membre_abcm` VARCHAR(50) NULL AFTER `id_fam`,
  ADD COLUMN IF NOT EXISTS `classe` VARCHAR(100) NULL AFTER `prenom`;
ALTER TABLE `members` ADD INDEX IF NOT EXISTS `idx_members_code_abcm` (`code_membre_abcm`);

-- ---------------------------------------------------------------------
-- 3. travaux / infos : annee scolaire des inscriptions, suivi des mails automatiques
-- ---------------------------------------------------------------------
ALTER TABLE `travaux`
  ADD COLUMN IF NOT EXISTS `ref_mail_sent_at` DATETIME NULL,
  ADD COLUMN IF NOT EXISTS `alert_sent_at` DATETIME NULL;

ALTER TABLE `infos` ADD COLUMN IF NOT EXISTS `civil_year` VARCHAR(255) NULL AFTER `nb_unites_valides_effectif`;
-- reprend l'annee de la session pour les inscriptions existantes
UPDATE `infos` i JOIN `travaux` t ON t.`id` = i.`id_travaux`
   SET i.`civil_year` = t.`civil_year`
 WHERE i.`civil_year` IS NULL AND t.`civil_year` IS NOT NULL;

-- ---------------------------------------------------------------------
-- 4. e-mails : le journal SMTP depasse 255 caracteres, et `error` n'a pas de valeur par defaut
-- ---------------------------------------------------------------------
ALTER TABLE `sendmail_statut` MODIFY `error` TEXT NULL;
ALTER TABLE `sendmail` MODIFY `error` VARCHAR(255) NULL DEFAULT NULL;

-- ---------------------------------------------------------------------
-- 5. Nouvelles tables
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `validation_tokens` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `id_travaux` INT(11) NOT NULL,
  `id_fam_ref` INT(11) NOT NULL,
  `token`      VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used_at`    DATETIME NULL,
  `created`    DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_token` (`token`),
  KEY `idx_travaux` (`id_travaux`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `famille_alerts` (
  `id`      INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_fam`  INT(11)      NULL,
  `id_type` VARCHAR(255) NULL,
  `created` DATETIME     NULL,
  `updated` DATETIME     NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fam` (`id_fam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `parameters` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `app_name`    VARCHAR(255) NULL,
  `slogan`      VARCHAR(255) NULL,
  `debug_app`   VARCHAR(255) NULL,
  `protocol`    VARCHAR(255) NULL,
  `smtp_host`   VARCHAR(255) NULL,
  `smtp_port`   VARCHAR(255) NULL,
  `smtp_user`   VARCHAR(255) NULL,
  `smtp_pass`   VARCHAR(255) NULL,
  `smtp_crypto` VARCHAR(255) NULL,
  `charset`     VARCHAR(255) NULL,
  `mailtype`    VARCHAR(255) NULL,
  `wordwrap`    VARCHAR(255) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `family_import` (
  `id`             INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename`       VARCHAR(255) NOT NULL,
  `stored_path`    VARCHAR(255) NOT NULL,
  `civil_year`     VARCHAR(20)  NOT NULL,
  `nb_lines`       INT(11) NOT NULL DEFAULT 0,
  `nb_families`    INT(11) NOT NULL DEFAULT 0,
  `nb_created`     INT(11) NOT NULL DEFAULT 0,
  `nb_updated`     INT(11) NOT NULL DEFAULT 0,
  `nb_marked`      INT(11) NOT NULL DEFAULT 0,
  `nb_reactivated` INT(11) NOT NULL DEFAULT 0,
  `nb_errors`      INT(11) NOT NULL DEFAULT 0,
  `report`         TEXT NULL,
  `imported_by`    INT(11) NULL,
  `created`        DATETIME NOT NULL,
  `updated`        DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_family_import_civil_year` (`civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Cantine (garde du midi)
CREATE TABLE IF NOT EXISTS `cantine_config` (
  `id`          INT(11) NOT NULL AUTO_INCREMENT,
  `id_day`      TINYINT(1) NOT NULL,
  `active`      TINYINT(1) NOT NULL DEFAULT 0,
  `nb_slots`    INT(2) NOT NULL DEFAULT 0,
  `ecole`       VARCHAR(3) NOT NULL DEFAULT 'B',
  `nb_units`    FLOAT(10) NOT NULL DEFAULT 1,
  `id_referent` VARCHAR(255) NULL,
  `heure_deb`   VARCHAR(10) NOT NULL DEFAULT '11:45',
  `heure_fin`   VARCHAR(10) NOT NULL DEFAULT '13:30',
  `civil_year`  VARCHAR(255) NULL,
  `created`     DATETIME NULL,
  `updated`     DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_day_ecole_year` (`id_day`, `ecole`, `civil_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantine_inscriptions` (
  `id`         INT(11) NOT NULL AUTO_INCREMENT,
  `date_garde` DATE NOT NULL,
  `id_famille` INT(11) NOT NULL,
  `ecole`      VARCHAR(3) NOT NULL DEFAULT 'B',
  `id_info`    INT(11) NULL,
  `id_travaux` INT(11) NULL,
  `civil_year` VARCHAR(255) NULL,
  `created`    DATETIME NULL,
  `updated`    DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_date_fam` (`date_garde`, `id_famille`),
  KEY `idx_date` (`date_garde`),
  KEY `idx_famille` (`id_famille`),
  KEY `idx_info` (`id_info`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `cantine_generation` (
  `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date_deb`   DATE         NULL,
  `date_fin`   DATE         NULL,
  `ecole`      VARCHAR(3)   NULL,
  `civil_year` VARCHAR(255) NULL,
  `nb_created` INT(11)      NULL,
  `nb_skipped` INT(11)      NULL,
  `created`    DATETIME     NULL,
  `updated`    DATETIME     NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------
-- 6. Sessions : CodeIgniter 4 stocke l'activite dans une colonne TIMESTAMP (CI3 : entier).
--    Les sessions en cours sont perdues : tous les utilisateurs devront se reconnecter.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`, `ip_address`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

### 13.6 Scripts historiques (`database/sql/`)

| Fichier | Contenu | Statut |
|---|---|---|
| `mig_prod_vers_ci4.sql` | Migration complète d'une base de production vers la version CodeIgniter 4 | **À utiliser** |
| `Migration.sql` | Migration initiale Joomla → CI3 : colonnes `created`/`updated`, `type_session`, `e_mail_comp` | Historique (déjà appliqué) |
| `mig_0910.sql` | `civil_year` et `archived` sur `unites`, `infos`, `travaux` | Historique (déjà appliqué) |
| `Acl.sql`, `Options.sql`, `groupes.sql`, `emails.sql`, `members.sql`, `capacitys.sql`, `Trombi.sql` | Dumps de tables d'origine | Historique |
| `mig_family_import.sql`, `mig_cantine.sql`, `mig_alert_sent_at.sql`, `mig_cantine_generation_cols.sql`, `mig_sendmail_statut_error_text.sql`, `ci4_ci_sessions.sql` | Morceaux de la migration, conservés pour mémoire | Remplacés par `mig_prod_vers_ci4.sql` |
| `jeu_de_test/` | Jeu de données de **recette** (comptes dont le mot de passe est public) — jamais en production | Recette uniquement |

## 14. Vérifications automatisées

```bash
php tools/check_classes.php                                             # toutes les classes référencées dans app/ se résolvent
vendor/bin/phpunit tests/unit                                           # structure (routes, langues, API CI3 absente, uploads), helpers
tools/smoke.sh http://localhost:8080 <login> <mot_de_passe> routes.txt  # GET de chaque route : erreurs 500, pages HTML vides
tools/e2e.sh   http://localhost:8080 <login> <mot_de_passe>             # envois de fichiers/images, import CSV, génération cantine
```

| Outil | Rôle |
|---|---|
| `tools/check_classes.php` | Analyse statique : toutes les classes citées dans `app/` existent (les classes du framework sont localisées sans être chargées) |
| `tests/unit` (PHPUnit, 36 tests) | Structure (contrôleurs routés, langues, API CI3 absente, schémas JSON, commandes spark, liste blanche d'envois, script SQL de migration identique au § 13), helpers, `PasswordAuthenticator` (bcrypt, `crypt()`, MD5, rehash), JWT, `Acl` (droits, `guestPages`), jeu de test (scripts limités à la plage 9000–9999) |
| `tools/smoke.sh` | Se connecte puis appelle chaque couple contrôleur/action en GET : signale les erreurs 5xx, les erreurs PHP affichées et les pages HTML incomplètes (sans `</html>`) |
| `tools/e2e.sh` (+ `tools/e2e/make_fixtures.php`) | Recette de bout en bout, **sur la base de recette uniquement** (écrit en base et dans `public/files`) : fichiers (`.txt` accepté, `.php` refusé, doublon renommé, `.php` inaccessible par HTTP), images (JPEG/PNG, faux `.jpg`, redimensionnement), import CSV (aperçu, application, accents CP1252, chemin forgé, historique), cantine (configuration, génération, journal, idempotence) |

Les échecs connus du test de fumée, antérieurs à la migration (pages `view/*` sans vue associée, `Jsondata` sans argument, `register_one/1` sur un
identifiant inexistant, `MakePdf` sans identifiant), ne sont pas des régressions (voir [§ 12.3](#123-changements-de-comportement-et-corrections)).

Variables de `tools/e2e.sh` : `DB="mysql -uroot -proot --default-character-set=utf8mb4 nom_de_la_base"` (client SQL utilisé pour vérifier les résultats).

---

## 15. Plan de test

> Application de gestion des travaux associatifs et de la participation des familles (ABCM Mulhouse-Lutterbach) — CodeIgniter 4 / PHP / MariaDB.

| | |
|---|---|
| **Périmètre** | Application web complète + API REST + tâches planifiées |
| **Environnement de recette** | `https://regio.dev-asso.fr` (branche `develop`) |
| **Environnement de production** | `https://mulhouse-travaux.abcmzwei.eu/` (branche `main`) |
| **Type de tests** | Recette fonctionnelle manuelle + tests de sécurité + non-régression, complétés par les vérifications automatisées du [§ 14](#14-vérifications-automatisées) |

### 15.1 Objectifs et périmètre

#### 15.1.1 Objectifs

- Vérifier que le **cycle de vie complet d'une session de travaux** fonctionne : création → publication → inscription famille → validation référent → contrôle admin → archivage.
- Garantir le **cloisonnement des droits** (`sys` / `fam` / invité) : aucune donnée ni action accessible hors de son rôle.
- Garantir la **justesse du décompte des unités** de participation, qui conditionne la restitution du chèque de caution des familles.
- Vérifier la **chaîne d'envoi des e-mails** (file `sendmail` + cron) et les notifications aux référents.
- Contrôler la **non-régression** des écrans refondus récemment (`Units_controller/valid`, agenda cantine, scan ACL, import familles).

#### 15.1.2 Dans le périmètre

| Module | Contrôleur |
|---|---|
| Authentification, compte, page d'accueil | `Home` |
| Travaux / sessions | `Admwork_controller` |
| Cantine (garde du midi) | `Cantine_controller` |
| Validation des unités | `Units_controller` |
| Familles, import, statistiques | `Familys_controller` |
| Organigramme, commissions, candidatures | `Orgchart_controller`, `GroupesMembers_controller`, `Candidatures_controller` |
| ACL (rôles, contrôleurs, actions, utilisateurs) | `Acl_*_controller` |
| E-mails, modèles, options, paramètres | `Sendmail_controller`, `Templates_controller`, `Options_controller`, `Parameters` |
| Fichiers publics, évènements | `Files_controller`, `Event_controller`, `Publics` |
| Traductions | `Translations_controller` |
| API REST | `Api` |
| Tâches planifiées | commandes spark `cron:*` (`app/Commands`, `CronJobs`) |

#### 15.1.3 Hors périmètre

- Le cœur du framework CodeIgniter 4 (`system/`) et les bibliothèques tierces (`assets/vendor/`).
- Le SSO **Delta Enfance** côté fournisseur : seule l'intégration côté application est testée (avec un compte Delta de test, ou en simulant les réponses de `service('curlrequest')`).
- L'infrastructure serveur (Apache, MySQL, SMTP) hors vérification de configuration.
- L'audit de sécurité approfondi (pentest) : seuls les contrôles listés en [§ 15.6.15](#15615-sec--sécurité) sont couverts.

---

### 15.2 Stratégie de test

#### 15.2.1 Niveaux de test

| Niveau | Contenu | Quand |
|---|---|---|
| **N0 — Smoke** | 12 cas critiques, 30 min ([§ 15.5](#155-campagne-de-smoke-test-30-min)) | Après chaque déploiement sur recette et en prod |
| **N1 — Fonctionnel par module** | Tous les cas P1 et P2 du [§ 15.6](#156-cas-de-test-détaillés) | Avant chaque merge `develop` → `main` |
| **N2 — Non-régression complète** | Tous les cas, y compris P3 | Avant chaque release majeure / début de campagne annuelle |
| **N3 — Sécurité** | [§ 15.6.15](#15615-sec--sécurité) | À chaque modification touchant l'ACL, l'auth ou un upload |
| **N4 — Performance** | [§ 15.6.17](#15617-per--performance-et-robustesse) | Avant une campagne annuelle, ou si volumétrie > 300 familles |

#### 15.2.2 Niveaux de priorité

| Priorité | Signification |
|---|---|
| **P1** | Bloquant — le module est inutilisable ou une donnée métier est fausse (unités, droits, envoi de mail). Test obligatoire à chaque livraison. |
| **P2** | Majeur — fonction dégradée, contournement possible. |
| **P3** | Mineur — confort, affichage, cas limite rare. |

#### 15.2.3 Rôles de test

| Rôle | Type | Utilisé pour |
|---|---|---|
| **Administrateur** | `sys` | Configuration, CRUD, validation des unités |
| **Famille standard** | `fam` | Inscriptions, consultation de son historique |
| **Famille référente** | `fam` + `trombi` | Validation des présences sur ses sessions |
| **Invité** | `none` | Pages publiques et validation par lien e-mail |

#### 15.2.4 Convention d'identification des cas

`<MODULE>-<NN>` — par exemple `TRV-07`. Chaque anomalie doit référencer l'identifiant du cas qui l'a révélée.

---

### 15.3 Environnement et prérequis

#### 15.3.1 Prérequis techniques

- [ ] Base de recette **restaurée à partir d'un dump anonymisé de production** (ou du jeu de données du [§ 15.4](#154-jeux-de-données-et-comptes-de-test)).
- [ ] Migration SQL appliquée : `database/sql/mig_prod_vers_ci4.sql` ([§ 13](#13-migration-de-la-base-de-données)).
- [ ] `.env` présent et renseigné (`travaux.apiKey`, `travaux.passwordSalt`, SMTP `email.*`).
- [ ] `.env` (`database.default.*`) pointant sur la base de **recette** et jamais sur la production.
- [ ] Droits d'écriture sur `writable/`, `public/files/`, `app/Language/`.
- [ ] `travaux.civilYear` (`.env`) cohérent avec les données de test (par défaut `2025-2026` ; adapter à la campagne en cours — `Api/jeu_de_test` reprend cette valeur).
- [ ] `travaux.maintenance = false`, `travaux.debugApp = none` (sauf test dédié) et `CI_ENVIRONMENT = production`.
- [ ] SMTP de recette pointant sur une **boîte de capture** (MailHog, Mailtrap ou alias interne) — jamais sur les adresses réelles des familles.

> ⚠️ **Règle absolue** : ne jamais lancer `php spark cron:sendmail` sur une base contenant les adresses réelles des familles avec un SMTP de production. Vider la table `sendmail` ou remplacer les adresses avant toute campagne de test.

#### 15.3.2 Navigateurs cibles

| Navigateur | Versions | Priorité |
|---|---|---|
| Chrome / Edge | 2 dernières | P1 |
| Firefox | 2 dernières | P1 |
| Safari macOS / iOS | 2 dernières | P2 |
| Chrome Android | dernière | P1 (les familles s'inscrivent majoritairement sur mobile) |

Résolutions : **360×740** (mobile), **768×1024** (tablette), **1440×900** (bureau).

---

### 15.4 Jeux de données et comptes de test

#### 15.4.1 Comptes

Comptes créés par `database/sql/jeu_de_test/10_jeu_de_test.sql` (domaine `@recette.local`). Le mot de passe est choisi au chargement (`charger_jeu_de_test.sh` ou `Api/jeu_de_test`) et commun à tous les comptes.

| Réf. | Login | Type | Rôle | Usage |
|---|---|---|---|---|
| `U-ADM` | `admin.recette@recette.local` | `sys` | admin (1) | Toutes les actions d'administration |
| `U-SYS2` | `restreint.recette@recette.local` | `sys` | rôle restreint (9001) | Vérifier le cloisonnement entre rôles `sys` (droits recopiés du rôle Famille) |
| `U-FAM1` | `famille1@recette.local` | `fam` | `role_famille` (2) | Famille école **M**, 2 enfants, 0 unité, abonnée aux alertes. **Déjà inscrite à `T-FUTUR`**, à `T-PASSE` et à `T-ACTION`. Second référent de session |
| `U-FAM2` | `famille2@recette.local` | `fam` | `role_famille` | Famille école **L**, unités déjà validées (T-VIEUX + 3 unités complémentaires), non abonnée aux alertes, candidature en attente |
| `U-REF` | `referent@recette.local` | `fam` | `role_famille` | Référente principale de `T-FUTUR`, `T-PASSE`, `T-ACTION`… ; porte les 3 jetons de validation |
| `U-LEGACY` | `legacy@recette.local` | `fam` | `role_famille` | Mot de passe encore en hash **MD5** (test de migration bcrypt) |
| `U-FAM3` | `famille3@recette.local` | `fam` | `role_famille` | Famille école **B** (les deux), 3 enfants ; cantine du vendredi, T-PLEIN, T-PASSE |
| `U-FAM4` | `famille.import@recette.local` | `fam` | `role_famille` | Cible de l'import CSV (modifiée par `import_ok.csv`) |
| `U-DELTA` | compte Delta de test | `fam` | — | Connexion SSO — **non créé par le jeu de test** : prévoir un compte Delta réel (AUT-06) |

#### 15.4.2 Données métier

Dates relatives au jour du chargement (J). Détail complet : `database/sql/jeu_de_test/README.md` et onglet « Jeu de données » du classeur `plan_tests_site_travaux.xlsx`.

| Réf. | Objet | Caractéristiques |
|---|---|---|
| `T-BROUILLON` | `travaux` 9001 | `statut=0`, J+20 — ne doit **pas** apparaître côté famille |
| `T-FUTUR` | `travaux` 9002 | `type=TRA`, `statut=1`, J+15, `nb_inscrits_max=4`, `nb_units=2`, `accespar=M`, référent = `U-REF` ; **`U-FAM1` y est déjà inscrite** |
| `T-PLEIN` | `travaux` 9003 | `nb_inscrits_max=2`, déjà 2 inscrits (`U-FAM2`, `U-FAM3`) — test de saturation |
| `T-PASSE` | `travaux` 9004 | J-5, non archivé, 3 inscrits non validés — validation référent |
| `T-VIEUX` | `travaux` 9005 | J-45, `archived=0` — test d'archivage automatique (déjà validée pour `U-FAM2`) |
| `T-URG` | `travaux` 9006 | `type=URG`, sans date — ne doit **jamais** être archivé |
| `T-LUT` | `travaux` 9007 | `accespar=L` — invisible pour `U-FAM1` (école M) |
| `T-AN-1` | `travaux` 9008 | `civil_year` précédente — cloisonnement par année |
| `T-ACTION` | `travaux` 9009 | J-3, type de session « Action », 2 inscrits non validés — file de validation (UNI-01) |
| `T-REF-KO` | `travaux` 9010 | J+6, référent à chaîne rompue (`id_fam` non numérique) — aucun mail, aucune erreur (CRO-08, ORG-08) |
| `T-DERNIERE-PLACE` | `travaux` 9011 | J+8, 3 places dont 2 prises (`U-FAM2`, `U-FAM3`), second référent — concurrence (TRV-11) |
| `T-CAN` | `travaux` 9020–9024 | `type=can`, semaine en cours (lundi, mardi, jeudi, vendredi ; vendredi saturé) |
| `G-COM` | `groupes` 9001 | Commission TRAVAUX (`type=com`) : 4 rattachements `trombi` dont 3 référents proposables, et 1 candidature en attente (`U-FAM2`) |
| `TOK-OK` / `TOK-EXP` / `TOK-USED` | `validation_tokens` 9001–9003 | Jetons référent valide (T-PASSE), expiré (T-ACTION), déjà utilisé (T-VIEUX) |
| `MAIL-3` | `sendmail` 9001–9003 | 3 mails en attente (`statut=0`) |
| `CSV-OK` | fichier | `csv/import_ok.csv` : 4 familles dont 2 nouvelles (`ZZIMPORT01`, `ZZIMPORT02`) et 1 modifiée (`ZZTEST006`) |
| `CSV-KO` | fichier | `csv/import_ko.csv` : lignes tronquées, code famille vide, e-mail manquant |

#### 15.4.3 Requêtes SQL utiles

```sql
-- Comptes encore en hash legacy (doivent migrer au prochain login)
SELECT id, login FROM famille
 WHERE password NOT LIKE '$2y$%' AND password NOT LIKE '$2a$%';

-- File d'envoi en attente
SELECT id, reference, email, statut FROM sendmail WHERE statut = 0;

-- Tokens de validation référent actifs
SELECT id_travaux, id_fam_ref, expires_at, used_at FROM validation_tokens
 ORDER BY created DESC;

-- Contrôle du décompte d'unités d'une famille
SELECT SUM(nb_unites_valides_effectif) FROM infos
 WHERE id_famille = :id AND civil_year = '2025-2026';
```

---

### 15.5 Campagne de smoke test (30 min)

À exécuter **après chaque déploiement**, recette comme production. Un seul échec bloque la mise en service.

| # | Cas | Attendu |
|---|---|---|
| 1 | `AUT-01` — Connexion admin | Redirection vers le tableau de bord, menu `sys` complet |
| 2 | `AUT-04` — Connexion famille | Menu `fam`, pas d'entrée d'administration |
| 3 | `TRV-01` — `Admwork_controller/register` | Liste des sessions à venir, sans erreur PHP |
| 4 | `TRV-08` — Désinscription (`U-FAM1` est déjà inscrite à `T-FUTUR`) | Ligne supprimée, place libérée |
| 5 | `TRV-05` — Inscription à une session | Ligne créée dans `infos`, compteur d'inscrits incrémenté |
| 6 | `CAN-03` — Agenda cantine semaine courante | Grille lundi-vendredi affichée |
| 7 | `UNI-01` — `Units_controller/valid` | Cartes par session, filtres et compteurs opérationnels |
| 8 | `FAM-01` — `Familys_controller/histo` | Total d'unités correct pour `U-FAM1` |
| 9 | `ORG-01` — `Orgchart_controller/orga` | Organigramme affiché |
| 10 | `ACL-01` — URL admin en session famille | Redirection vers `Home/no_right` |
| 11 | `MAI-01` — Déclenchement d'une notification | Ligne `statut=0` créée dans `sendmail` |
| 12 | `AUT-08` — Déconnexion | Session détruite, retour au formulaire de connexion |

---

### 15.6 Cas de test détaillés

> Format : **ID · Priorité · Rôle** — Objectif / Étapes / Résultat attendu.

#### 15.6.1 AUT — Authentification

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| AUT-01 | P1 | sys | Connexion admin locale | `Home/login`, saisir `U-ADM` + mot de passe valide | Session ouverte, `type=sys`, redirection `/Home`, menu admin visible |
| AUT-02 | P1 | — | Mot de passe erroné | Login valide + mot de passe faux | Message d'erreur générique, **pas** de distinction « login inconnu » vs « mot de passe faux », aucune session |
| AUT-03 | P1 | — | Login inexistant | Saisir un login inconnu | Même message et **temps de réponse comparable** à AUT-02 (protection contre l'énumération de comptes) |
| AUT-04 | P1 | fam | Connexion famille | `U-FAM1` | `type=fam`, menu famille sans entrées d'administration |
| AUT-05 | P1 | fam | Migration de mot de passe legacy | Se connecter avec `U-LEGACY` (hash MD5/`crypt`) | Connexion acceptée **et** `famille.password` réécrit en `$2y$...` en base |
| AUT-06 | P2 | fam | Connexion SSO Delta | Choisir `type_cnx = DELTA`, identifiants Delta de test | Compte famille local créé ou synchronisé, mot de passe stocké en bcrypt |
| AUT-07 | P2 | — | Forçage `NORM` pour `admin` | Se connecter avec le login `admin` en sélectionnant `DELTA` | L'application force `type_cnx=NORM` et authentifie en local |
| AUT-08 | P1 | tous | Déconnexion | `Home/logout` | Session détruite, cache ACL invalidé, accès à une page protégée redirigé vers le login |
| AUT-09 | P2 | — | Captcha activé | `travaux.captcha = true`, soumettre sans résoudre le captcha | Connexion refusée, message d'erreur captcha affiché |
| AUT-10 | P2 | — | Champs obligatoires | Soumettre le formulaire vide | Messages de validation en français, pas d'erreur PHP |
| AUT-11 | P2 | tous | Mode maintenance | `travaux.maintenance = true`, visiter le site | Page `Home/maintenance` pour tous ; l'admin conserve un accès (à confirmer avec le métier) |
| AUT-12 | P2 | fam | Changement de mot de passe | `Home/myaccount` → nouveau mot de passe | Reconnexion possible avec le nouveau seulement ; l'ancien est refusé |
| AUT-13 | P3 | fam | Expiration de session | Rester inactif au-delà de la durée de session, puis cliquer | Redirection vers le login sans erreur PHP |
| AUT-14 | P2 | — | Page publique `Home/about` | Accès sans être connecté | Page affichée (elle figure dans `guestPages`) |

#### 15.6.2 ACL — Droits et rôles

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| ACL-01 | P1 | fam | Accès direct à une URL admin | En session `fam`, appeler `Familys_controller/list` | Redirection `Home/no_right`, aucune donnée exposée |
| ACL-02 | P1 | — | Accès sans session | Appeler `Units_controller/valid` déconnecté | Redirection vers le login, pas de fuite de contenu |
| ACL-03 | P1 | — | Pages invitées (`guestPages`) | Appeler `home/login`, `home/no_right`, `home/about`, `admwork_controller/validate_by_token/<token>` | Accessibles sans session ; **toute autre** route exige une session |
| ACL-04 | P1 | sys | Matrice des droits | `Acl_roles_controller/set_rules/<id>`, retirer un droit, sauvegarder | Le droit disparaît immédiatement pour le rôle concerné (après reconnexion si le cache session persiste — comportement à documenter) |
| ACL-05 | P1 | sys | Invalidation du cache | Modifier les droits d'un rôle, se déconnecter/reconnecter | Les nouveaux droits s'appliquent sans intervention en base |
| ACL-06 | P2 | sys | Scan des contrôleurs | `Acl_controllers_controller/scan` | Les contrôleurs/actions présents dans le code mais absents de l'ACL sont listés ; les alertes (actions orphelines, contrôleurs supprimés) sont cohérentes |
| ACL-07 | P2 | sys | Application du scan | Sélectionner quelques éléments détectés puis appliquer | Insertion en `acl_controllers` / `acl_actions` conforme à la sélection, rien d'autre modifié |
| ACL-08 | P2 | sys | Ajout d'action en lot | `Acl_controllers_controller/bulk_add_action` | Action ajoutée à tous les contrôleurs choisis, sans doublon |
| ACL-09 | P1 | sys | Secure by default | Ajouter un contrôleur non déclaré en ACL et l'appeler | Accès refusé par défaut (`DontCheck = FALSE`) |
| ACL-10 | P2 | sys | Comptes admin | `Acl_users_controller` : créer, modifier, supprimer un compte `sys` | CRUD fonctionnel, mot de passe stocké en bcrypt, impossible de supprimer le dernier admin (ou anomalie à ouvrir si possible) |
| ACL-11 | P1 | fam | Cloisonnement inter-familles (IDOR) | En session `U-FAM1`, appeler `Familys_controller/histo` puis forcer un `id` d'une autre famille dans l'URL | Aucune donnée d'une autre famille accessible |

#### 15.6.3 TRV — Travaux

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| TRV-01 | P1 | fam | Liste des sessions | `Admwork_controller/register` | Seules les sessions `statut=1`, non archivées, de la `civil_year` courante et compatibles avec l'école de la famille apparaissent |
| TRV-02 | P1 | fam | Brouillon masqué | Chercher `T-BROUILLON` dans la liste | Absent de la vue famille ; visible uniquement en `Admwork_controller/list` (admin) |
| TRV-03 | P1 | fam | Filtrage par école | En `U-FAM1` (école M), chercher `T-LUT` (`accespar=L`) | Session non proposée ; les sessions `accespar=B` restent visibles |
| TRV-04 | P2 | fam | Cloisonnement par année | Chercher `T-AN-1` | Absent de la liste courante |
| TRV-05 | P1 | fam | Inscription | En `U-FAM1`, **après TRV-08** (le jeu l'inscrit déjà à `T-FUTUR`) : `register_one/<T-FUTUR>`, `type_participant = Mr` | `infos` créé avec `nb_participants=1`, unités prévues = `nb_units`, redirection avec confirmation |
| TRV-06 | P1 | fam | Inscription couple | Même session, `type_participant = Both` | `nb_participants = 2` (règle métier forcée côté serveur, indépendante du POST) |
| TRV-07 | P1 | fam | Session complète | S'inscrire sur `T-PLEIN` | Message `TOO_MANY_PEOPLE`, **aucune** ligne créée |
| TRV-08 | P1 | fam | Désinscription | Se désinscrire de `T-FUTUR` | Ligne `infos` supprimée, place recomptée, session à nouveau proposée |
| TRV-09 | P1 | fam | Double inscription | S'inscrire deux fois à la même session | Refus (`ALREADY_REGISTERED`), pas de doublon en base |
| TRV-10 | P2 | fam | Session passée | Tenter de s'inscrire à `T-PASSE` | Refus (`PAST_DATE`) |
| TRV-11 | P2 | fam | Concurrence sur la dernière place | Deux navigateurs, deux familles non inscrites (`U-FAM1` et `U-REF`), dernière place de `T-DERNIERE-PLACE`, valider quasi simultanément | Une seule inscription acceptée ; l'autre reçoit `TOO_MANY_PEOPLE` (à défaut, ouvrir une anomalie sur le contrôle de capacité) |
| TRV-12 | P1 | sys | CRUD session | `Admwork_controller/add`, `edit`, `delete` | Création, modification et suppression conformes ; validations de champs actives |
| TRV-13 | P2 | sys | Inscription pour le compte d'une famille | `managed_one/<id>` | Inscription créée au nom de la famille choisie, **sans** notification « inscription famille » au référent |
| TRV-14 | P2 | sys | Génération PDF | `MakePdf/<id_work>` | PDF de la feuille de session généré, lisible, avec les inscrits et les horaires |
| TRV-15 | P2 | sys | Archivage automatique | Positionner `T-VIEUX` à J-45, supprimer `writable/cache/last_archive_run.txt`, visiter `register` | `T-VIEUX` passe à `archived=1` ; `T-URG` reste actif ; le fichier de throttle est recréé |
| TRV-16 | P3 | sys | Throttle d'archivage | Rappeler `register` dans la foulée | Pas de nouveau balayage d'archivage le même jour |
| TRV-17 | P2 | fam | Filtres de la liste | Utiliser recherche, type, date sur `register` | Résultats cohérents, compteurs mis à jour, pas d'erreur si aucun résultat |
| TRV-18 | P2 | sys | Statistiques participants | `Admwork_controller/worker` | Totaux cohérents avec `infos` (contrôle SQL croisé) |
| TRV-19 | P3 | fam | Session sans date (`URG`) | Ouvrir `T-URG` | Affichage sans date, inscription possible, pas d'erreur de formatage |

#### 15.6.4 REF — Validation par le référent

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| REF-01 | P1 | fam réf. | Mes sessions | `Admwork_controller/my_sessions` en `U-REF` | Seules les sessions dont l'utilisateur est référent sont listées |
| REF-02 | P1 | fam réf. | Validation connectée | `validate_one/<T-PASSE>`, cocher les présents, valider | `infos.nb_unites_valides_effectif` = `nb_units × nb_participants` pour les présents, `0` pour les absents |
| REF-03 | P1 | fam | Non-référent | En `U-FAM1`, appeler `validate_one/<T-PASSE>` | Accès refusé, aucune donnée de validation exposée |
| REF-04 | P1 | invité | Validation par lien e-mail | Ouvrir `validate_by_token/<token valide>` sans session | Écran de validation accessible, limité à la session concernée |
| REF-05 | P1 | invité | Token invalide | Token inexistant ou altéré d'un caractère | Page d'erreur `Admwork_controller_token_error`, aucune donnée affichée |
| REF-06 | P1 | invité | Token expiré | Forcer `expires_at` dans le passé | Refus explicite |
| REF-07 | P1 | invité | Token déjà utilisé | Rejouer un token dont `used_at` est renseigné | Refus (ou lecture seule) ; **aucune** double validation d'unités |
| REF-08 | P2 | fam réf. | Ajout d'un participant | Depuis l'écran référent, ajouter une famille présente non inscrite | `infos` créé, capacité maximale respectée |
| REF-09 | P2 | fam réf. | Retrait d'un participant | Retirer une famille inscrite mais absente | Ligne supprimée ou unités mises à 0, cohérent avec la règle métier |
| REF-10 | P1 | invité | Cloisonnement du token | Depuis un token de la session A, tenter d'agir sur la session B (id forcé dans le POST) | Action refusée |
| REF-11 | P2 | fam réf. | Idempotence | Valider deux fois de suite la même session | Les unités ne sont pas doublées |

#### 15.6.5 CAN — Cantine

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| CAN-01 | P1 | sys | Paramétrage | `Cantine_controller/config` : jours actifs, `nb_slots`, école, `nb_units`, puis `save_config` | Configuration enregistrée et relue à l'identique |
| CAN-02 | P1 | sys | Génération des créneaux | `generate` sur une période donnée | Une ligne `travaux` `type=can` par jour actif ; pas de doublon si on relance sur la même période |
| CAN-03 | P1 | fam | Agenda hebdomadaire | `register/0`, puis `register/1` et `register/-1` | Grille lundi-vendredi, navigation semaine suivante / précédente cohérente, dates en français |
| CAN-04 | P1 | fam | Inscription à un créneau | `register_one/<T-CAN>` | Inscription enregistrée, retour sur la semaine du créneau (bon `week_offset`) |
| CAN-05 | P1 | fam | Créneau complet | S'inscrire sur un créneau saturé | Message `SESSION_FULL` en flash, aucune inscription |
| CAN-06 | P1 | fam | Créneau passé | S'inscrire sur un jour antérieur à aujourd'hui | Refus `PAST_DATE` |
| CAN-07 | P1 | fam | Désinscription | `unregister_one/<T-CAN>` avant validation | Inscription supprimée |
| CAN-08 | P1 | fam | Désinscription après validation | Positionner `nb_unites_valides_effectif > 0` puis se désinscrire | Refus `ALREADY_VALIDATED`, message affiché, ligne conservée |
| CAN-09 | P2 | sys | Accès admin à l'agenda | `register` en `sys` | Vue accessible en lecture ; les actions d'inscription réservées aux familles sont neutralisées |
| CAN-10 | P2 | fam | Filtre par école | Famille école L sur une config école M | Créneaux non proposés |
| CAN-11 | P3 | fam | Fin d'année scolaire | Naviguer au-delà de la fin de l'année civile configurée | Pas de créneau au-delà, pas d'erreur de date |

#### 15.6.6 UNI — Unités

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| UNI-01 | P1 | sys | Écran de validation | `Units_controller/valid` | Une **carte par session** (y compris pour les sessions de type « Action » — régression corrigée), en-tête avec date, titre, type, référent, unités, inscrits |
| UNI-02 | P1 | sys | Validation en lot | Cocher plusieurs lignes → `valids` → confirmer | `nb_unites_valides_effectif` mis à jour uniquement pour les lignes cochées |
| UNI-03 | P1 | sys | Cases masquées décochées | Cocher une ligne, appliquer un filtre qui la masque, valider | La ligne masquée **n'est pas** soumise |
| UNI-04 | P2 | sys | Filtres rapides | Recherche libre, filtre date, filtre famille, filtre type, réinitialisation | Filtrage client immédiat, compteurs (sessions visibles / unités totales / unités sélectionnées) recalculés |
| UNI-05 | P2 | sys | Persistance des filtres | Filtrer, aller sur `valids`, revenir sur `valid` | Filtres restaurés depuis `localStorage` (`uv_filters_v1`) |
| UNI-06 | P2 | sys | Tout cocher tri-state | Cocher partiellement une carte | La case « tout cocher » passe en état indéterminé ; elle ignore les lignes masquées |
| UNI-07 | P1 | sys | Barre d'action sticky | Aucune ligne cochée | Bouton « Valider » désactivé ; il s'active dès la première case cochée |
| UNI-08 | P2 | sys | Lien « Ouvrir » | Cliquer sur le lien d'une carte | Ouverture de `Admwork_controller/register_one/<id>` de la bonne session |
| UNI-09 | P1 | sys | Unités complémentaires | `Units_controller/list` : ajouter une unité hors session avec commentaire | Ligne `unites` créée, prise en compte dans le total de la famille |
| UNI-10 | P1 | sys/fam | Total consolidé | Comparer le total affiché en `Familys_controller/histo` à la somme SQL `infos` + `unites` | Écart nul |
| UNI-11 | P2 | sys | Seuil d'unités attendues | Famille à moins de `unit_todo` (20) unités puis au-dessus | Indicateur visuel cohérent de part et d'autre du seuil |
| UNI-12 | P3 | sys | Liste vide | Filtrer sans résultat | Message explicite, pas de tableau vide brut ni d'erreur JS |

#### 15.6.7 FAM — Familles

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| FAM-01 | P1 | fam | Mon historique | `Familys_controller/histo` | Unités validées, unités restantes, sessions à venir de **la famille connectée uniquement** |
| FAM-02 | P1 | sys | Historique d'une famille | `histo` en `sys` puis sélection d'une famille | Données de la famille choisie, changement de famille possible |
| FAM-03 | P1 | sys | CRUD famille | `list` / `add` / `edit` / `delete` | Validations actives (email, champs requis), suppression avec confirmation |
| FAM-04 | P2 | sys | E-mails complémentaires | Ajouter deux e-mails secondaires à une famille | Lignes `emails` liées à `id_fam`, restituées à l'édition |
| FAM-05 | P2 | sys | Compétences | `skills` : filtrer par compétence | Seules les familles portant la compétence apparaissent |
| FAM-06 | P1 | sys | Statistiques | `stats` | Totaux par famille cohérents avec le contrôle SQL |
| FAM-07 | P2 | sys | Export des statistiques | `stats_export` | Fichier téléchargé, séparateur et encodage corrects, accents non altérés à l'ouverture dans Excel/LibreOffice |
| FAM-08 | P2 | sys | Mise à jour en masse | `MassUpdate` sur une sélection | Seules les familles sélectionnées sont modifiées ; contrôle SQL avant/après |
| FAM-09 | P2 | sys | Chèques de caution | `check/<id_fam>` | Saisie et consultation du statut du chèque |
| FAM-10 | P2 | sys | Détail des unités | `units/<id_fam>` | Détail ligne à ligne, total identique à `histo` |
| FAM-11 | P2 | fam | Préférences d'alerte | `Home/myaccount` : activer/désactiver un type d'alerte | Enregistré dans `alert_pref`, respecté par les notifications ([§ 15.6.10](#15610-mai--e-mails-et-notifications)) |
| FAM-12 | P3 | sys | Volumétrie | Liste avec > 300 familles | Pagination fonctionnelle, temps de réponse < 3 s |

#### 15.6.8 IMP — Import CSV des familles

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| IMP-01 | P1 | sys | Import valide — prévisualisation | `Familys_controller/import` avec `CSV-OK` | Écran de différentiel : créations, modifications, membres ajoutés/retirés, **aucune écriture** en base à ce stade |
| IMP-02 | P1 | sys | Application de l'import | `import_apply` après prévisualisation | Base conforme au différentiel affiché, ni plus ni moins |
| IMP-03 | P1 | sys | Fichier invalide | Importer `CSV-KO` | Erreur explicite, aucune modification en base |
| IMP-04 | P1 | sys | Idempotence | Réimporter `CSV-OK` immédiatement après IMP-02 | Différentiel vide, aucun doublon de famille ni de membre |
| IMP-05 | P2 | sys | Normalisation | Fichier avec e-mails en majuscules, espaces parasites, écoles en libellé long | E-mails normalisés en minuscules, valeurs nettoyées, école mappée sur `M`/`L`/`B` |
| IMP-06 | P2 | sys | Historique | `import_history` | Historique des imports avec date, opérateur et volumétrie |
| IMP-07 | P2 | sys | Encodage | Fichier en ISO-8859-1 et fichier en UTF-8 avec BOM | Accents corrects en base dans les deux cas (ou refus explicite si un encodage n'est pas supporté) |
| IMP-08 | P2 | sys | Fichier non CSV | Téléverser un `.exe` renommé en `.csv`, puis un fichier de 20 Mo | Rejet propre, pas de fichier conservé sur le serveur |
| IMP-09 | P1 | sys | Préservation des données locales | Importer un CSV pour une famille ayant des unités et des inscriptions | Unités, inscriptions et mot de passe locaux **non écrasés** |

#### 15.6.9 ORG — Organigramme et commissions

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| ORG-01 | P1 | tous | Vue publique | `Orgchart_controller/orga` | Commissions et membres affichés, photos ou image de remplacement si absente |
| ORG-02 | P2 | tous | Organisation | `organisation` | Vue structurelle correcte, classifications (RT, ME, BU, PE, VP) respectées |
| ORG-03 | P2 | sys | CRUD commission | `list` / `add` / `edit` / `delete` | CRUD conforme, aucun membre orphelin après suppression |
| ORG-04 | P2 | sys | Mise en avant | `featured/<id>` | La commission remonte en tête, une seule mise en avant à la fois (ou comportement documenté) |
| ORG-05 | P2 | sys | Membres | `GroupesMembers_controller` : créer un membre relié à une famille | Chaîne `trombi → groupes_member → famille` cohérente (test croisé avec REF-01) |
| ORG-06 | P2 | fam | Candidature | Déposer une candidature à `G-COM` | Ligne `candidatures` créée, notification au responsable ([§ 15.6.10](#15610-mai--e-mails-et-notifications)) |
| ORG-07 | P2 | sys | Traitement d'une candidature | `Candidatures_controller` : accepter puis refuser | Statut mis à jour, membre ajouté à la commission si accepté |
| ORG-08 | P3 | sys | Chaîne référent incohérente | Mettre un `groupes_member.id_fam` non numérique | Aucune ligne remontée, pas d'erreur SQL (conversion implicite documentée) |
| ORG-09 | P3 | tous | Détail d'un membre | `view_one/<id>` | Fiche complète, données personnelles non exposées aux invités |

#### 15.6.10 MAI — E-mails et notifications

| ID | P | Rôle | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|---|
| MAI-01 | P1 | fam | Notification d'inscription | S'inscrire à `T-FUTUR` en tant que famille | Ligne `sendmail` `statut=0` adressée au référent, objet et corps renseignés |
| MAI-02 | P1 | sys | Pas de notification en saisie admin | Inscrire une famille via `managed_one` | **Aucune** notification d'inscription famille générée |
| MAI-03 | P2 | fam | Notification de désinscription | Se désinscrire | Notification correspondante en file |
| MAI-04 | P2 | fam | Notification cantine | S'inscrire puis se désinscrire d'un créneau cantine | Notifications cohérentes avec le type de session |
| MAI-05 | P2 | fam | Notification de candidature | Déposer une candidature | Notification au responsable de commission |
| MAI-06 | P1 | — | Destinataire sans e-mail | Référent sans adresse valide | Erreur tracée (fallback), pas d'exception, le reste du traitement se poursuit |
| MAI-07 | P2 | fam | Respect des préférences | Désactiver un type d'alerte puis déclencher l'évènement | Aucun mail de ce type n'est mis en file |
| MAI-08 | P2 | sys | File d'envoi | `Sendmail_controller/list` | Liste, filtres et statuts (0 en attente / 1 envoyé / 2 erreur) lisibles |
| MAI-09 | P2 | sys | Modèles de message | `Templates_controller` : modifier un modèle, déclencher l'évènement associé | Le contenu du mail reflète le modèle, variables substituées |
| MAI-10 | P2 | — | Rendu HTML | Ouvrir un mail capturé | HTML correct sur client bureau et webmail, liens absolus et cliquables |
| MAI-11 | P1 | — | Sécurité des liens de validation | Inspecter le lien référent envoyé | URL en HTTPS, token de 64 caractères hexadécimaux, non devinable |

#### 15.6.11 CRO — Tâches planifiées

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| CRO-01 | P1 | Envoi de la file | `php spark cron:sendmail 10` avec 3 mails en attente | 3 mails partis, `statut=1`, log en `sendmail_statut` |
| CRO-02 | P1 | Gestion d'erreur SMTP | Config SMTP volontairement fausse | `statut=2`, message d'erreur enregistré, pas d'interruption du lot |
| CRO-03 | P1 | Taille du lot | `cron sendmail 2` avec les 3 mails du jeu en attente (recharger le jeu après CRO-01) | Exactement 2 traités, 1 reste en `statut=0` |
| CRO-04 | P1 | Verrou d'exécution | Lancer deux exécutions simultanées | La seconde s'arrête immédiatement (« Un autre processus est déjà en cours », code retour 1) |
| CRO-05 | P2 | Verrou résiduel | Tuer (`kill -9`) une exécution en cours, puis relancer | Le verrou `flock` (`writable/cron.lock`) est libéré par le système : la relance fonctionne |
| CRO-06 | P1 | Mails de validation référent | `php spark cron:ref-validation 10` (sessions MEN/TRA de J à J+10 avec référent : `T-DERNIERE-PLACE` J+8, `T-PLEIN` J+10) | 2 tokens créés (expiration J+30), 2 mails poussés en file, `travaux.ref_mail_sent_at` renseigné ; avec l'argument 7 par défaut, seule `T-REF-KO` (J+6) est candidate (voir CRO-08) |
| CRO-07 | P1 | Non-duplication | Relancer immédiatement CRO-06 | Aucun nouveau token ni mail pour la même session |
| CRO-08 | P2 | Référent introuvable | Session sans référent ou chaîne `trombi` cassée (`T-REF-KO`) | Session ignorée sans erreur, trace en log |
| CRO-09 | P2 | Alertes nouvelles sessions | `php spark cron:session-alerts` | Familles concernées notifiées, filtrage par école et préférences respecté |
| CRO-10 | P2 | Exécution web impossible | Appeler `/cron/sendmail` via le navigateur | Page introuvable (404) : les tâches ne sont plus exposées en HTTP |
| CRO-11 | P3 | Volumétrie | 500 mails en file, lots successifs | Pas de fuite mémoire ni de timeout ; durée mesurée et notée |

#### 15.6.12 API — API REST

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| API-01 | P1 | Connexion | `POST /api/login` avec identifiants + `api-key` valides | `200` + JWT, `expireAt`, `type`, `role_id` |
| API-02 | P1 | Clé d'API invalide | Même appel avec une mauvaise `api-key` | `403`, pas de JWT |
| API-03 | P1 | Identifiants invalides | Mot de passe erroné | `401`/`403` selon le contrat, message générique |
| API-04 | P1 | Appel sans jeton | `GET /api/mails` sans `Authorization` | `401`/`403`, aucune donnée |
| API-05 | P1 | Jeton altéré | Modifier un caractère de la signature JWT | Refus |
| API-06 | P1 | Jeton expiré | Utiliser un JWT dont `expireAt` est dépassé | Refus |
| API-07 | P1 | Création | `POST /api/mails` avec `reference`, `email`, `object`, `message` | `201` + `{ "id": n }`, ligne `statut=0` en base |
| API-08 | P1 | Lecture | `GET /api/mails` puis `GET /api/mails/<id>` | `200` + JSON conforme |
| API-09 | P1 | Mise à jour | `PUT /api/mails/<id>` | `202`, données modifiées |
| API-10 | P1 | Suppression | `DELETE /api/mails/<id>` | `200`, ligne supprimée |
| API-11 | P1 | Validation | `POST` sans `email` ou avec un e-mail invalide | `400`, aucune création |
| API-12 | P2 | Identifiant inexistant | `GET /api/mails/999999` | `400`/`404` selon le contrat, pas de trace PHP |
| API-13 | P2 | Verbe non supporté | `PATCH /api/mails/<id>` | Refus propre |
| API-14 | P2 | Pré-vol CORS | `OPTIONS /api/mails` | En-têtes attendus, `200`/`204` |
| API-15 | P1 | Injection | `id = 1 OR 1=1` et charge SQL dans les champs | Aucune fuite, aucune erreur SQL renvoyée au client |
| API-16 | P2 | Déconnexion | `POST /api/logout` puis réutiliser le JWT | Comportement documenté (JWT stateless : préciser si le jeton reste valide jusqu'à expiration) |

#### 15.6.13 BOF — Back-office générique (CRUD)

Ces cas s'appliquent à **chaque** contrôleur reposant sur `CrudController` (`Familys_controller`, `Admwork_controller`, `Options_controller`, `Templates_controller`, `Files_controller`, `Event_controller`, `Sendmail_controller`, `Parameters`, `Acl_*`).

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| BOF-01 | P1 | Liste | Ouvrir `list` sur chaque contrôleur | Colonnes conformes au schéma JSON (`list: true`), tri et pagination opérationnels |
| BOF-02 | P1 | Recherche | Rechercher sur un champ `search: true` | Résultats filtrés ; caractère `%`, apostrophe et accents sans erreur |
| BOF-03 | P2 | Réinitialisation des filtres | `clear_filters` | Retour à la liste complète |
| BOF-04 | P1 | Création | `add` avec champs obligatoires vides puis complets | Messages de validation français, puis création effective |
| BOF-05 | P1 | Édition | `edit/<id>` | Formulaire prérempli, y compris `select_database`, `typeahead`, `memo`, `checkbox`, tables liées |
| BOF-06 | P1 | Suppression | `delete/<id>` | Confirmation demandée, suppression effective |
| BOF-07 | P1 | Actions en lot | `bulk` avec `delete` sur plusieurs éléments | Seuls les éléments sélectionnés sont supprimés ; action inconnue refusée |
| BOF-08 | P1 | Droit sur l'action en lot | Rôle sans droit `delete` | L'action de suppression en lot n'est ni affichée ni exécutable |
| BOF-09 | P2 | Export CSV | `export_csv` | Colonnes cohérentes avec la liste, encodage et séparateur corrects |
| BOF-10 | P2 | Champ fichier / image | Téléverser une image dans `Files_controller` puis dans une fiche membre | Fichier stocké sous `public/files/`, restitué en liste et en vue |
| BOF-11 | P2 | Éditeur riche | Champ `memo`/`html` via CKEditor | Contenu sauvegardé et restitué ; balises `<script>` neutralisées à l'affichage |
| BOF-12 | P2 | Types d'éléments | Champs `date`, `time`, `month`, `select`, `checkboxdb`, `typeahead` | Saisie, sauvegarde et relecture correctes ; format de date `H` en 24 h |
| BOF-13 | P2 | Options et paramètres | `Options_controller/list`, `Parameters/list` | Modification d'une couleur / d'un libellé de type répercutée dans les vues métier |
| BOF-14 | P3 | Identifiant inexistant | `edit/999999`, `view/999999` | Message propre, pas de trace PHP |
| BOF-15 | P2 | Vue publique de fichiers | `Publics/files` sans session | Seuls les fichiers marqués publics sont accessibles |

#### 15.6.14 TRA — Traductions

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| TRA-01 | P2 | Liste des fichiers | `Translations_controller/list` | Fichiers de langue de l'idiome courant listés |
| TRA-02 | P2 | Édition et sauvegarde | Modifier une clé puis `save` | Fichier `app/Language/fr/*.php` réécrit, valeur visible dans l'interface après rechargement |
| TRA-03 | P1 | Sauvegarde préalable | Vérifier après TRA-02 | Copie de sauvegarde créée avant réécriture |
| TRA-04 | P2 | Caractères spéciaux | Saisir une valeur avec `'`, `"`, `\`, un accent et un retour à la ligne | Échappement correct ; le fichier PHP reste valide (contrôler `php -l`) |
| TRA-05 | P2 | Suppression de clé | `delete_key` | Clé supprimée, aucune autre ligne altérée |
| TRA-06 | P2 | Ajout de langue | `add_language` puis `switch_lang` | Nouveau répertoire créé à partir de la référence, bascule effective |
| TRA-07 | P1 | Traversée de répertoire | Appeler `edit/../../config/config.php` et `edit/fr/../../../index.php` | Refus (`_is_safe_idiom` / `_is_safe_file`), **aucun** fichier hors `app/Language/` lu ou écrit |
| TRA-08 | P1 | Droits | Accéder au module en session `fam` | Accès refusé |
| TRA-09 | P3 | Clé manquante | Afficher une page dont une clé a été supprimée | Dégradation propre (clé brute affichée), pas d'erreur fatale |

#### 15.6.15 SEC — Sécurité

| ID | P | Objectif | Étapes | Résultat attendu |
|---|---|---|---|---|
| SEC-01 | P1 | Injection SQL | `'`, `" OR 1=1--`, `%` dans les champs de recherche, les filtres et les identifiants d'URL | Aucun message d'erreur SQL, aucun résultat anormal |
| SEC-02 | P1 | XSS stocké | Enregistrer `<script>alert(1)</script>` dans un titre de session, un commentaire d'unité, un nom de membre | Affiché en texte, jamais exécuté (vérifier la liste, la vue, le PDF **et** l'e-mail) |
| SEC-03 | P1 | XSS réfléchi | Injecter la charge dans un paramètre de recherche | Échappé à l'affichage |
| SEC-04 | P1 | CSRF | Rejouer un POST de suppression depuis une page externe | Requête rejetée si la protection CSRF de CI est activée — sinon **ouvrir une anomalie P1** |
| SEC-05 | P1 | IDOR | Manipuler `id_famille`, `id_travaux`, `id_info` dans les URL et les POST en session `fam` | Aucune action sur les données d'une autre famille |
| SEC-06 | P1 | Élévation de privilège | En session `fam`, poster vers `Acl_roles_controller/set_rules` et `Familys_controller/edit` d'une autre famille | Refus |
| SEC-07 | P1 | Fixation de session | Comparer l'identifiant de session avant/après connexion | Identifiant régénéré à la connexion |
| SEC-08 | P1 | Cookies | Inspecter le cookie de session en HTTPS | `HttpOnly` et `Secure` positionnés ; `SameSite` défini |
| SEC-09 | P1 | Accès direct aux fichiers | `/app/Config/Travaux.php`, `/.env`, `/writable/logs/`, `/php_errors.log` | `403`/`404` — **aucun** contenu servi |
| SEC-10 | P1 | Fuite d'erreurs | Provoquer une erreur SQL en production | Page d'erreur générique, aucune trace ni requête affichée (`debug_app = none`) |
| SEC-11 | P1 | Upload | Téléverser `.php`, `.phtml`, `.svg` avec script, et un fichier de très grande taille | Rejet ou stockage non exécutable ; vérifier qu'aucun script n'est atteignable via son URL |
| SEC-12 | P1 | Force brute | 20 tentatives de connexion échouées | Limitation ou temporisation en place — sinon ouvrir une anomalie et documenter le risque |
| SEC-13 | P1 | Robustesse du token référent | Tester des tokens tronqués, en majuscules, avec des caractères ajoutés | Refus systématique ; aucune information sur l'existence du token |
| SEC-14 | P2 | En-têtes HTTP | Inspecter les réponses | `X-Content-Type-Options`, `X-Frame-Options`/CSP, HSTS en production |
| SEC-15 | P2 | HTTPS | Appeler le site en `http://` | Redirection vers HTTPS |
| SEC-16 | P1 | Secrets versionnés | `git grep` sur les mots de passe, clés d'API et identifiants de base | Aucun secret dans le dépôt ; `.env`, `secured.php` et `production/*` bien ignorés |
| SEC-17 | P2 | Mots de passe en base | Inspecter `acl_users.password` et `famille.password` après une campagne de connexions | 100 % en `$2y$` (bcrypt) |

#### 15.6.16 IHM — Ergonomie, responsive, compatibilité

| ID | P | Objectif | Résultat attendu |
|---|---|---|---|
| IHM-01 | P1 | Parcours famille sur mobile 360 px | Inscription à une session réalisable de bout en bout sans scroll horizontal |
| IHM-02 | P1 | Agenda cantine sur mobile | Grille lisible, boutons d'inscription atteignables au pouce |
| IHM-03 | P1 | Écran `Units_controller/valid` sur portable 1366 px | Cartes lisibles, barre sticky visible, pas de chevauchement |
| IHM-04 | P2 | Menu latéral | Ouverture/fermeture correcte sur mobile et bureau, entrées filtrées par type d'utilisateur (`opt`) |
| IHM-05 | P2 | Compatibilité navigateurs | Aucun défaut bloquant sur les navigateurs du [§ 15.3.2](#1532-navigateurs-cibles) |
| IHM-06 | P2 | Console JavaScript | Aucune erreur JS sur les écrans principaux |
| IHM-07 | P2 | Impression / PDF | La feuille de session imprimée est lisible et complète (`assets/css/pdf.css`) |
| IHM-08 | P2 | Langue | Aucune clé de traduction brute (`SOME_KEY`) visible dans l'interface |
| IHM-09 | P3 | Accessibilité de base | Contrastes suffisants, libellés de formulaire associés, navigation au clavier possible sur les formulaires d'inscription |
| IHM-10 | P3 | Images manquantes | Photo de membre absente → image de remplacement (`broken_image.png`), pas d'icône cassée |

#### 15.6.17 PER — Performance et robustesse

| ID | P | Objectif | Résultat attendu |
|---|---|---|---|
| PER-01 | P2 | `Admwork_controller/register` avec 200 sessions | Chargement < 2 s |
| PER-02 | P2 | `Units_controller/valid` avec 100 sessions × 10 inscrits | Chargement < 3 s, filtrage client fluide |
| PER-03 | P2 | Liste des familles (> 300 lignes) | Pagination efficace, < 3 s |
| PER-04 | P2 | 30 inscriptions simultanées | Aucune erreur 500, capacité maximale jamais dépassée |
| PER-05 | P3 | Import CSV de 500 lignes | Traitement sans timeout, ou pagination/lot documenté |
| PER-06 | P2 | Journaux | Après une campagne complète, `writable/logs/` ne contient aucune erreur `ERROR` inattendue |

---

### 15.7 Checklist de non-régression avant mise en production

À dérouler avant chaque merge `develop` → `main`, puis à revalider en production après déploiement.

**Préparation**

- [ ] `.env` à jour sur le serveur (`database.default.*`, `travaux.*`, `email.*`)
- [ ] Migration SQL appliquée (voir [§ 13](#13-migration-de-la-base-de-données))
- [ ] Droits d'écriture vérifiés sur `writable/`, `public/files/`
- [ ] `civil_year` correcte pour la campagne en cours
- [ ] `maintenance = false`, `debug_app = 'none'`
- [ ] Sauvegarde de la base de production effectuée

**Fonctionnel**

- [ ] Smoke test complet ([§ 15.5](#155-campagne-de-smoke-test-30-min)) — 12/12
- [ ] Connexion admin **et** connexion famille
- [ ] Inscription puis désinscription à une session réelle de test, ensuite nettoyée
- [ ] `Units_controller/valid` affiche les sessions en attente
- [ ] Un mail de test traverse la file (`sendmail` → cron → boîte de réception)
- [ ] `php spark cron:sendmail`, `cron:ref-validation` et `cron:session-alerts` s'exécutent sans erreur en CLI
- [ ] Crontabs actifs sur le serveur (`*/10 * * * *` et `0 6 * * *`)
- [ ] Aucune erreur dans `writable/logs/` après 30 min d'exploitation

**Sécurité**

- [ ] `/.env` et `/app/` inaccessibles via le navigateur (seul `public/` est exposé)
- [ ] Aucun secret ajouté au dépôt sur ce cycle (`git diff` sur les fichiers de configuration)

---

### 15.8 Gestion des anomalies

#### 15.8.1 Sévérités

| Sévérité | Définition | Délai attendu |
|---|---|---|
| **S1 — Bloquant** | Le site est inaccessible, les données sont corrompues, une faille de droits expose des données personnelles, les unités sont fausses | Correction immédiate (hotfix depuis `main`) |
| **S2 — Majeur** | Une fonction principale est inutilisable sans contournement (inscription, validation, envoi de mails) | Correction avant la mise en production |
| **S3 — Mineur** | Fonction dégradée avec contournement, défaut d'affichage significatif | Planifiée sur la prochaine itération |
| **S4 — Cosmétique** | Libellé, alignement, confort | Backlog |

#### 15.8.2 Modèle de fiche d'anomalie

```
ID          : ANO-<nnn>
Cas de test : <ID du cas, ex. TRV-07>
Sévérité    : S1 | S2 | S3 | S4
Environnement : recette | production — navigateur, version, résolution
Compte utilisé : U-ADM | U-FAM1 | ...
URL         :
Préconditions :
Étapes de reproduction :
  1.
  2.
Résultat obtenu :
Résultat attendu :
Fréquence   : systématique | intermittente (n/10)
Pièces jointes : capture, extrait de writable/logs/, requête SQL de contrôle
```

#### 15.8.3 Circuit

1. Reproduire deux fois avant d'ouvrir la fiche.
2. Créer une **issue GitHub** sur `tmilefr/site_travaux` avec le libellé de sévérité.
3. Correction sur `feature-*` (depuis `develop`) ou `hotfix-*` (depuis `main`).
4. Re-test du cas d'origine **plus** les cas connexes du même module.
5. Clôture après validation en recette, puis contrôle après déploiement en production.

---

### 15.9 Critères d'entrée et de sortie

#### 15.9.1 Entrée en recette

- [ ] La branche est déployée sur `regio.dev-asso.fr` et l'application démarre.
- [ ] Les migrations SQL du lot sont passées.
- [ ] Le jeu de données du [§ 15.4](#154-jeux-de-données-et-comptes-de-test) est en place.
- [ ] Le smoke test passe à 12/12.

#### 15.9.2 Sortie de recette (autorisation de mise en production)

- [ ] 100 % des cas **P1** exécutés et conformes.
- [ ] ≥ 90 % des cas **P2** exécutés, aucune anomalie **S1** ou **S2** ouverte.
- [ ] Les anomalies **S3/S4** restantes sont tracées et arbitrées avec le métier.
- [ ] La checklist du [§ 15.7](#157-checklist-de-non-régression-avant-mise-en-production) est complète.
- [ ] Le CHANGELOG et la documentation sont à jour pour les évolutions livrées.

---

### 15.10 Automatisation

L'outillage décrit au [§ 14](#14-vérifications-automatisées) existe : `tools/check_classes.php`, PHPUnit (36 tests : structure, helpers, `PasswordAuthenticator`, JWT, `Acl`, jeu de test),
`tools/smoke.sh` (GET de toutes les routes, erreurs 5xx), `tools/e2e.sh` (30 contrôles de bout en bout, **base de recette uniquement**) et l'intégration continue
GitHub Actions (`.github/workflows/ci.yml` : `php -l`, PHPUnit sur PHP 8.2 et 8.3 avec MariaDB).

**Cas déjà couverts par `tools/e2e.sh`** : IMP-01, IMP-02, IMP-03, IMP-04 (partiellement), IMP-07 (CP1252), IMP-08, BOF-10, SEC-11, CAN-01, CAN-02.
`e2e.sh` génère ses propres CSV (`tools/e2e/make_fixtures.php`), distincts de `csv/import_ok.csv` / `import_ko.csv` du jeu de test, mais suppose la présence des familles `ZZTEST001` et `ZZTEST002`.

**Cas couverts, au moins en partie, par PHPUnit** : AUT-02 et AUT-03 (rejet des identifiants), AUT-05 (migration `crypt()`/MD5 vers bcrypt), SEC-17 (rehash), ACL-02, ACL-03 et ACL-09 (`AclTest`), API-05 (jeton signé d'une autre clé ou malformé).
**Non couverts** : l'expiration d'un JWT (API-06) et les règles d'inscription (capacité, doublon, date passée).

Pistes restantes, par rapport effort/valeur décroissant :

1. **Tests unitaires métier (PHPUnit)** sur les briques encore sans test : `Inscriptions` (codes de retour `SESSION_FULL`, `PAST_DATE`, `ALREADY_VALIDATED`…, calcul de capacité),
   parsing et différentiel de l'import CSV (à extraire de `Familys_controller` dans une classe dédiée), expiration des JWT.
2. **Smoke test navigateur (Playwright)** : les 12 cas du [§ 15.5](#155-campagne-de-smoke-test-30-min) contre la recette, après chaque déploiement.
3. **Compléter la CI** : `php tools/check_classes.php` et contrôle qu'aucun secret (`.env`) n'entre dans le dépôt.

### 15.11 Annexes

#### 15.11.1 Fiche de suivi d'exécution

| Cas | Priorité | Testeur | Date | Statut | Anomalie | Commentaire |
|---|---|---|---|---|---|---|
| AUT-01 | P1 | | | ☐ OK ☐ KO ☐ N/A | | |
| … | | | | | | |

Statuts : **OK** (conforme) · **KO** (anomalie ouverte) · **N/A** (non applicable, à justifier) · **Bloqué** (préconditions indisponibles).

#### 15.11.2 Synthèse de couverture

| Module | Cas | dont P1 |
|---|---|---|
| AUT — Authentification | 14 | 6 |
| ACL — Droits et rôles | 11 | 7 |
| TRV — Travaux | 19 | 9 |
| REF — Validation référent | 11 | 8 |
| CAN — Cantine | 11 | 8 |
| UNI — Unités | 12 | 6 |
| FAM — Familles | 12 | 4 |
| IMP — Import CSV | 9 | 5 |
| ORG — Organigramme | 9 | 1 |
| MAI — E-mails | 11 | 4 |
| CRO — Cron | 11 | 6 |
| API — API REST | 16 | 12 |
| BOF — Back-office | 15 | 7 |
| TRA — Traductions | 9 | 3 |
| SEC — Sécurité | 17 | 14 |
| IHM — Ergonomie | 10 | 3 |
| PER — Performance | 6 | 0 |
| **Total** | **203** | **103** |

#### 15.11.3 Zones à risque identifiées à la lecture du code

Ces points méritent une attention particulière en recette ; ils sont issus des pièges documentés et de la structure du code.

| Zone | Risque | Cas associés |
|---|---|---|
| `Admwork_model::GetFiltered` | L'année `2025-2026` est codée en dur — les campagnes suivantes risquent de ne rien afficher | TRV-04 |
| Contrôle de capacité (`ADD_registration`) | Lecture puis écriture non atomiques : dépassement possible en cas d'inscriptions simultanées | TRV-11, PER-04 |
| `trombi.ref` / `groupes_member.id_fam` en VARCHAR | Une valeur non numérique casse silencieusement la chaîne référent → aucun mail envoyé | ORG-08, CRO-08 |
| Tokens de validation | Rejeu et expiration : risque de double comptage d'unités | REF-06, REF-07, REF-11 |
| `Translations_controller` | Écriture de fichiers PHP depuis l'interface : traversée de répertoire et fichier corrompu | TRA-04, TRA-07 |
| Verrou cron (`writable/cron.lock`, flock) | Deux cron simultanés doivent s'exclure ; le verrou ne doit jamais rester orphelin | CRO-04, CRO-05 |
| Import CSV | Écrasement de données locales (unités, mots de passe) | IMP-09 |
| Format de date `H` vs `h` | Bug historique sur `updated` — à revérifier après toute modification des éléments de date | BOF-12 |

#### 15.11.4 Références

- [§ 2 à § 9](#2-architecture-du-projet) de ce document — architecture, modèle de données, modules, API, tâches planifiées
- [§ 13](#13-migration-de-la-base-de-données) — migration SQL à jouer avant la recette
- `CHANGELOG.md` — refonte de `Units_controller/valid` (cas UNI-01 à UNI-08)
- `database/sql/jeu_de_test/` — jeu de données de recette (comptes, sessions, inscriptions, cas limites)

---

## 16. Annexes

### 16.1 Convention de nommage

- Contrôleurs : `Xxx_controller.php` (PascalCase + suffixe)
- Modèles : `Xxx_model.php` (PascalCase + suffixe)
- Vues d'édition : `app/Views/edition/Xxx_form.php`
- Vues spécifiques : `app/Views/unique/Xxx_controller_<action>.php`
- Schémas JSON : `app/Models/json/Xxx.json` (PascalCase, sans `_model`)
- Langue : `app/Language/<langue>/<Controleur>.php` (tableaux `return [...]`, clé = nom du champ ou `GESTION_<Controleur>`…)
- Cron : commande spark dans `app/Commands`, logique dans `CronJobs`, verrou `writable/cron.lock`

### 16.2 Bonnes pratiques observées

- **Secure by default** : `Acl::$DontCheck = FALSE` par défaut. Tout contrôleur public doit explicitement opt-in.
- **Cache permissions** : par `role_id` en session, invalidé à la déconnexion.
- **Migration password transparente** : pas de batch SQL à lancer, la migration se fait à la connexion.
- **Timing constant** : `PasswordAuthenticator::verify()` exécute `password_hash` même si le login n'existe pas, pour empêcher l'énumération d'utilisateurs.
- **Lock cron** : `CronJobs::locked()` (flock sur `writable/cron.lock`) empêche deux exécutions parallèles.
- **Throttle archivage** : flag fichier journalier dans `writable/cache/`.

### 16.3 Pièges connus

- `groupes.acteurs` et `trombi.ref` stockent des **IDs en VARCHAR** (héritage). Les jointures fonctionnent grâce à la conversion implicite MySQL.
- `Auth` est autoloadée → son constructeur ne doit pas charger de modèles (cela casserait l'init de `CrudController`). Le chargement est différé via `_requireDeps()`.
- Le format de date utilisé pour `updated` doit être `'H'` (24h) et non `'h'` (12h sans AM/PM) — bug historique corrigé.
- Le placeholder `'2025-2026'` est codé en dur dans `Admwork_model::GetFiltered` — à mettre en config si les campagnes futures débordent.
- **Strictement typé** : les helpers `form_open()` / `form_hidden()` de CodeIgniter 4 n'acceptent que des chaînes ; utiliser `open_form()` / `open_form_multipart()` (casts) pour passer des identifiants numériques.
- **Routage sensible à la casse** : `Home/login` et `home/login` sont déclarés tous les deux ; une URL en casse mixte (`Home/Login`) donne un 404. Un contrôleur nouveau doit être ajouté à `app/Config/Routes.php`.
- **Rendu des vues** : `render_view()` écrit la vue avec `echo` (le tampon de sortie devient le corps de la réponse) ; ne pas faire `return null` après un `view()` sans `echo`, le corps serait perdu.
- **Colonnes `created` / `updated`** : posées par le modèle ; une valeur postée est ignorée (`element_created` / `element_updated` n'ont pas de champ de formulaire).
- **Hash des mots de passe de `famille`** : la colonne doit faire 255 caractères (voir le [§ 13](#13-migration-de-la-base-de-données)).
- **Fichiers déposés** : toujours via `UploadTrait` (liste blanche d'extensions) ; ne jamais réintroduire `move_uploaded_file()`.
- **Fichiers inutiles à la racine** : `readme.rst`, `contributing.md`, `license.txt`, `build/`, `builds/`, `php.ini`, `preload.php`, `phpunit.dist.xml`, `php_errors.log` viennent du squelette
  CodeIgniter ou de l'ancienne version ; à supprimer après vérification de l'hébergement.
