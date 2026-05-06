<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * User Controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
class Acl_roles_controller extends MY_Controller {

	/* Model in use */
	public $Acl_controllers_model = null;
	public $Acl_roles_controllers_model = null;
	public $Acl_actions_model = null;
	public $Acl_roles_model = null;


	public function __construct(){
		parent::__construct();

		$this->_controller_name = 'Acl_roles_controller';  //controller name for routing
		$this->_model_name 		= 'Acl_roles_model';	   //DataModel
		$this->_edit_view 		= 'edition/Acl_roles_form';//template for editing
		$this->_list_view		= 'unique/Acl_roles_view.php';
		$this->_autorize 		= array('add'=>true,'edit'=>true,'list'=>true,'delete'=>true,'view'=>false,'set_rules'=>true);


		$this->title 			= $this->lang->line('GESTION_'.$this->_controller_name);
		$this->_bg_color 		= 'nicdark_bg_red';
		$this->_set('_debug', FALSE);
		$this->init();

		$this->load->model('Acl_controllers_model');
		$this->load->model('Acl_roles_controllers_model');
		$this->load->model('Acl_actions_model');
	}

	/**
	 * Définition des zones fonctionnelles permettant de regrouper les
	 * contrôleurs ACL dans la vue set_rules.
	 *
	 * Chaque zone est décrite par :
	 *   - label   : clé i18n affichée dans le titre du groupe
	 *   - icon    : icône Open Iconic (oi-*)
	 *   - color   : classe utilitaire nicdark_bg_* (rappel visuel cohérent
	 *               avec la charte du back-office)
	 *   - match   : liste de noms de contrôleurs OU préfixes (suffixés '*')
	 *               permettant le rattachement à la zone
	 *
	 * NB : l'ordre du tableau est l'ordre d'affichage des zones. Le dernier
	 * groupe ('other') sert de fourre-tout pour ne jamais perdre un
	 * contrôleur nouvellement ajouté en base.
	 *
	 * @return array
	 */
	private function _get_zones_definition()
	{
		return array(
			'sys' => array(
				'label' => 'ACL_ZONE_SYS',
				'icon'  => 'oi-key',
				'color' => 'nicdark_bg_red',
				'match' => array('Acl_*', 'Sendmail_controller'),
			),
			'config' => array(
				'label' => 'ACL_ZONE_CONFIG',
				'icon'  => 'oi-cog',
				'color' => 'nicdark_bg_violet',
				'match' => array('Options_controller', 'Templates_controller', 'Parameters'),
			),
			'orga' => array(
				'label' => 'ACL_ZONE_ORGA',
				'icon'  => 'oi-people',
				'color' => 'nicdark_bg_green',
				'match' => array(
					'Orgchart_controller', 'Candidatures_controller',
					'GroupesMembers_controller', 'Files_controller',
					'Event_controller',
				),
			),
			'travaux' => array(
				'label' => 'ACL_ZONE_TRAVAUX',
				'icon'  => 'oi-wrench',
				'color' => 'nicdark_bg_yellow',
				'match' => array(
					'Admwork_controller', 'Units_controller',
					'Familys_controller', 'Histo_controller',
				),
			),
			'cantine' => array(
				'label' => 'ACL_ZONE_CANTINE',
				'icon'  => 'oi-fork',
				'color' => 'nicdark_bg_orange',
				'match' => array('Cantine_controller'),
			),
			'home' => array(
				'label' => 'ACL_ZONE_HOME',
				'icon'  => 'oi-home',
				'color' => 'nicdark_bg_bluedark',
				'match' => array('Home'),
			),
			// Toujours en dernier : tout contrôleur non rattaché.
			'other' => array(
				'label' => 'ACL_ZONE_OTHER',
				'icon'  => 'oi-puzzle-piece',
				'color' => 'nicdark_bg_grey',
				'match' => array(),
			),
		);
	}

