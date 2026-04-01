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
class Orgchart_controller extends MY_Controller {

	public $Trombi_model;

	/**
	 * @return void 
	 * @throws RuntimeException 
	 */
	public function __construct(){
		parent::__construct();
		$this->_controller_name = 'Orgchart_controller';  //controller name for routing
		$this->_model_name 		= 'Orgchart_model';	   //DataModel
		$this->_edit_view 		= 'edition/Orgchart_form';//template for editing
		$this->_list_view		= 'unique/Orgchart_view.php';
		$this->_autorize 		= array('add'=>true,'edit'=>true,'list'=>true,'delete'=>true,'view'=>false, 'featured'=>true);
		
		$this->_bg_color = 'nicdark_bg_blue';
		
		$this->LoadModel('Admwork_model');
		$this->LoadModel('Trombi_model');
		$this->LoadModel('Familys_model');
		$this->LoadModel('Grprelated_model');
		$this->LoadModel('Orgchart_model');
		$this->LoadModel('GroupesMembers_model');
		$this->LoadModel('Candidatures_model');

		

		$this->_set('_debug', FALSE);
		$this->init();

		$this->bootstrap_tools->_SetHead('assets/vendor/countto/jquery.countTo.js','js');
		
	}

	/**
	 * @brief Router Default 
	 * @returns 
	 * 
	 * 
	 */
	public function index(){
		redirect($this->_controller_name.'/orga');
	}
	
	public function featured($id){
		$this->{$this->_model_name}->UpdateHit($id);
		redirect($this->_controller_name.'/list');
	}
	/** 
	 * @return void 
	 * Override view for featured ORGA
	 * 
	 */
	public function list(){
		$this->_set('render_view', false);
		parent::list();
		foreach($this->data_view['datas'] AS $key => $data){
			$data->override_class = '';
			if ($data->hit == 1)
				$data->override_class = 'btn-success';
			
		}
		$this->render_view();
	}

	//$data->blocked

	/**
	 * Method PolulateCommission
	 *
	 * @param $type $type [explicite description]
	 * @param $id_com $id_com [explicite description]
	 *
	 * @return $comissions (array)
	 */
	private function PolulateCommission($type = null, $id_com = null){
		//Récupération des commissions et recherche des membres.
		$this->data_view['classif'] = $this->Trombi_model->_get('defs')['classif']->_get('values');
		$this->{$this->_model_name}->_set('filter', ['type'=>$type]);
		if($id_com){
			$this->{$this->_model_name}->_set('key_value',$id_com);
			$comissions = $this->{$this->_model_name}->get_one();
			$comissions->acteurs = $this->Trombi_model->GetMembers($id_com, false, ['RT']);
			$comissions->RT = $this->Trombi_model->GetMemberFromClassif($id_com,'RT');
			foreach($comissions->acteurs AS $key=>$acteur){
				$comissions->acteurs[$key]->groups = $this->Trombi_model->GetGroupeFromMember($acteur->details->id);
			}			
		} else {
			$this->{$this->_model_name}->_set('order','listorder');
			$comissions	= $this->{$this->_model_name}->get_all();
			foreach($comissions AS $key => $comission){
				$comission->acteurs = $this->Trombi_model->GetMembers($comission->id, false, ['RT']);
				$comission->RT = $this->Trombi_model->GetMemberFromClassif($comission->id,'RT');
				foreach($comission->acteurs AS $key=>$acteur){
					$comission->acteurs[$key]->groups = $this->Trombi_model->GetGroupeFromMember($acteur->details->id);
				}
			}
		}
		return $comissions;		
	}

	public function organisation(){
		$this->LoadModel('Files_model');
		$this->LoadModel('Event_model');

		$view_name = 'unique/'.$this->_controller_name.'_organisation';
		
		$this->data_view['featured'] 	= $this->{$this->_model_name}->GetHit();
		$this->data_view['organisations'] 	= $this->PolulateCommission('org');
		$this->data_view['stats'] 	= $this->_GetSessionStat();

		$this->data_view['pvca'] 	= $this->Files_model->GetFilesByType('pvca","pub','P');
		$this->data_view['reubur'] 	= $this->Event_model->GetEventByType('reubur','P');
		$this->data_view['reuca'] 	= $this->Event_model->GetEventByType('reuca','P');

		
		$this->_set('view_inprogress', $view_name );
		$this->render_view();
	}

	private function _GetSessionStat(){
		/* STATS sur la page */		
		$st =  [];
		$stats = $this->Admwork_model->stats();
		foreach($stats AS $stat){
			$design = $this->render_object->GetDesign($stat->type);
			$design->nb = $stat->nb;
			$st[$stat->type] = $design;

		}
		return $st;
	}


	public function view_one($id, $state = null){
		$this->bootstrap_tools->_SetHead('assets/js/main/jquery-ui.js','js');
		$this->bootstrap_tools->_SetHead('assets/js/toggle.js','js');
		$this->bootstrap_tools->_SetHead('assets/js/modal.js','js');

		$this->data_view['required_field'] = $this->Candidatures_model->_get('required');
		$this->data_view['id_fam'] = $this->acl->getUserId();
		$this->data_view['msg'] = '';

		if ($state){
			$this->Candidatures_model->_set('key_value', $this->input->post('id'));
			$this->Candidatures_model->delete();
			redirect($this->_controller_name.'/view_one/'.$id);
		} else {
			if ($this->input->post('form_mod')){
				if ($this->form_validation->run('Candidatures_model') === FALSE){ //les champs sont ok
					//echo debug($_POST);
					$this->bootstrap_tools->_SetHead('assets/js/activate_modal.js','js');
				} else {
					$datas = $this->_ProcessPost('Candidatures_model');	
					$this->data_view['msg'] = Lang('CANDIDATE_SENDED');
					//redirect($this->_controller_name.'/view_one/'.$id);
				}
			}
		}
		$this->data_view['candidature'] = $this->Candidatures_model->GetFrom($id, $this->data_view['id_fam']);

		$this->_set('view_inprogress','unique/Orgchart_controller_view_one');
		/* Récupération des groupes et des organisations */
		$this->data_view['group'] 	= $this->PolulateCommission('com',$id);
		$this->render_view();
	}


	/** @return void  */
	public function orga($id = null){
		if ($id){
			$this->_set('view_inprogress','unique/Orgchart_controller_trombi');
			$this->bootstrap_tools->_SetHead('assets/js/main/jquery-ui.js','js');
			$this->bootstrap_tools->_SetHead('assets/js/toggle.js','js');
	
			/* Récupération des groupes et des organisations */
			$this->data_view['organisation'] 	= $this->PolulateCommission('com',$id);
		} else {
			$this->_set('view_inprogress','unique/Orgchart_controller_orga');
			/* Récupération des groupes et des organisations */
			$this->data_view['commissions'] 	= $this->PolulateCommission('com');
			$this->data_view['organisations'] 	= $this->PolulateCommission('org');

			$this->data_view['stats'] = $this->_GetSessionStat();
		}
		$this->render_view();
	}	
}
