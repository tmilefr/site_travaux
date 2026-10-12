# Réécriture en CodeIgniter 4 (branche `ci4-natif`)

Le site tourne sur **CodeIgniter 4.7.x** (PHP ≥ 8.2) avec le code réécrit « à la CI4 » : plus de couche
de compatibilité CI3, plus de `get_instance()`, plus de `$this->load`. Cette branche remplace l'approche
« shim » de la branche `develop` (qui reproduisait l'API CI3 au-dessus de CI4).

## 1. Ce qui est natif CodeIgniter 4

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

## 2. Conventions de l'application

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

## 3. Arborescence

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

## 4. Installation / déploiement

```bash
composer install --no-dev -o
cp env.example .env     # CI_ENVIRONMENT, app.baseURL, database.default.*, travaux.*, email.*
chmod -R u+rwX writable public/files public/data
```

1. **Document root** du serveur web : `public/` (un `.htaccess` racine redirige vers `public/` en mutualisé).
2. **Base de données de production** : après une sauvegarde (`mysqldump`), exécuter **un seul script**, rejouable sans risque :
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

## 5. Changements de comportement et corrections

* **Envois de fichiers** (`element_file`, `element_img`) : seules les extensions d'une liste blanche sont acceptées (jamais de `.php`),
  le nom est assaini, un fichier existant n'est jamais écrasé, une image est vérifiée (`getimagesize`) puis redimensionnée par le
  gestionnaire d'images natif (JPEG, PNG, GIF, WebP). Avant : un `.php` envoyé par le formulaire était exécutable depuis
  `public/files/` et un PNG provoquait une erreur 500. `public/files/.htaccess` interdit en plus l'exécution de scripts.
* Génération cantine : le journal utilisait des colonnes absentes de certaines bases (`date_deb`, `date_fin`, `nb_skipped`) ; appliquer
  `database/sql/mig_cantine_generation_cols.sql` (rejouable).

* `Familys_controller::import_history` n'avait jamais chargé `FamilyImport_model` (erreur 500) — corrigé.
* `Admwork_controller::MakePdf` visait une vue inexistante — corrigé ; le PDF est renvoyé par la réponse HTTP.
* `Api::login` s'appuyait sur `Acl::CheckLogin()` (qui retourne un message) : il appelle maintenant `Auth::Login()` et renvoie le JWT.
  L'accès non authentifié à `Api/login` reste bloqué par l'ACL (non modifié).
* Le CRUD générique n'est plus joignable pour `Home`, `Cantine_controller`, `Parameters`… (contrôleurs sans `$_autorize`) :
  c'était possible mais sans objet. `MY_Controller`/`BaseController` ne sont plus routables (404).
* Les champs « créé/modifié » sont posés par le modèle (24 h) et non plus par des champs cachés de formulaire ; `updated` est aussi renseigné à la création.
* Un champ vide non requis n'est plus soumis aux autres règles (comportement CI3 conservé explicitement : `permit_empty`).

## 6. Vérifications

```bash
php tools/check_classes.php              # toutes les classes référencées dans app/ se résolvent
vendor/bin/phpunit tests/unit            # structure (routes, langues, API CI3 absente), helpers
tools/smoke.sh http://localhost:8080 <login> <mot_de_passe> routes.txt   # GET de chaque route : 500 et pages HTML vides
tools/e2e.sh http://localhost:8080 <login> <mot_de_passe>               # envois de fichiers, import CSV, génération cantine
```

Passé sur la base de recette (`database/sql/jeu_de_test/`) en admin et en famille : connexion bcrypt et migration MD5 → bcrypt,
listes (filtres, tri, pagination, export CSV), formulaires CRUD, inscription à une session (POST), PDF, scan ACL,
traductions, commandes spark (`cron:sendmail` jusqu'à l'échec SMTP du bac à sable, `cron:ref-validation`, `cron:session-alerts`, verrou). Les pages `view/*` sans vue associée et `Jsondata` sans argument échouent comme avant.

## 7. Reste à faire / points d'attention

* Recette de bout en bout rejouable : `tools/e2e.sh <url> <login> <mot_de_passe>` (base de recette uniquement) couvre les envois de
  fichiers et d'images, l'import CSV des familles et la génération cantine (30 contrôles). Reste à recetter : envoi SMTP vers des
  adresses réelles et le SSO Delta (non joignables depuis le bac à sable).
* Les actions renvoient encore souvent le HTML par `echo` dans `render_view()` (CI4 le capture comme corps de réponse) ; elles
  peuvent progressivement `return view(...)`.
* Les identifiants de l'ancien `.env` versionné (`DB_DEV_*`, `PWD`) figurent dans l'historique git : à changer.
* Fichiers CI3 sans utilité (`readme.rst`, `contributing.md`, `license.txt`, dossier vide `application/`) : à supprimer.