	/**
	 * Range un contrôleur dans une zone en fonction des règles
	 * de _get_zones_definition(). Retombe sur 'other' si rien ne matche.
	 *
	 * @param  string $controller_name  ex. 'Acl_users_controller'
	 * @param  array  $zones            issu de _get_zones_definition()
	 * @return string                    clé de la zone
	 */
	private function _resolve_zone($controller_name, array $zones)
	{
		foreach ($zones as $key => $def) {
			if ($key === 'other') {
				continue;
			}
			foreach ($def['match'] as $pattern) {
				// Match exact
				if ($pattern === $controller_name) {
					return $key;
				}
				// Match par préfixe : 'Acl_*' couvre Acl_users_controller, etc.
				if (substr($pattern, -1) === '*') {
					$prefix = rtrim($pattern, '*');
					if (strncmp($controller_name, $prefix, strlen($prefix)) === 0) {
						return $key;
					}
				}
			}
		}
		return 'other';
	}

	public function set_rules($id){

		$this->_set('view_inprogress','edition/Set_rules_view');

		$this->{$this->_model_name}->_set('key_value',$id);
		$dba_data = $this->{$this->_model_name}->get_one();

		$this->data_view['title'] = $this->lang->line($this->_controller_name).' : '.$dba_data->role_name;

		if ($this->input->post('form_mod') == 'roles'){
			if ($this->input->post('rules')){
				$this->Acl_roles_controllers_model->DelRole($id);
				foreach($this->input->post('rules') AS $rule){
					list($id_ctrl,$id_act) = explode('_', $rule);
					$acl_rca = new StdClass();
					$acl_rca->id_role = $id;
					$acl_rca->id_ctrl = $id_ctrl;
					$acl_rca->id_act = $id_act;
					$acl_rca->allow = 1;
					$this->Acl_roles_controllers_model->post($acl_rca);
				}
			}
		}

		// --- Chargement des contrôleurs + actions associées ----------------
		$this->data_view['ctrls'] 	= $this->Acl_controllers_model->get_all();
		$acl_rca = $this->Acl_roles_model->getRolePermissions($id);
		$this->data_view['id'] 	= $id;

		foreach($this->data_view['ctrls'] AS $key=>$ctrl){
			$this->Acl_actions_model->_set('filter',['id_ctrl'=>$ctrl->id]);
			$this->data_view['ctrls'][$key]->actions = $this->Acl_actions_model->get_all();
			foreach($this->data_view['ctrls'][$key]->actions AS $key_action=>$action){
				$rule = strtolower($ctrl->controller.'/'.$action->action);
				$value = FALSE;
				if (isset($acl_rca[$id]) && count($acl_rca[$id]) > 0){
					if (in_array($rule,$acl_rca[$id])){
						$value = TRUE;
					}
				}
				$this->data_view['ctrls'][$key]->actions[$key_action]->allow = $value;
			}
		}

		// --- Regroupement des contrôleurs par zone -------------------------
		// On construit une structure prête à consommer dans la vue :
		//   $zones_view = [
		//     'sys' => (object) ['def' => {label, icon, color}, 'ctrls' => [...]],
		//     ...
		//   ]
		// Les zones vides sont supprimées du tableau final pour ne pas
		// afficher d'accordéon inutile.
		$zones_def = $this->_get_zones_definition();
		$zones_view = array();
		foreach ($zones_def as $key => $def) {
			$zones_view[$key] = (object) array(
				'key'         => $key,
				'label'       => $def['label'],
				'icon'        => $def['icon'],
				'color'       => $def['color'],
				'ctrls'       => array(),
				'total_acts'  => 0,
				'total_allow' => 0,
			);
		}

		foreach ($this->data_view['ctrls'] as $ctrl) {
			$zone_key = $this->_resolve_zone($ctrl->controller, $zones_def);
			$zones_view[$zone_key]->ctrls[] = $ctrl;
			if (is_array($ctrl->actions)) {
				$zones_view[$zone_key]->total_acts += count($ctrl->actions);
				foreach ($ctrl->actions as $a) {
					if ($a->allow) {
						$zones_view[$zone_key]->total_allow++;
					}
				}
			}
		}

		// On retire les zones sans contrôleur (peu probable pour 'sys' mais
		// très probable pour 'other' dans une installation propre).
		foreach ($zones_view as $k => $z) {
			if (count($z->ctrls) === 0) {
				unset($zones_view[$k]);
			}
		}

		$this->data_view['zones'] = $zones_view;

		$this->render_view();
	}

}
