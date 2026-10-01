<?php

namespace App\Controllers;

/**
 * Translations_controller
 * -----------------------
 * Interface d'édition des fichiers de traduction situés dans
 *   application/language/<idiom>/*_lang.php
 *
 * Fonctionnalités :
 *   - list                : choix langue + fichier à éditer
 *   - edit                : édition des paires clé/valeur d'un fichier
 *                           avec vue côte-à-côte langue de référence (français)
 *                           pour repérer les clés manquantes
 *   - save                : sauvegarde POST (préserve commentaires + structure)
 *   - switch_lang         : change la langue courante (session) — accessible
 *                           sans authentification (via guestPages dans Acl.php)
 *   - add_language        : crée une nouvelle langue en clonant le français
 *   - add_key             : ajoute une nouvelle clé à un fichier
 *   - delete_key          : supprime une clé d'un fichier
 *
 * Sécurité :
 *   - Toutes les actions (sauf switch_lang) sont protégées par l'ACL standard
 *     (à enregistrer via Acl_controllers_controller/scan puis assignation au
 *     rôle Admin via Acl_roles_controller/set_rules).
 *   - Validation stricte des noms de langue ([a-z_]+) et de fichier
 *     ([A-Za-z0-9_]+_lang) pour éviter tout path traversal.
 *   - Sauvegarde automatique du fichier original dans .bak avant écrasement.
 *
 * @package    WebApp
 * @subpackage Translations
 * @author     ABCM Mulhouse
 */
class Translations_controller extends MY_Controller {

    /**
     * Langue de référence pour la comparaison (clés sources).
     * @var string
     */
    protected $_ref_idiom = 'french';

