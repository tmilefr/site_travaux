<?php

namespace App\Controllers;

use ReflectionClass;
use ReflectionMethod;
use StdClass;
/**
 * User Controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
class Acl_controllers_controller extends CrudController {

	/**
	 * Method __construct
	 *
	 * @return void
	 */
	protected function boot(): void
	{

		$this->_controller_name = 'Acl_controllers_controller';  //controller name for routing
		$this->_model_name 		= 'Acl_controllers_model';	   //DataModel
		$this->_edit_view 		= 'edition/Acl_controllers_form';//template for editing
		$this->_list_view		= 'unique/Acl_controllers_view.php';
		$this->_autorize 		= array('add'=>true,'edit'=>true,'list'=>true,'delete'=>true,'view'=>false,'scan'=>true);


		$this->title 			= tr('GESTION_'.$this->_controller_name);
		$this->_bg_color = 'nicdark_bg_red';
		$this->_set('_debug', FALSE);
		$this->init();

		$this->Loadmodel('Acl_actions_model');
		$this->Loadmodel('Acl_roles_controllers_model');
	}

	/**
	 * @brief Surcharge de la liste pour ajouter un bandeau de KPIs / alertes ACL
	 * @returns void
	 *
	 * Le rendu reste celui de list_view.php (vue générique). Pour insérer
	 * le bandeau au-dessus, on s'appuie sur le mécanisme déjà câblé dans
	 * CrudController::render_view() : si une vue existe dans
	 *   app/Views/unique/{ControllerName}/list_view.php
	 * elle prime sur la vue générique. Cette vue spécifique inclut la
	 * vue générique tout en ajoutant le bandeau au-dessus.
	 */
	public function list()
	{
		// On bloque le rendu automatique pour pouvoir enrichir data_view
		$this->_set('render_view', false);
		parent::list();

		$this->data_view['acl_kpis']     = $this->_compute_acl_kpis();
		$this->data_view['acl_warnings'] = $this->_compute_acl_warnings();

		$this->render_view();
	}

	/**
	 * @brief Scanne le dossier app/Controllers/ et synchronise la
	 *        base ACL avec le code réel.
	 *
	 *  GET  /Acl_controllers_controller/scan
	 *      → analyse + affichage de la prévisualisation (rien n'est écrit).
	 *  POST /Acl_controllers_controller/scan  (avec confirm=1)
	 *      → exécute les insertions validées par l'utilisateur via les
	 *        cases à cocher du formulaire de prévisualisation.
	 *
	 * Ce qui est créé :
	 *   - Les contrôleurs absents de `acl_controllers`.
	 *   - Les actions absentes de `acl_actions` (méthodes publiques nouvelles).
	 *
	 * Ce qui n'est PAS supprimé automatiquement :
	 *   - Les contrôleurs / actions présents en BDD mais absents du code
	 *     sont juste listés en "obsolètes" (suppression manuelle = on ne
	 *     casse aucune règle ACL existante par accident).
	 *
	 * @return void
	 */
	public function scan()
	{
		$this->_set('view_inprogress', 'edition/Acl_scan_view');

		// 1) Scanner le système de fichiers
		$scanned = $this->_scan_controllers_dir();

		// 2) État courant de la base
		$this->_reset_model_for_kpi($this->_model_name);
		$db_ctrls_raw = $this->{$this->_model_name}->get_all();
		$db_ctrls = array(); // controller_name => row
		foreach ($db_ctrls_raw as $row) {
			$db_ctrls[$row->controller] = $row;
		}

		$this->_reset_model_for_kpi('Acl_actions_model');
		$db_actions_raw = $this->Acl_actions_model->get_all();
		$db_actions_by_ctrl = array(); // id_ctrl => [action => row]
		foreach ($db_actions_raw as $row) {
			$db_actions_by_ctrl[$row->id_ctrl][$row->action] = $row;
		}

		// 3) Diff : ce qui doit être créé / ce qui est déjà OK / ce qui est obsolète
		$diff = $this->_diff_acl($scanned, $db_ctrls, $db_actions_by_ctrl);

		// 4) Confirmation -> insertions ciblées par l'utilisateur
		$confirm = (int) $this->request->getPost('confirm') === 1;
		if ($confirm) {
			$picks_ctrls   = (array) $this->request->getPost('add_ctrl');   // [ 'NomController', ... ]
			$picks_actions = (array) $this->request->getPost('add_action'); // [ 'NomController::action', ... ]
			$report = $this->_apply_scan($scanned, $db_ctrls, $picks_ctrls, $picks_actions);

			$msg = sprintf(
				tr('Acl_scan_done')
					?: 'Scan ACL terminé : %d contrôleur(s) ajouté(s), %d action(s) ajoutée(s).',
				$report['ctrls_added'],
				$report['actions_added']
			);
			$this->session->setFlashdata('bulk_success', $msg);
			$this->goTo($this->_controller_name . '/scan');
			return;
		}

		// 5) Affichage de la prévisualisation
		$this->data_view['title'] = tr('GESTION_' . $this->_controller_name)
			. ' : ' . (tr('Acl_scan_title') ?: 'Synchronisation depuis le code');
		$this->data_view['scan_diff'] = $diff;
		$this->data_view['scan_summary'] = array(
			'files_scanned'   => count($scanned),
			'new_ctrls'       => count($diff['new_ctrls']),
			'new_actions'     => array_sum(array_map(function($x){ return count($x['actions']); }, $diff['new_actions'])),
			'obsolete_ctrls'  => count($diff['obsolete_ctrls']),
			'obsolete_acts'   => array_sum(array_map(function($x){ return count($x['actions']); }, $diff['obsolete_actions'])),
		);

		$this->render_view();
	}

	/**
	 * Scanne app/Controllers/ et retourne un tableau
	 *   [ 'NomController' => [ 'method1', 'method2', ... ], ... ]
	 *
	 * Utilise token_get_all (pas d'instanciation = pas d'effet de bord
	 * type sessions ou autoload de modèles déclenché par les constructeurs).
	 *
	 * Sont ignorés :
	 *   - les fichiers non-PHP
	 *   - les classes sans nom (anonymes — ne devrait pas arriver)
	 *   - les classes listées dans $this->_scan_blacklist
	 *   - les méthodes magiques (commencent par __)
	 *   - les méthodes privées / protégées (préfixe _ ou non publiques)
	 *   - le constructeur __construct et index (CI redirige souvent dessus)
	 *
	 * @return array
	 */
	private function _scan_controllers_dir()
	{
		$dir = APPPATH . 'Controllers';
		$result = array();

		if (!is_dir($dir)) {
			return $result;
		}

		$blacklist        = $this->_scan_blacklist();
		$inherited_actions = $this->_inherited_actions_whitelist();
		$files = glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.php');

		foreach ($files as $file) {
			// 1) Récupérer le nom de la classe sans instancier (token_get_all sûr)
			$src = @file_get_contents($file);
			if ($src === false || $src === '') {
				continue;
			}
			$class = $this->_extract_class_name($src);
			if (!$class || in_array($class, $blacklist, true)) {
				continue;
			}

			// 2) Résoudre la classe (App\Controllers\<Nom>, chargée par l'autoloader
			//    PSR-4) puis utiliser Reflection pour TOUTES les méthodes publiques
			//    (héritées comprises).
			$fq_class = 'App\\Controllers\\' . $class;
			if (!class_exists($fq_class)) {
				continue; // fichier "exotique" non chargeable, on ignore proprement
			}

			$own_methods       = $this->_methods_declared_in_file($fq_class, $file);
			$inherited_methods = $this->_inherited_actions_for($fq_class, $inherited_actions);

			$methods = array_values(array_unique(array_merge($own_methods, $inherited_methods)));
			sort($methods);

			$result[$class] = $methods;
		}

		ksort($result);
		return $result;
	}

	/**
	 * Extrait le nom de la première classe déclarée dans un source PHP,
	 * sans charger ni instancier la classe (lecture par tokens).
	 *
	 * @param string $src
	 * @return string|null
	 */
	private function _extract_class_name($src)
	{
		if (!function_exists('token_get_all')) {
			return null;
		}
		$tokens = @token_get_all($src);
		if (!is_array($tokens)) {
			return null;
		}
		$count = count($tokens);
		for ($i = 0; $i < $count; $i++) {
			$t = $tokens[$i];
			if (is_array($t) && $t[0] === T_CLASS) {
				for ($j = $i + 1; $j < $count; $j++) {
					if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
						return $tokens[$j][1];
					}
				}
			}
		}
		return null;
	}

	/**
	 * Retourne les méthodes publiques exposables comme action URL qui sont
	 * réellement DÉCLARÉES dans le fichier du contrôleur (pas héritées).
	 *
	 * On filtre :
	 *   - le constructeur, index, et les magiques
	 *   - les méthodes commençant par '_' (convention CI = privé URL)
	 *   - les méthodes dont le fichier source ne correspond pas
	 *
	 * @param string $class
	 * @param string $file  Chemin absolu du fichier du contrôleur
	 * @return array
	 */
	private function _methods_declared_in_file($class, $file)
	{
		$out = array();
		try {
			$ref = new ReflectionClass($class);
		} catch (\Throwable $e) {
			return $out;
		}
		// realpath des deux côtés pour comparer fiablement (Windows et symlinks)
		$file_norm = realpath($file) ?: $file;
		foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $m) {
			if ($m->isStatic() || $m->isConstructor() || $m->isDestructor()) { continue; }
			$name = $m->getName();
			if ($name === 'index')      { continue; }
			if (strpos($name, '_') === 0)  { continue; }
			if (strpos($name, '__') === 0) { continue; }

			$decl_file = $m->getFileName();
			if (!$decl_file) { continue; }
			$decl_file_norm = realpath($decl_file) ?: $decl_file;
			if ($decl_file_norm !== $file_norm) { continue; } // hérité, traité ailleurs

			$out[] = $name;
		}
		return $out;
	}

	/**
	 * Retourne les actions héritées qui s'appliquent à un contrôleur donné.
	 *
	 * Les contrôleurs métier héritent de CrudController, qui fournit list/add/
	 * edit/delete/view/clear_filters/bulk. Mais toutes ne sont pertinentes
	 * que si le contrôleur les autorise via $_autorize. On lit donc cette
	 * propriété protégée par Reflection.
	 *
	 * Règle :
	 *   - une action héritée est exposée SI elle figure dans la whitelist
	 *     ET (elle est = true dans $_autorize OU c'est un utilitaire toujours
	 *     présent type 'clear_filters'/'bulk').
	 *
	 * @param string $class
	 * @param array  $whitelist  ex. ['list','add','edit','delete','view','clear_filters','bulk']
	 * @return array
	 */
	private function _inherited_actions_for($class, array $whitelist)
	{
		$out = array();
		try {
			$ref = new ReflectionClass($class);
		} catch (\Throwable $e) {
			return $out;
		}
		// Lecture de $_autorize (protected) sans instancier le contrôleur
		$autorize = array();
		if ($ref->hasProperty('_autorize')) {
			$prop = $ref->getProperty('_autorize');
			$prop->setAccessible(true);
			$default = $prop->getDeclaringClass()->getDefaultProperties();
			if (isset($default['_autorize']) && is_array($default['_autorize'])) {
				$autorize = $default['_autorize'];
			}
			// Tente aussi les valeurs par défaut au niveau de la classe enfant
			$child_defaults = $ref->getDefaultProperties();
			if (isset($child_defaults['_autorize']) && is_array($child_defaults['_autorize'])) {
				$autorize = $child_defaults['_autorize'];
			}
		}

		// Dernier recours : si le constructeur du contrôleur affecte $_autorize
		// par instruction (cas classique ici), on parse le fichier pour extraire
		// le tableau littéral. Cf. _autorize_from_source().
		if (empty($autorize)) {
			$src = @file_get_contents($ref->getFileName());
			if ($src) {
				$autorize = $this->_autorize_from_source($src);
			}
		}

		// Utilitaires toujours exposés (mécanique de la liste, pas un CRUD)
		$always_on = array('clear_filters', 'bulk');

		foreach ($whitelist as $action) {
			if (in_array($action, $always_on, true)) {
				$out[] = $action;
				continue;
			}
			if (!empty($autorize[$action])) {
				$out[] = $action;
			}
		}
		return $out;
	}

	/**
	 * Extrait par regex le tableau littéral assigné à $this->_autorize dans
	 * un constructeur. Suffisant pour la convention utilisée dans ce projet :
	 *   $this->_autorize = array('add'=>true,'edit'=>true,...);
	 * ou
	 *   $this->_autorize = ['add'=>true, ...];
	 *
	 * @param string $src
	 * @return array
	 */
	private function _autorize_from_source($src)
	{
		// Capture le contenu entre 'array(' ... ')' OU '[' ... ']' qui suit '_autorize ='
		if (!preg_match('/_autorize\s*=\s*(array\s*\(|\[)/', $src, $m, PREG_OFFSET_CAPTURE)) {
			return array();
		}
		$open_pos = $m[1][1];
		$open_char = (substr($src, $open_pos, 1) === '[') ? '[' : '(';
		$close_char = ($open_char === '[') ? ']' : ')';
		// Avancer après le caractère d'ouverture (gérer 'array(')
		$start = ($open_char === '[') ? $open_pos + 1 : strpos($src, '(', $open_pos) + 1;

		$depth = 1;
		$len = strlen($src);
		$buf = '';
		for ($i = $start; $i < $len; $i++) {
			$c = $src[$i];
			if ($c === $open_char) { $depth++; }
			elseif ($c === $close_char) {
				$depth--;
				if ($depth === 0) { break; }
			}
			$buf .= $c;
		}
		// Parse les paires 'key'=>true|false|1|0
		$result = array();
		if (preg_match_all('/[\'"]([A-Za-z_][A-Za-z0-9_]*)[\'"]\s*=>\s*(true|false|1|0)/i', $buf, $mm)) {
			foreach ($mm[1] as $k => $key) {
				$val = strtolower($mm[2][$k]);
				$result[$key] = ($val === 'true' || $val === '1');
			}
		}
		return $result;
	}

	/**
	 * Whitelist des méthodes publiques de CrudController qui sont des actions
	 * URL légitimes (= candidates à figurer dans acl_actions). Les autres
	 * méthodes publiques de CrudController (set_ref_field, render_view, init,
	 * LoadModel, _set, _get…) sont des helpers techniques, pas des actions.
	 *
	 * @return array
	 */
	private function _inherited_actions_whitelist()
	{
		return array(
			'list', 'add', 'edit', 'delete', 'view',
			'clear_filters', 'bulk',
		);
	}

	/**
	 * Liste des classes à ne PAS référencer dans la table ACL.
	 * Les contrôleurs ouverts au public (pas d'auth) n'ont rien à y faire.
	 *
	 * @return array
	 */
	private function _scan_blacklist()
	{
		return array(
			// Contrôleurs publics non soumis à l'ACL
			'Publics',
			'Login',
			// Eventuels helpers de routage
			'Welcome',
			// Classes de base (non routables)
			'BaseController',
			'CrudController',
		);
	}

	/**
	 * Compare le code (scanné) avec la base et calcule le diff.
	 *
	 * @param array $scanned             [ class => [methods] ]
	 * @param array $db_ctrls            [ class => row ]
	 * @param array $db_actions_by_ctrl  [ id_ctrl => [action => row] ]
	 * @return array {
	 *     new_ctrls         : [ class => [methods] ],
	 *     new_actions       : [ class => ['id_ctrl' => int, 'actions' => [methods]] ],
	 *     ok_ctrls          : [ class => [methods déjà en base] ],
	 *     obsolete_ctrls    : [ class => row ],
	 *     obsolete_actions  : [ class => ['id_ctrl' => int, 'actions' => [methods]] ],
	 * }
	 */
	private function _diff_acl(array $scanned, array $db_ctrls, array $db_actions_by_ctrl)
	{
		$new_ctrls        = array();
		$new_actions      = array();
		$ok_ctrls         = array();
		$obsolete_ctrls   = array();
		$obsolete_actions = array();

		// 1) Côté code → BDD
		foreach ($scanned as $class => $methods) {
			if (!isset($db_ctrls[$class])) {
				// contrôleur entièrement nouveau
				$new_ctrls[$class] = $methods;
				continue;
			}
			$id_ctrl = (int) $db_ctrls[$class]->id;
			$existing_actions = isset($db_actions_by_ctrl[$id_ctrl])
				? array_keys($db_actions_by_ctrl[$id_ctrl])
				: array();

			$missing = array_values(array_diff($methods, $existing_actions));
			$present = array_values(array_intersect($methods, $existing_actions));

			if (!empty($missing)) {
				$new_actions[$class] = array(
					'id_ctrl' => $id_ctrl,
					'actions' => $missing,
				);
			}
			if (!empty($present)) {
				$ok_ctrls[$class] = $present;
			}
		}

		// 2) Côté BDD → code  (obsolètes)
		foreach ($db_ctrls as $class => $row) {
			if (!isset($scanned[$class])) {
				$obsolete_ctrls[$class] = $row;
				continue;
			}
			$id_ctrl = (int) $row->id;
			$existing_actions = isset($db_actions_by_ctrl[$id_ctrl])
				? array_keys($db_actions_by_ctrl[$id_ctrl])
				: array();
			$gone = array_values(array_diff($existing_actions, $scanned[$class]));
			if (!empty($gone)) {
				$obsolete_actions[$class] = array(
					'id_ctrl' => $id_ctrl,
					'actions' => $gone,
				);
			}
		}

		ksort($new_ctrls);
		ksort($new_actions);
		ksort($ok_ctrls);
		ksort($obsolete_ctrls);
		ksort($obsolete_actions);

		return array(
			'new_ctrls'        => $new_ctrls,
			'new_actions'      => $new_actions,
			'ok_ctrls'         => $ok_ctrls,
			'obsolete_ctrls'   => $obsolete_ctrls,
			'obsolete_actions' => $obsolete_actions,
		);
	}

	/**
	 * Applique les ajouts validés par l'utilisateur (cases cochées).
	 *
	 * @param array $scanned        [ class => [methods] ]
	 * @param array $db_ctrls       [ class => row ] (état AVANT scan)
	 * @param array $picks_ctrls    [ 'NomController', ... ]  (à créer en BDD)
	 * @param array $picks_actions  [ 'NomController::action', ... ]
	 * @return array { ctrls_added: int, actions_added: int }
	 */
	private function _apply_scan(array $scanned, array $db_ctrls, array $picks_ctrls, array $picks_actions)
	{
		$ctrls_added   = 0;
		$actions_added = 0;
		$now = date('Y-m-d H:i:s');

		// 1) Insertion des contrôleurs choisis
		foreach ($picks_ctrls as $class) {
			if (!isset($scanned[$class]) || isset($db_ctrls[$class])) {
				continue; // sécurité : n'insère que ce qui a été réellement scanné et absent
			}
			$obj = new \stdClass();
			$obj->controller = $class;
			$obj->actions    = ''; // champ historique ; les vraies actions vont dans acl_actions
			$obj->created    = $now;
			$obj->updated    = $now;
			$new_id = $this->{$this->_model_name}->post($obj);
			$ctrls_added++;

			// On rafraîchit l'index pour permettre l'ajout d'actions ci-dessous
			if ($new_id) {
				$row = new \stdClass();
				$row->id = (int) $new_id;
				$row->controller = $class;
				$db_ctrls[$class] = $row;
			} else {
				// retombe : on relit la base pour récupérer l'id
				$this->_reset_model_for_kpi($this->_model_name);
				$this->{$this->_model_name}->_set('filter', array('controller' => $class));
				$rows = $this->{$this->_model_name}->get_all();
				if (!empty($rows)) {
					$db_ctrls[$class] = $rows[0];
				}
			}
		}

		// 2) Insertion des actions choisies
		foreach ($picks_actions as $pick) {
			$parts = explode('::', $pick, 2);
			if (count($parts) !== 2) { continue; }
			list($class, $action) = $parts;

			if (!isset($scanned[$class]) || !in_array($action, $scanned[$class], true)) {
				continue; // l'action doit exister réellement dans le code
			}
			if (!isset($db_ctrls[$class])) {
				continue; // contrôleur introuvable (pas créé / pas coché)
			}
			$id_ctrl = (int) $db_ctrls[$class]->id;

			$obj = new \stdClass();
			$obj->id_ctrl = $id_ctrl;
			$obj->action  = $action;
			$obj->created = $now;
			$obj->updated = $now;
			$this->Acl_actions_model->post($obj);
			$actions_added++;
		}

		return array(
			'ctrls_added'   => $ctrls_added,
			'actions_added' => $actions_added,
		);
	}

	/**
	 * Réinitialise un modèle avant un get_all() de KPI.
	 *
	 * CrudController::list() a posé sur le modèle un état (order en TABLEAU
	 * pour la pile de tris vague 2, filter, pagination, global_search…)
	 * qui ferait planter ou fausserait les comptages. On force ici un état
	 * neutre : pas de filtre, pas de pagination, tri simple sur l'id.
	 *
	 * @param string $model_name
	 * @return void
	 */
	private function _reset_model_for_kpi($model_name)
	{
		$this->{$model_name}->_set('filter',         null);
		$this->{$model_name}->_set('global_search',  null);
		$this->{$model_name}->_set('per_page',       0);
		$this->{$model_name}->_set('page',           null);
		$this->{$model_name}->_set('order',          'id');
		$this->{$model_name}->_set('direction',      'asc');
	}

	/**
	 * Charge en une seule passe les jeux de données nécessaires aux KPIs
	 * et aux alertes (évite N+1 si les deux méthodes appellent get_all()).
	 *
	 * @return array { ctrls, actions, rules }
	 */
	private function _load_acl_data()
	{
		static $cache = null;
		if ($cache !== null) {
			return $cache;
		}

		$this->_reset_model_for_kpi($this->_model_name);
		$ctrls = $this->{$this->_model_name}->get_all();

		$this->_reset_model_for_kpi('Acl_actions_model');
		$actions = $this->Acl_actions_model->get_all();

		$this->_reset_model_for_kpi('Acl_roles_controllers_model');
		$rules = $this->Acl_roles_controllers_model->get_all();

		$cache = array(
			'ctrls'   => is_array($ctrls)   ? $ctrls   : array(),
			'actions' => is_array($actions) ? $actions : array(),
			'rules'   => is_array($rules)   ? $rules   : array(),
		);
		return $cache;
	}

	/**
	 * Calcule les indicateurs principaux affichés en tête de liste.
	 *
	 * @return array { total_ctrls, total_actions, avg_actions, total_rules, total_roles_using }
	 */
	private function _compute_acl_kpis()
	{
		$d = $this->_load_acl_data();

		$nb_ctrls   = count($d['ctrls']);
		$nb_actions = count($d['actions']);
		$avg = ($nb_ctrls > 0) ? round($nb_actions / $nb_ctrls, 1) : 0;

		$nb_rules = count($d['rules']);

		// Nombre de rôles distincts qui consomment au moins un contrôleur
		$roles_using = array();
		foreach ($d['rules'] as $r) {
			if (isset($r->id_role)) {
				$roles_using[$r->id_role] = true;
			}
		}

		return array(
			'total_ctrls'       => $nb_ctrls,
			'total_actions'     => $nb_actions,
			'avg_actions'       => $avg,
			'total_rules'       => $nb_rules,
			'total_roles_using' => count($roles_using),
		);
	}

	/**
	 * Détecte les anomalies de configuration ACL.
	 *
	 * @return array of objects { type, severity, message, items }
	 *   - type     : clé technique (orphan_actions, no_role, missing_file)
	 *   - severity : 'danger' | 'warning' | 'info'
	 *   - items    : liste des contrôleurs concernés (objets ou noms)
	 */
	private function _compute_acl_warnings()
	{
		$warnings = array();

		$d = $this->_load_acl_data();
		$ctrls = $d['ctrls'];
		if (count($ctrls) === 0) {
			return $warnings;
		}

		// Index actions par id_ctrl
		$actions_by_ctrl = array();
		foreach ($d['actions'] as $a) {
			if (isset($a->id_ctrl)) {
				$actions_by_ctrl[$a->id_ctrl] = isset($actions_by_ctrl[$a->id_ctrl])
					? $actions_by_ctrl[$a->id_ctrl] + 1
					: 1;
			}
		}

		// Index rules par id_ctrl
		$ctrl_in_rule = array();
		foreach ($d['rules'] as $r) {
			if (isset($r->id_ctrl)) {
				$ctrl_in_rule[$r->id_ctrl] = true;
			}
		}

		// 1) Contrôleurs sans aucune action (anomalie de config)
		$orphans = array();
		// 2) Contrôleurs déclarés sans le moindre droit assigné à un rôle
		$no_role = array();
		// 3) Contrôleurs déclarés en BDD mais dont le fichier PHP n'existe pas
		$missing_file = array();

		foreach ($ctrls as $c) {
			if (empty($actions_by_ctrl[$c->id])) {
				$orphans[] = $c;
			}
			if (empty($ctrl_in_rule[$c->id])) {
				$no_role[] = $c;
			}
			$file = APPPATH . 'Controllers/' . $c->controller . '.php';
			if (!is_file($file)) {
				$missing_file[] = $c;
			}
		}

		if (count($orphans)) {
			$warnings[] = (object) array(
				'type'     => 'orphan_actions',
				'severity' => 'danger',
				'message'  => tr('ACL_WARN_NO_ACTION')
					?: 'Contrôleur(s) sans aucune action déclarée :',
				'items'    => $orphans,
			);
		}
		if (count($no_role)) {
			$warnings[] = (object) array(
				'type'     => 'no_role',
				'severity' => 'warning',
				'message'  => tr('ACL_WARN_NO_ROLE')
					?: 'Contrôleur(s) non utilisé(s) par un rôle :',
				'items'    => $no_role,
			);
		}
		if (count($missing_file)) {
			$warnings[] = (object) array(
				'type'     => 'missing_file',
				'severity' => 'warning',
				'message'  => tr('ACL_WARN_MISSING_FILE')
					?: 'Contrôleur(s) déclaré(s) en base mais sans fichier PHP correspondant :',
				'items'    => $missing_file,
			);
		}

		return $warnings;
	}

	/**
	 * Ajoute en masse une action ACL sur tous les contrôleurs
	 * où elle n'existe pas encore.
	 *
	 * GET  -> affiche le formulaire + preview (dry-run).
	 * POST -> exécute l'insertion (sauf si dry_run=1).
	 */
	public function bulk_add_action()
	{
		$this->_set('view_inprogress', 'edition/Acl_bulk_add_action_view');

		$action_name = trim((string) $this->request->getPost('action_name'));
		$confirm     = (int) $this->request->getPost('confirm') === 1;

		// Récupération de tous les contrôleurs
		$ctrls = $this->{$this->_model_name}->get_all();

		$preview = array(
			'to_add'   => array(), // [ ['id'=>X, 'controller'=>'...'], ... ]
			'existing' => array(),
		);
		$inserted = 0;

		if ($action_name !== '') {
			// Validation simple : alphanumérique + underscore (cohérent avec le style CI)
			if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,254}$/', $action_name)) {
				$this->session->setFlashdata(
					'bulk_error',
					tr('Acl_controllers_controller_bulk_invalid')
				);
				$this->goTo($this->_controller_name . '/bulk_add_action');
				return;
			}

			foreach ($ctrls as $ctrl) {
				$exists = $this->Acl_actions_model->is_exist(
					null,
					null,
					array('id_ctrl' => $ctrl->id, 'action' => $action_name)
				);

				if ($exists) {
					$preview['existing'][] = array(
						'id'         => $ctrl->id,
						'controller' => $ctrl->controller,
					);
				} else {
					$preview['to_add'][] = array(
						'id'         => $ctrl->id,
						'controller' => $ctrl->controller,
					);
				}
			}

			// Étape 2 : confirmation -> on insère
			if ($confirm && count($preview['to_add'])) {
				foreach ($preview['to_add'] as $row) {
					$obj = new \stdClass();
					$obj->id_ctrl = $row['id'];
					$obj->action  = $action_name;
					$obj->created = date('Y-m-d H:i:s');
					$obj->updated = date('Y-m-d H:i:s');
					$this->Acl_actions_model->post($obj);
					$inserted++;
				}

				$this->session->setFlashdata(
					'bulk_success',
					sprintf(
						tr('Acl_controllers_controller_bulk_added_x'),
						$inserted,
						htmlspecialchars($action_name, ENT_QUOTES, 'UTF-8')
					)
				);
				$this->goTo($this->_controller_name . '/list');
				return;
			}
		}

		$this->data_view['title']       = tr($this->_controller_name)
		                                . ' : '
		                                . tr('Acl_controllers_controller_bulk_add_action');
		$this->data_view['action_name'] = $action_name;
		$this->data_view['preview']     = $preview;

		$this->render_view();
	}
}
