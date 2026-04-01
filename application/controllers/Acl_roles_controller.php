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

		$this->render_view();
	}

}
