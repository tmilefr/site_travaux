# Migration CodeIgniter 3.1.13 → CodeIgniter 4.7

Le site tourne désormais sur **CodeIgniter 4.7.x** (PHP ≥ 8.2). Ce document décrit ce qui a changé,
comment installer / déployer, et les choix techniques faits pour migrer sans réécrire les ~29 000 lignes
de code métier.

## 1. Stratégie : une couche de compatibilité plutôt qu'une réécriture

Le code historique (contrôleurs, modèles, bibliothèques, vues) est très couplé à l'API CI3
(`$this->load`, `$this->input`, `$this->session`, `$this->db`, `get_instance()`...). Plutôt que de tout
réécrire (risque de régressions sur un back-office de gestion), le framework a été remplacé et une fine couche
`app/Libraries/Compat/` reproduit l'API CI3 au-dessus des services CI4 :

| Classe `Compat\…` | Remplace | S'appuie sur |
|---|---|---|
| `Loader` | `CI_Loader` (`library()`, `model()`, `view()`, `helper()`, `dbforge()`) | autoload PSR-4, `Services::renderer` |
| `Db`, `Result`, `Forge` | `CI_DB_query_builder`, résultats, `dbforge` | Query Builder CI4 (appels mis en file puis rejoués sur `table()`) |
| `Input` | `CI_Input` (`post()`, `get()`, `server()`, `is_ajax_request()`) | superglobales (le code modifie `$_POST` en cours de requête) |
| `Session` | `CI_Session` (`userdata`, `set_flashdata`, `sess_destroy`...) | `session()` CI4 (driver base de données) |
| `Lang` | `CI_Lang` + fichiers `$lang['CLE']` | `app/Language/french/*_lang.php` (inchangés) |
| `Config` | `CI_Config` | `app/Config/legacy/*.php` (`$config['x']`) |
| `Uri`, `Router`, `Output` | `CI_URI`, `CI_Router`, `CI_Output` | Router/Request CI4 |
| `Form_validation` | `CI_Form_validation` (`run($groupe)`, `set_data`, `form_error`) | validateur autonome (mêmes règles, mêmes messages FR) |
| `Pagination`, `Email` | `CI_Pagination`, `CI_Email` | rendu Bootstrap identique / service Email CI4 |
| `CompatView` | contexte `$this` des vues CI3 | `CodeIgniter\View\View` (`$this->render_object`, `$this->lang`...) |
| `Model` | `CI_Model` | accès aux composants du contrôleur courant |

`app/Common.php` ajoute les fonctions globales CI3 absentes de CI4 : `get_instance()`, `ci_redirect()`,
`ci_lang()`, `form_error()`, `validation_errors()`, `html_escape()`, `config_item()`, `show_error()`,
`form_hidden()` (non typée)... Dans le code métier, `redirect()` est devenu `ci_redirect()` et `lang()` /
`Lang()` sont devenus `ci_lang()` (les fonctions homonymes de CI4 ont une autre signature).

## 2. Ce qui a changé dans l'arborescence

| CI3 | CI4 |
|---|---|
| `system/` | `vendor/codeigniter4/framework` (Composer) |
| `index.php` | `public/index.php` (document root = `public/`) |
| `assets/` | `public/assets/` |
| `application/controllers` | `app/Controllers` (namespace `App\Controllers`) |
| `application/models` (+ `json/`) | `app/Models` (namespace `App\Models`) |
| `application/libraries` (+ `elements/`) | `app/Libraries` (+ `Elements/`) |
| `application/views`, `language`, `helpers` | `app/Views`, `app/Language`, `app/Helpers` |
| `application/config/app.php`, `secured.php` | `app/Config/legacy/app.php`, `secured.php` |
| `application/config/<env>/config.php`, `database.php` | `.env` + `app/Config/*.php` |
| `application/hooks/Loginchecker` (hook `post_controller_constructor`) | `app/Config/Events.php` (même événement) |
| `application/core/MY_Controller` | `app/Controllers/MY_Controller.php` (abstrait) |
| `application/core/MY_Lang`, `MY_Exceptions` | `Compat\Lang`, `show_error()` dans `Common.php` |
| `application/libraries/Form_validation.php` | `Compat\Form_validation` |
| `application/migrations/*.sql` | `database/sql/*.sql` |
| `application/cache`, `application/logs` | `writable/cache`, `writable/logs` |
| `php index.php cron sendmail` | `php public/index.php cron sendmail` |

URLs inchangées : le routage « automatique » CI3 (`Controleur/methode/arg`) est conservé
(`Routing::$autoRoute = true`, `Feature::$autoRoutesImproved = false`) ; seul `index.php` disparaît des URLs.