    /**
     * @return void
     */
    public function __construct(){
        parent::__construct();

        $this->_controller_name = 'Translations_controller';
        // Pas de _model_name : on ne manipule pas une table SQL,
        // mais des fichiers PHP directement.


		$this->_model_name 		= 'Templates_model';	   //DataModel
		$this->title 			.=  $this->lang->line('GESTION').$this->lang->line($this->_controller_name);

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

        $this->title    = $this->lang->line('GESTION_'.$this->_controller_name);
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
        $idiom    = $this->input->get('idiom');
        $available= $this->_get_available_languages();

        if (!$idiom || !in_array($idiom, $available, true)) {
            // Langue actuelle (session > config)
            $idiom = $this->session->userdata('user_language');
            if (!$idiom || !in_array($idiom, $available, true)) {
                $idiom = $this->config->item('language') ?: 'french';
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
     * Ex. : /Translations_controller/edit/french/menu_lang
     *
     * @param string $idiom Nom de la langue (sans slash)
     * @param string $file  Nom du fichier sans .php (ex: menu_lang)
     */
    public function edit($idiom = null, $file = null){
        if (!$idiom || !$file) {
            ci_redirect($this->_controller_name.'/list');
        }
        if (!$this->_is_safe_idiom($idiom) || !$this->_is_safe_file($file)) {
            show_error('Paramètres invalides.', 400);
        }

        $available = $this->_get_available_languages();
        if (!in_array($idiom, $available, true)) {
            show_error('Langue inconnue : '.htmlspecialchars($idiom), 404);
        }

        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        $ref_path = APPPATH.'Language/'.$this->_ref_idiom.'/'.$file.'.php';

        if (!file_exists($ref_path)) {
            show_error('Fichier de référence inexistant : '.$file, 404);
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
        $idiom = $this->input->post('idiom');
        $file  = $this->input->post('file');

        if (!$this->_is_safe_idiom($idiom) || !$this->_is_safe_file($file)) {
            show_error('Paramètres invalides.', 400);
        }

        $available = $this->_get_available_languages();
        if (!in_array($idiom, $available, true)) {
            show_error('Langue inconnue.', 400);
        }

        $values = $this->input->post('values');
        if (!is_array($values)) { $values = array(); }

        // Permet d'ajouter une nouvelle clé en bas du formulaire
        $new_key = trim((string) $this->input->post('new_key'));
        $new_val = (string) $this->input->post('new_val');
        if ($new_key !== '') {
            // On accepte tout caractère imprimable mais pas d'espaces ni quotes
            if (!preg_match('/^[A-Za-z0-9_\-\[\]]+$/', $new_key)) {
                $this->session->set_flashdata('flash_error',
                    $this->lang->line('TRANSLATIONS_INVALID_KEY'));
            } else {
                $values[$new_key] = $new_val;
            }
        }

        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';

        // 1) Si le fichier cible existe → on met à jour en préservant la structure.
        // 2) Sinon → on génère un nouveau fichier à partir des clés ref + valeurs saisies.
        if (file_exists($path)) {
            $this->_backup_file($path);
            $new_content = $this->_rewrite_lang_file($path, $values);
        } else {
            // Pas encore de fichier cible : crée un fichier neuf à partir
            // des clés du fichier de référence (français), en injectant
            // les valeurs saisies.
            $ref_path = APPPATH.'Language/'.$this->_ref_idiom.'/'.$file.'.php';
            if (!file_exists($ref_path)) {
                show_error('Fichier de référence introuvable.', 404);
            }
            $new_content = $this->_create_lang_file_from_ref($ref_path, $values);
        }

        // Écriture atomique
        $tmp = $path.'.tmp.'.uniqid();
        if (@file_put_contents($tmp, $new_content) === false) {
            $this->session->set_flashdata('flash_error',
                $this->lang->line('TRANSLATIONS_WRITE_ERROR').' '.$path);
            ci_redirect($this->_controller_name.'/edit/'.$idiom.'/'.$file);
        }
        if (!@rename($tmp, $path)) {
            @unlink($tmp);
            $this->session->set_flashdata('flash_error',
                $this->lang->line('TRANSLATIONS_WRITE_ERROR').' '.$path);
            ci_redirect($this->_controller_name.'/edit/'.$idiom.'/'.$file);
        }

        $this->session->set_flashdata('flash_success',
            $this->lang->line('TRANSLATIONS_SAVED_OK'));

        ci_redirect($this->_controller_name.'/edit/'.$idiom.'/'.$file);
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
            show_error('Paramètres manquants.', 400);
        }
        if (!$this->_is_safe_idiom($idiom) || !$this->_is_safe_file($file)) {
            show_error('Paramètres invalides.', 400);
        }
        // Décode (la clé peut contenir des caractères encodés URL)
        $key = urldecode($key);

        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        if (!file_exists($path)) {
            show_error('Fichier introuvable.', 404);
        }

        $this->_backup_file($path);

        $content = file_get_contents($path);
        $lines   = preg_split('/\R/', $content); // gère \r\n / \n / \r
        $out     = array();
        $found   = false;

        foreach ($lines as $line) {
            if (!$found && $this->_line_matches_key($line, $key)) {
                $found = true;
                continue; // on saute la ligne
            }
            $out[] = $line;
        }

        if ($found) {
            file_put_contents($path, implode("\n", $out));
            $this->session->set_flashdata('flash_success',
                sprintf($this->lang->line('TRANSLATIONS_KEY_DELETED'), $key));
        } else {
            $this->session->set_flashdata('flash_error',
                sprintf($this->lang->line('TRANSLATIONS_KEY_NOT_FOUND'), $key));
        }

        ci_redirect($this->_controller_name.'/edit/'.$idiom.'/'.$file);
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
            ci_redirect($_SERVER['HTTP_REFERER'] ?? base_url());
        }
        $available = $this->_get_available_languages();
        if (in_array($idiom, $available, true)) {
            $this->session->set_userdata('user_language', $idiom);
        }
        // Retour à la page précédente
        $back = $this->input->server('HTTP_REFERER');
        if (!$back) { $back = base_url(); }
        ci_redirect($back);
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
        $idiom = strtolower(trim((string) $this->input->post('idiom')));

        if (!$this->_is_safe_idiom($idiom)) {
            $this->session->set_flashdata('flash_error',
                $this->lang->line('TRANSLATIONS_INVALID_LANGUAGE_NAME'));
            ci_redirect($this->_controller_name.'/list');
        }

        $src = APPPATH.'Language/'.$this->_ref_idiom;
        $dst = APPPATH.'Language/'.$idiom;

        if (is_dir($dst)) {
            $this->session->set_flashdata('flash_error',
                sprintf($this->lang->line('TRANSLATIONS_LANGUAGE_EXISTS'), $idiom));
            ci_redirect($this->_controller_name.'/list');
        }

        if (!@mkdir($dst, 0755, true)) {
            $this->session->set_flashdata('flash_error',
                $this->lang->line('TRANSLATIONS_MKDIR_ERROR'));
            ci_redirect($this->_controller_name.'/list');
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

        $this->session->set_flashdata('flash_success',
            sprintf($this->lang->line('TRANSLATIONS_LANGUAGE_CREATED'), $idiom));
        ci_redirect($this->_controller_name.'/list?idiom='.$idiom);
    }

    // =================================================================
    // OUTILS PRIVÉS
    // =================================================================

    /**
     * Liste les langues disponibles (sous-dossiers de application/language/).
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
     * Liste les fichiers de langue *_lang.php d'une langue donnée.
     * Retourne les noms sans extension (ex: "menu_lang", "traduction_lang").
     * @param string $idiom
     * @return array
     */
    protected function _list_lang_files($idiom){
        $dir = APPPATH.'Language/'.$idiom;
        if (!is_dir($dir)) return array();
        $files = glob($dir.'/*_lang.php');
        $out = array();
        foreach ($files as $f) {
            $name = basename($f, '.php');
            if ($this->_is_safe_file($name)) {
                $out[] = $name;
            }
        }
        sort($out);
        return $out;
    }

    /**
     * Parse un fichier de langue et retourne un tableau associatif
     * [clé => valeur] pour TOUTES les lignes $lang['xxx'] = '...';
     * (lignes simples, ce qui couvre quasi 100% du projet).
     *
     * Les valeurs sont retournées DÉCAPÉES (les \' deviennent ',
     * les \\ deviennent \).
     *
     * @param string $idiom
     * @param string $file  sans extension .php
     * @return array
     */
    protected function _parse_lang_file($idiom, $file){
        $path = APPPATH.'Language/'.$idiom.'/'.$file.'.php';
        if (!file_exists($path)) return array();

        $content = file_get_contents($path);
        $lines   = preg_split('/\R/', $content);
        $out     = array();

        foreach ($lines as $line) {
            $parsed = $this->_parse_lang_line($line);
            if ($parsed !== null) {
                $out[$parsed['key']] = $parsed['value'];
            }
        }
        return $out;
    }

    /**
     * Tente de parser une ligne unique et retourne ['key', 'value', 'quote']
     * ou null si la ligne n'est pas une affectation $lang[].
     * @param string $line
     * @return array|null
     */
    protected function _parse_lang_line($line){
        // Quote simple : $lang['key'] = 'value';
        if (preg_match("/^\s*\\\$lang\[\s*'([^']+)'\s*\]\s*=\s*'((?:\\\\.|[^'\\\\])*)'\s*;.*$/", $line, $m)) {
            return array(
                'key'   => $m[1],
                'value' => stripcslashes($m[2]), // \' → '   \\ → \
                'quote' => "'",
            );
        }
        // Quote double : $lang["key"] = "value";
        if (preg_match('/^\s*\$lang\[\s*"([^"]+)"\s*\]\s*=\s*"((?:\\\\.|[^"\\\\])*)"\s*;.*$/', $line, $m)) {
            return array(
                'key'   => $m[1],
                'value' => stripcslashes($m[2]),
                'quote' => '"',
            );
        }
        return null;
    }

    /**
     * Vérifie si une ligne déclare la clé donnée.
     * @param string $line
     * @param string $key
     * @return bool
     */
    protected function _line_matches_key($line, $key){
        $p = $this->_parse_lang_line($line);
        return ($p !== null && $p['key'] === $key);
    }

    /**
     * Réécrit un fichier de langue en remplaçant uniquement les valeurs
     * des clés présentes dans $values, sans toucher aux commentaires ni
     * à la structure du fichier.
     *
     * Les nouvelles clés (présentes dans $values mais absentes du fichier)
     * sont ajoutées à la fin, juste avant la balise ?> si présente.
     *
     * @param string $path   chemin absolu du fichier
     * @param array  $values [clé => valeur (non échappée)]
     * @return string nouveau contenu
     */
    protected function _rewrite_lang_file($path, array $values){
        $content = file_get_contents($path);
        $lines   = preg_split('/\R/', $content);

        $seen = array();
        $out  = array();

        foreach ($lines as $line) {
            $parsed = $this->_parse_lang_line($line);
            if ($parsed !== null && array_key_exists($parsed['key'], $values)) {
                // Remplace la valeur tout en préservant le préfixe / suffixe
                $out[]            = $this->_rebuild_lang_line($line, $parsed, $values[$parsed['key']]);
                $seen[$parsed['key']] = true;
            } elseif ($parsed !== null && !array_key_exists($parsed['key'], $values)) {
                // La clé existait dans le fichier mais le formulaire ne la
                // renvoie pas → soit elle a été supprimée explicitement
                // (auquel cas on a déjà retiré la ligne via delete_key),
                // soit le formulaire ne la couvre pas. Pour rester safe,
                // on conserve la ligne telle quelle.
                $out[] = $line;
            } else {
                $out[] = $line;
            }
        }

        // Ajout des clés nouvelles (non vues)
        $new_keys = array_diff(array_keys($values), array_keys($seen));
        if (!empty($new_keys)) {
            // Trouve la position de la balise de fermeture PHP si présente
            $insert_at = count($out);
            for ($i = count($out) - 1; $i >= 0; $i--) {
                if (preg_match('/^\s*\?>\s*$/', $out[$i])) {
                    $insert_at = $i;
                    break;
                }
            }
            $additions = array();
            $additions[] = '';
            $additions[] = '// --- Clés ajoutées via Translations_controller ('.date('Y-m-d H:i').') ---';
            foreach ($new_keys as $k) {
                $additions[] = $this->_format_lang_line($k, $values[$k]);
            }
            // Splice des additions
            array_splice($out, $insert_at, 0, $additions);
        }

        return implode("\n", $out);
    }

    /**
     * Reconstruit une ligne $lang['key'] = '...'; en remplaçant la valeur,
     * tout en préservant l'indentation et l'éventuel commentaire de fin
     * de ligne.
     *
     * @param string $original_line
     * @param array  $parsed         résultat de _parse_lang_line()
     * @param string $new_value      valeur non échappée
     * @return string
     */
    protected function _rebuild_lang_line($original_line, array $parsed, $new_value){
        $quote = $parsed['quote'];
        $escaped = $this->_escape_for_quote($new_value, $quote);

        // Pattern selon le type de quote
        if ($quote === "'") {
            $pattern = "/^(\s*\\\$lang\[\s*'[^']+'\s*\]\s*=\s*)('(?:\\\\.|[^'\\\\])*')(\s*;.*)$/";
        } else {
            $pattern = '/^(\s*\$lang\[\s*"[^"]+"\s*\]\s*=\s*)("(?:\\\\.|[^"\\\\])*")(\s*;.*)$/';
        }

        if (preg_match($pattern, $original_line, $m)) {
            return $m[1] . $quote . $escaped . $quote . $m[3];
        }
        // Fallback : on reconstruit complètement
        return $this->_format_lang_line($parsed['key'], $new_value, $quote);
    }

    /**
     * Formate une ligne $lang[...] = '...'; depuis zéro.
     * @param string $key
     * @param string $value (non échappée)
     * @param string $quote
     * @return string
     */
    protected function _format_lang_line($key, $value, $quote = "'"){
        $key_escaped = $this->_escape_for_quote($key, $quote);
        $val_escaped = $this->_escape_for_quote($value, $quote);
        return '$lang['.$quote.$key_escaped.$quote.'] = '.$quote.$val_escaped.$quote.';';
    }

    /**
     * Échappe une chaîne pour insertion dans une chaîne PHP avec le quote
     * donné. Pour les single quotes, seuls \ et ' sont à échapper.
     * Pour les double quotes, plus d'échappements (\, ", $, \n etc.) — mais
     * comme tout le projet utilise les single quotes, on y reste fidèle.
     *
     * @param string $s
     * @param string $quote
     * @return string
     */
    protected function _escape_for_quote($s, $quote){
        if ($quote === "'") {
            return str_replace(array('\\', "'"), array('\\\\', "\\'"), $s);
        }
        return str_replace(array('\\', '"', '$'), array('\\\\', '\\"', '\\$'), $s);
    }

    /**
     * Crée un tout nouveau fichier de langue, en se basant sur les commentaires
     * et la structure du fichier de référence (français), mais en injectant
     * les valeurs depuis $values pour les clés correspondantes (et la
     * valeur du français comme fallback pour les autres).
     *
     * @param string $ref_path
     * @param array  $values
     * @return string
     */
    protected function _create_lang_file_from_ref($ref_path, array $values){
        $content = file_get_contents($ref_path);
        $lines   = preg_split('/\R/', $content);
        $out     = array();

        foreach ($lines as $line) {
            $parsed = $this->_parse_lang_line($line);
            if ($parsed !== null) {
                // Si l'utilisateur a fourni une valeur, on l'utilise.
                // Sinon, on laisse vide pour que l'utilisateur sache qu'il
                // doit traduire.
                $val = isset($values[$parsed['key']]) ? $values[$parsed['key']] : '';
                $out[] = $this->_rebuild_lang_line($line, $parsed, $val);
            } else {
                $out[] = $line;
            }
        }
        return implode("\n", $out);
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
     * Validation : un nom de fichier de langue doit terminer par "_lang"
     * et ne contenir que [A-Za-z0-9_].
     * @param string $file
     * @return bool
     */
    protected function _is_safe_file($file){
        return is_string($file)
            && $file !== ''
            && preg_match('/^[A-Za-z0-9_]+_lang$/', $file);
    }
}

/* End of file Translations_controller.php */
/* Location: ./application/controllers/Translations_controller.php */
