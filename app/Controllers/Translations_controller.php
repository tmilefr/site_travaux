<?php

namespace App\Controllers;

/**
 * Translations_controller
 * -----------------------
 * Interface d'édition des fichiers de traduction situés dans
 *   app/Language/<langue>/<Fichier>.php   (tableaux PHP « return [...] » au format CodeIgniter 4)
 *
 * Fonctionnalités :
 *   - list                : choix langue + fichier à éditer
 *   - edit                : édition des paires clé/valeur d'un fichier
 *                           avec vue côte-à-côte langue de référence (fr)
 *                           pour repérer les clés manquantes
 *   - save                : sauvegarde POST
 *   - switch_lang         : change la langue courante (session) — accessible
 *                           sans authentification (via guestPages dans Acl.php)
 *   - add_language        : crée une nouvelle langue en clonant le français
 *   - delete_key          : supprime une clé d'un fichier
 *
 * Sécurité :
 *   - Toutes les actions (sauf switch_lang) sont protégées par l'ACL standard.
 *   - Validation stricte des noms de langue ([a-z_-]+) et de fichier
 *     ([A-Za-z0-9_]+) pour éviter tout path traversal.
 *   - Sauvegarde automatique du fichier original dans .bak avant écrasement.
 *
 * @package    WebApp
 * @subpackage Translations
 * @author     ABCM Mulhouse
 */
class Translations_controller extends CrudController {

    /**
     * Langue de référence pour la comparaison (clés sources).
     * @var string
     */
    protected $_ref_idiom = 'fr';

    /**
     * @return void
     */
    protected function boot(): void
	{

        $this->_controller_name = 'Translations_controller';
        // Pas de _model_name : on ne manipule pas une table SQL,
        // mais des fichiers PHP directement.


		$this->_model_name 		= 'Templates_model';	   //DataModel
		$this->title 			.=  tr('GESTION').tr($this->_controller_name);

		$this->_bg_color = 'nicdark_bg_violet';

		$this->init();
		//pour dire, on affiche pas les boutons ajout et list dans les listes
		$this->render_object->_set('_not_link_list', ['add','list']);


        $this->_autorize = array(
            'list'         => true,
            'edit'         => true,
            'save'         => true,
            'switch_lang'  => true,
            'add_language' => true,
            'add_key'      => true,
            'delete_key'   => true,
        );

        $this->title    = tr('GESTION_'.$this->_controller_name);
        $this->_bg_color = 'nicdark_bg_violet';

        $this->init();

        $this->render_object->_set('_not_link_list', array('add','list'));
    }

    // =================================================================
    // ACTION : list — page d'accueil de l'interface
    // =================================================================

    /**
     * Affiche le sélecteur (langue, fichier) avec un tableau récapitulatif
     * des fichiers présents dans chaque langue.
     */
    public function list(){
        $idiom    = $this->request->getGet('idiom');
        $available= $this->_get_available_languages();

        if (!$idiom || !in_array($idiom, $available, true)) {
            // Langue actuelle (session > config)
            $idiom = $this->session->get('user_language');
            if (!$idiom || !in_array($idiom, $available, true)) {
                $idiom = $this->request->getLocale();
            }
        }

        $files_ref    = $this->_list_lang_files($this->_ref_idiom);
        $files_target = $this->_list_lang_files($idiom);

        // Statistiques de couverture par fichier (clés présentes vs ref)
        $coverage = array();
        foreach ($files_ref as $f) {
            $ref_keys    = array_keys($this->_parse_lang_file($this->_ref_idiom, $f));
            $target_keys = in_array($f, $files_target, true)
                ? array_keys($this->_parse_lang_file($idiom, $f))
                : array();

            $missing = array_diff($ref_keys, $target_keys);
            $extra   = array_diff($target_keys, $ref_keys);

            $coverage[$f] = array(
                'total_ref' => count($ref_keys),
                'present'   => count(array_intersect($ref_keys, $target_keys)),
                'missing'   => count($missing),
                'extra'     => count($extra),
                'exists'    => in_array($f, $files_target, true),
            );
        }

        $this->data_view['available_languages'] = $available;
        $this->data_view['current_idiom']       = $idiom;
        $this->data_view['ref_idiom']           = $this->_ref_idiom;
        $this->data_view['files']               = $files_ref;
        $this->data_view['coverage']            = $coverage;

        $this->_set('view_inprogress', 'unique/Translations_controller/Translations_list');
        $this->render_view();
    }