## 3. Installation / déploiement

```bash
composer install --no-dev -o          # dépendances (CI4, dompdf, firebase/php-jwt, sodium_compat)
cp env.example .env                   # puis renseigner CI_ENVIRONMENT, app.baseURL et database.default.*
cp app/Config/legacy/secured.sample.php app/Config/legacy/secured.php   # puis renseigner les secrets
chmod -R u+rwX writable public/files public/data
```

1. **Document root** du serveur web : `public/` (un `.htaccess` racine redirige vers `public/` sur mutualisé).
2. **Table des sessions** : CI4 stocke l'activité dans une colonne `TIMESTAMP` (CI3 : entier). Exécuter une fois
   `database/sql/ci4_ci_sessions.sql` (purge les sessions : les utilisateurs devront se reconnecter).
3. **Cron** : remplacer `php index.php cron …` par `php public/index.php cron …` (le dernier argument n'est plus
   l'environnement ; il vient de `CI_ENVIRONMENT` dans `.env`).
4. Vérifier que `app.baseURL` se termine par `/` et correspond à l'URL publique.

## 4. Différences de comportement à connaître

* **Avertissements PHP** : CI4 transforme chaque avertissement en exception. Le code historique en émet
  (clés/propriétés absentes) et tournait avec `error_reporting(E_ALL & ~E_NOTICE …)`. Pour conserver ce comportement,
  `Events.php` journalise `E_WARNING/E_NOTICE` (niveau `warning`, `writable/logs`) sans interrompre la requête.
  Les `TypeError`/`Error` restent fatales, comme en CI3 sous PHP 8.
* **Validation** : `trim` est reporté dans `$_POST` ; `run()` sans données renvoie `FALSE` (comme CI3).
* **Pagination / e-mail** : mêmes API ; la config SMTP CI3 (`smtp_host`, `smtp_crypto`…) est traduite vers
  `Config\Email` par `Compat\Email`.
* **ACL** : `Acl::Route()` s'exécute toujours juste après l'instanciation du contrôleur ; les redirections
  (`ci_redirect`) lèvent `RedirectException` (équivalent du `exit` de `redirect()` CI3).
* **Scan ACL** (`Acl_controllers_controller/scan`) : résout désormais `App\Controllers\<Classe>`.
* **Chemins fichiers** : PDF dans `public/data/pdf/`, imports CSV dans `public/files/imports/`, uploads dans
  `public/files/…`, flag d'archivage dans `writable/cache/`.
* **Contrôleurs abstraits** : `MY_Controller` n'est plus routable (500 au lieu d'un contrôleur vide).

## 5. Correctifs incidents découverts pendant la migration

* `Familys_controller::import_history` n'avait jamais chargé `FamilyImport_model` (erreur 500).
* `Admwork_controller::MakePdf` pointait vers une vue inexistante (`unique/<ctrl>_register_one_pdf`).

## 6. Vérifications

```bash
php tools/check_classes.php           # toutes les classes référencées dans app/ se résolvent
vendor/bin/phpunit tests/unit         # tests de la couche de compatibilité
tools/smoke.sh http://localhost:8080 <login> <mot_de_passe> routes.txt   # GET de chaque route, détecte les 500
```

Les routes ont été passées en admin et en famille sur la base de recette (`database/sql/jeu_de_test/`) :
connexion bcrypt et migration MD5 → bcrypt, listes/filtres/tri/pagination, formulaires CRUD, inscription à une
session (POST), génération PDF, scan ACL, cron `sendmail` en CLI.

## 7. Reste à faire / points d'attention

* **Régression à tester en recette** : parcours d'upload de fichiers/images (`element_file`, `element_img`),
  import CSV familles, cantine (génération), traductions (édition des fichiers de langue), envoi réel d'e-mails (SMTP).
* `Api::login` s'appuie sur `Acl::CheckLogin()` qui retourne un message/`NULL` (et non un objet) : défaut
  préexistant, non modifié.
* À terme, le code peut être progressivement réécrit en idiomes CI4 (Entities, `Model`, Filters, `Validation`) puis
  la couche `Compat/` réduite ; elle est volontairement isolée dans un seul dossier.
* Les fichiers CI3 `readme.rst`, `contributing.md`, `license.txt` et le dossier vide `application/` n'ont plus
  d'utilité et peuvent être supprimés.
* Les identifiants de l'ancien `.env` versionné (`DB_DEV_*`, `PWD`) figurent dans l'historique git : à changer.
