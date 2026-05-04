<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Acl_controllers_controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 */
class Acl_controllers_controller extends MY_Controller {

	public $Acl_actions_model = null;

	public function __construct(){
		parent::__construct();

		$this->_controller_name = 'Acl_controllers_controller';
		$this->_model_name      = 'Acl_controllers_model';
		$this->_edit_view       = 'edition/Acl_controllers_form';
		$this->_list_view       = 'unique/Acl_controllers_view.php';
		// /!\ on ajoute bulk_add_action dans les autorisations
		$this->_autorize        = array(
			'add'             => true,
			'edit'            => true,
			'list'            => true,
			'delete'          => true,
			'view'            => false
		);

		$this->title     = $this->lang->line('GESTION_'.$this->_controller_name);
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
	 * MY_Controller::render_view() : si une vue existe dans
	 *   application/views/unique/{ControllerName}/list_view.php
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
	 * Ajoute en masse une action ACL sur tous les contrôleurs
	 * où elle n'existe pas encore.
	 *
	 * GET  -> affiche le formulaire + preview (dry-run).
	 * POST -> exécute l'insertion (sauf si dry_run=1).
	 */
	public function bulk_add_action()
	{
		$this->_set('view_inprogress', 'edition/Acl_bulk_add_action_view');

		$action_name = trim((string) $this->input->post('action_name'));
		$confirm     = (int) $this->input->post('confirm') === 1;

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
				$this->session->set_flashdata(
					'bulk_error',
					$this->lang->line('Acl_controllers_controller_bulk_invalid')
				);
				redirect($this->_controller_name . '/bulk_add_action');
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
					$obj = new StdClass();
					$obj->id_ctrl = $row['id'];
					$obj->action  = $action_name;
					$obj->created = date('Y-m-d H:i:s');
					$obj->updated = date('Y-m-d H:i:s');
					$this->Acl_actions_model->post($obj);
					$inserted++;
				}

				$this->session->set_flashdata(
					'bulk_success',
					sprintf(
						$this->lang->line('Acl_controllers_controller_bulk_added_x'),
						$inserted,
						htmlspecialchars($action_name, ENT_QUOTES, 'UTF-8')
					)
				);
				redirect($this->_controller_name . '/list');
				return;
			}
		}

		$this->data_view['title']       = $this->lang->line($this->_controller_name)
		                                . ' : '
		                                . $this->lang->line('Acl_controllers_controller_bulk_add_action');
		$this->data_view['action_name'] = $action_name;
		$this->data_view['preview']     = $preview;

		$this->render_view();
	}

	/**
	 * Calcule les indicateurs principaux affichés en tête de liste.
	 *
	 * @return array { total_ctrls, total_actions, avg_actions, total_rules, total_roles_using }
	 */
	private function _compute_acl_kpis()
	{
		$ctrls   = $this->{$this->_model_name}->get_all();
		$actions = $this->Acl_actions_model->get_all();
 
		$nb_ctrls   = is_array($ctrls)   ? count($ctrls)   : 0;
		$nb_actions = is_array($actions) ? count($actions) : 0;
		$avg = ($nb_ctrls > 0) ? round($nb_actions / $nb_ctrls, 1) : 0;
 
		// Nombre de règles ACL définies (toutes lignes de acl_roles_controllers)
		$rules = $this->Acl_roles_controllers_model->get_all();
		$nb_rules = is_array($rules) ? count($rules) : 0;
 
		// Nombre de rôles distincts qui consomment au moins un contrôleur
		$roles_using = array();
		if (is_array($rules)) {
			foreach ($rules as $r) {
				if (isset($r->id_role)) {
					$roles_using[$r->id_role] = true;
				}
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
 
		$ctrls = $this->{$this->_model_name}->get_all();
		if (!is_array($ctrls) || count($ctrls) === 0) {
			return $warnings;
		}
 
		// Index actions par id_ctrl
		$actions_by_ctrl = array();
		foreach ($this->Acl_actions_model->get_all() as $a) {
			if (isset($a->id_ctrl)) {
				$actions_by_ctrl[$a->id_ctrl] = isset($actions_by_ctrl[$a->id_ctrl])
					? $actions_by_ctrl[$a->id_ctrl] + 1
					: 1;
			}
		}
 
		// Index rules par id_ctrl
		$ctrl_in_rule = array();
		foreach ($this->Acl_roles_controllers_model->get_all() as $r) {
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
			$file = APPPATH . 'controllers/' . $c->controller . '.php';
			if (!is_file($file)) {
				$missing_file[] = $c;
			}
		}
 
		if (count($orphans)) {
			$warnings[] = (object) array(
				'type'     => 'orphan_actions',
				'severity' => 'danger',
				'message'  => $this->lang->line('ACL_WARN_NO_ACTION')
					?: 'Contrôleur(s) sans aucune action déclarée :',
				'items'    => $orphans,
			);
		}
		if (count($no_role)) {
			$warnings[] = (object) array(
				'type'     => 'no_role',
				'severity' => 'warning',
				'message'  => $this->lang->line('ACL_WARN_NO_ROLE')
					?: 'Contrôleur(s) non utilisé(s) par un rôle :',
				'items'    => $no_role,
			);
		}
		if (count($missing_file)) {
			$warnings[] = (object) array(
				'type'     => 'missing_file',
				'severity' => 'warning',
				'message'  => $this->lang->line('ACL_WARN_MISSING_FILE')
					?: 'Contrôleur(s) déclaré(s) en base mais sans fichier PHP correspondant :',
				'items'    => $missing_file,
			);
		}
 
		return $warnings;
	}
}