    // =================================================================
    // ACTION : edit — formulaire d'édition d'un fichier
    // =================================================================

    /**
     * Affiche le formulaire d'édition pour un fichier de langue donné.
     *
     * URL : /Translations_controller/edit/<idiom>/<file>
     * Ex. : /Translations_controller/edit/fr/Menu
     *
     * @param string $idiom Nom de la langue (sans slash)
     * @param string $file  Nom du fichier sans .php (ex: menu_lang)
     */
    public function edit($idiom = null, $file = null){
        if (!$idiom || !$file) {
            $this->goTo($this->_controller_name.'/list');
        }
        if (!$this->_is_safe_idiom($idiom) || !$this->_is_safe_file($file)) {
            $this->abort(400, 'Paramètres invalides.');
        }

        $available = $this->_get_available_languages();
        if (!in_array($idiom, $available, true)) {
            $this->abort(404, 'Langue inconnue : '.htmlspecialchars($idiom));
        }

        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        $ref_path = APPPATH.'Language/'.$this->_ref_idiom.'/'.$file.'.php';

        if (!file_exists($ref_path)) {
            $this->abort(404, 'Fichier de référence inexistant : '.$file);
        }

        // Si le fichier cible n'existe pas encore, on le proposera vide
        // (toutes les clés ref seront marquées "manquantes").
        $target_entries = file_exists($path) ? $this->_parse_lang_file($idiom, $file) : array();
        $ref_entries    = $this->_parse_lang_file($this->_ref_idiom, $file);

        $this->data_view['idiom']          = $idiom;
        $this->data_view['file']           = $file;
        $this->data_view['target_entries'] = $target_entries;
        $this->data_view['ref_entries']    = $ref_entries;
        $this->data_view['ref_idiom']      = $this->_ref_idiom;
        $this->data_view['file_exists']    = file_exists($path);
        $this->data_view['available_languages'] = $available;

        $this->_set('view_inprogress', 'unique/Translations_controller/Translations_edit');
        $this->render_view();
    }

    // =================================================================
    // ACTION : save — POST handler
    // =================================================================

    /**
     * Reçoit le POST du formulaire d'édition et sauvegarde le fichier.
     *
     * Champs attendus :
     *   - idiom    : string
     *   - file     : string (sans .php)
     *   - keys[]   : array of original keys (pour ordre / suppression)
     *   - values[] : array of new values, indexé par clé
     *   - new_key  : string (optionnel - nouvelle clé à ajouter)
     *   - new_val  : string
     */
    public function save(){
        $idiom = $this->request->getPost('idiom');
        $file  = $this->request->getPost('file');

        if (!$this->_is_safe_idiom($idiom) || !$this->_is_safe_file($file)) {
            $this->abort(400, 'Paramètres invalides.');
        }

        $available = $this->_get_available_languages();
        if (!in_array($idiom, $available, true)) {
            $this->abort(400, 'Langue inconnue.');
        }

        $values = $this->request->getPost('values');
        if (!is_array($values)) { $values = array(); }

        // Permet d'ajouter une nouvelle clé en bas du formulaire
        $new_key = trim((string) $this->request->getPost('new_key'));
        $new_val = (string) $this->request->getPost('new_val');
        if ($new_key !== '') {
            // On accepte tout caractère imprimable mais pas d'espaces ni quotes
            if (!preg_match('/^[A-Za-z0-9_\-\[\]]+$/', $new_key)) {
                $this->session->setFlashdata('flash_error',
                    tr('TRANSLATIONS_INVALID_KEY'));
            } else {
                $values[$new_key] = $new_val;
            }
        }

        $path     = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        $ref_path = APPPATH.'Language/'.$this->_ref_idiom.'/'.$file.'.php';
        if (!is_file($ref_path)) {
            $this->abort(404, 'Fichier de référence introuvable.');
        }

        // Les valeurs saisies remplacent celles du fichier cible ; les clés de la
        // référence manquantes sont créées vides (à traduire).
        $target = file_exists($path) ? $this->_parse_lang_file($idiom, $file) : array();
        $ref    = $this->_parse_lang_file($this->_ref_idiom, $file);
        $new    = array();
        foreach (array_keys($ref) as $k) {
            $new[$k] = array_key_exists($k, $values) ? $values[$k] : ($target[$k] ?? '');
        }
        foreach ($target as $k => $v) {           // clés propres à la langue cible
            if (!array_key_exists($k, $new)) {
                $new[$k] = array_key_exists($k, $values) ? $values[$k] : $v;
            }
        }
        foreach ($values as $k => $v) {           // nouvelles clés ajoutées au formulaire
            if (!array_key_exists($k, $new)) {
                $new[$k] = $v;
            }
        }

        if (file_exists($path)) {
            $this->_backup_file($path);
        }
        if (!$this->_write_lang_file($path, $new)) {
            $this->session->setFlashdata('flash_error',
                tr('TRANSLATIONS_WRITE_ERROR').' '.$path);
            $this->goTo($this->_controller_name.'/edit/'.$idiom.'/'.$file);
        }

        $this->session->setFlashdata('flash_success',
            tr('TRANSLATIONS_SAVED_OK'));

        $this->goTo($this->_controller_name.'/edit/'.$idiom.'/'.$file);
    }

    // =================================================================
    // ACTION : delete_key
    // =================================================================

    /**
     * Supprime une clé d'un fichier de langue (préserve les commentaires
     * environnants en supprimant uniquement la ligne concernée).
     *
     * URL : /Translations_controller/delete_key/<idiom>/<file>/<key>
     */
    public function delete_key($idiom = null, $file = null, $key = null){
        if (!$idiom || !$file || !$key) {
            $this->abort(400, 'Paramètres manquants.');
        }
        if (!$this->_is_safe_idiom($idiom) || !$this->_is_safe_file($file)) {
            $this->abort(400, 'Paramètres invalides.');
        }
        // Décode (la clé peut contenir des caractères encodés URL)
        $key = urldecode($key);

        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        if (!file_exists($path)) {
            $this->abort(404, 'Fichier introuvable.');
        }

        $entries = $this->_parse_lang_file($idiom, $file);
        if (array_key_exists($key, $entries)) {
            $this->_backup_file($path);
            unset($entries[$key]);
            $this->_write_lang_file($path, $entries);
            $this->session->setFlashdata('flash_success',
                sprintf(tr('TRANSLATIONS_KEY_DELETED'), $key));
        } else {
            $this->session->setFlashdata('flash_error',
                sprintf(tr('TRANSLATIONS_KEY_NOT_FOUND'), $key));
        }

        $this->goTo($this->_controller_name.'/edit/'.$idiom.'/'.$file);
    }

    // =================================================================
    // ACTION : switch_lang — accessible sans authentification
    // =================================================================

    /**
     * Définit la langue courante en session et redirige.
     * À ajouter dans Acl::$guestPages pour un accès sans authentification.
     *
     * URL : /Translations_controller/switch_lang/<idiom>
     */
    public function switch_lang($idiom = null){
        if (!$idiom || !$this->_is_safe_idiom($idiom)) {
            $this->goTo($this->request->getServer('HTTP_REFERER') ?: base_url());
        }
        $available = $this->_get_available_languages();
        if (in_array($idiom, $available, true)) {
            $this->session->set('user_language', $idiom);
        }
        // Retour à la page précédente
        $back = $this->request->getServer('HTTP_REFERER');
        if (!$back) { $back = base_url(); }
        $this->goTo($back);
    }

    // =================================================================
    // ACTION : add_language
    // =================================================================

    /**
     * Crée une nouvelle langue en clonant l'arborescence du français.
     *
     * POST: idiom (string)
     */
    public function add_language(){
        $idiom = strtolower(trim((string) $this->request->getPost('idiom')));

        if (!$this->_is_safe_idiom($idiom)) {
            $this->session->setFlashdata('flash_error',
                tr('TRANSLATIONS_INVALID_LANGUAGE_NAME'));
            $this->goTo($this->_controller_name.'/list');
        }

        $src = APPPATH.'Language/'.$this->_ref_idiom;
        $dst = APPPATH.'Language/'.$idiom;

        if (is_dir($dst)) {
            $this->session->setFlashdata('flash_error',
                sprintf(tr('TRANSLATIONS_LANGUAGE_EXISTS'), $idiom));
            $this->goTo($this->_controller_name.'/list');
        }

        if (!@mkdir($dst, 0755, true)) {
            $this->session->setFlashdata('flash_error',
                tr('TRANSLATIONS_MKDIR_ERROR'));
            $this->goTo($this->_controller_name.'/list');
        }

        // Copie tous les *.php du dossier source
        $files = glob($src.'/*.php');
        foreach ($files as $f) {
            $base = basename($f);
            @copy($f, $dst.'/'.$base);
        }

        // index.html (sécurité CodeIgniter)
        if (file_exists($src.'/index.html')) {
            @copy($src.'/index.html', $dst.'/index.html');
        }

        $this->session->setFlashdata('flash_success',
            sprintf(tr('TRANSLATIONS_LANGUAGE_CREATED'), $idiom));
        $this->goTo($this->_controller_name.'/list?idiom='.$idiom);
    }

    // =================================================================
    // OUTILS PRIVÉS
    // =================================================================

    /**
     * Liste les langues disponibles (sous-dossiers de app/Language/).
     * @return array
     */
    protected function _get_available_languages(){
        $base = APPPATH.'Language/';
        $out  = array();
        if (!is_dir($base)) return $out;

        foreach (scandir($base) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $p = $base.$entry;
            if (is_dir($p) && $this->_is_safe_idiom($entry)) {
                $out[] = $entry;
            }
        }
        sort($out);
        return $out;
    }

    /**
     * Liste les fichiers de langue d'une langue donnée (noms sans extension,
     * ex: "Menu", "Traduction"). Le fichier "Validation" (messages CI4) est exclu.
     * @param string $idiom
     * @return array
     */
    protected function _list_lang_files($idiom){
        $dir = APPPATH.'Language/'.$idiom;
        if (!is_dir($dir)) return array();
        $out = array();
        foreach (glob($dir.'/*.php') as $f) {
            $name = basename($f, '.php');
            if ($name !== 'Validation' && $this->_is_safe_file($name)) {
                $out[] = $name;
            }
        }
        sort($out);
        return $out;
    }

    /**
     * Charge un fichier de langue et retourne le tableau [clé => valeur].
     * @param string $idiom
     * @param string $file  sans extension .php
     * @return array
     */
    protected function _parse_lang_file($idiom, $file){
        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        if (!is_file($path)) return array();

        $entries = (static function (string $path) {
            return include $path;
        })($path);

        return is_array($entries) ? $entries : array();
    }

    /**
     * Écrit un fichier de langue (tableau PHP) de façon atomique.
     *
     * @param string $path
     * @param array  $entries [clé => valeur]
     * @return bool
     */
    protected function _write_lang_file($path, array $entries){
        $out = "<?php\n\n// Fichier de traduction (édité via Translations_controller le ".date('Y-m-d H:i').")\n\nreturn [\n";
        foreach ($entries as $k => $v) {
            $out .= '    '.var_export((string) $k, true).' => '.var_export((string) $v, true).",\n";
        }
        $out .= "];\n";

        $tmp = $path.'.tmp.'.uniqid();
        if (@file_put_contents($tmp, $out) === false) {
            return false;
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            return false;
        }
        return true;
    }

    /**
     * Sauvegarde le fichier dans .bak avant écrasement.
     * @param string $path
     */
    protected function _backup_file($path){
        if (!file_exists($path)) return;
        $bak = $path.'.bak.'.date('Ymd_His');
        @copy($path, $bak);
    }

    /**
     * Validation : un nom de langue ne doit contenir que des lettres
     * minuscules, chiffres, underscore et tiret.
     * @param string $idiom
     * @return bool
     */
    protected function _is_safe_idiom($idiom){
        return is_string($idiom)
            && $idiom !== ''
            && preg_match('/^[a-z][a-z0-9_\-]*$/', $idiom);
    }

    /**
     * Validation : un nom de fichier de langue ne contient que [A-Za-z0-9_].
     * @param string $file
     * @return bool
     */
    protected function _is_safe_file($file){
        return is_string($file)
            && $file !== ''
            && preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $file);
    }
}
