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
class Admwork_controller extends MY_Controller {

	public $Infos_model = null;
	public $Admwork_model = null;
	public $libpdf = null;
	public $Members_model = null;
	public $Familys_model = null;
	protected $_local_redirect = null;
	protected $_local_unit_id = null;

	/**
	 * @return void 
	 * @throws RuntimeException 
	 */
	public function __construct(){
		parent::__construct();
		$this->_controller_name = 'Admwork_controller';  //controller name for routing
		$this->_model_name 		= 'Admwork_model';	   //DataModel
		$this->_edit_view 		= 'edition/Admwork_form';//template for editing
		$this->_list_view		= 'unique/Admwork_view.php';
		$this->_autorize 		= array('add'=>true,'edit'=>true,'list'=>true,'delete'=>true,'view'=>false,"draftvalidation"=>true);
		
		$this->title 			= $this->lang->line('GESTION_'.$this->_controller_name);

		$this->_bg_color = 'nicdark_bg_red';

		$this->init();
		//Model used by pages
		$this->LoadModel('Infos_model');
		$this->LoadModel('Admwork_model');
		$this->LoadModel('Familys_model');

		$this->render_object->_set('_not_link_list', ['add','view','list','draftvalidation']);
	}



	/** @return void  */
	public function index(){
		//redirect($this->_controller_name.'/register');
	}

	/**
	 * @brief Generic list view ( Need PHP 7)
	 * @returns 
	 * 
	 * 
	 */
	public function list()
	{
		$this->_set('render_view', false);
		parent::list();
		$this->data_view['civil_year'] = $this->{$this->_model_name}->_get('defs')['civil_year']->_get('values');
		$this->data_view['filter_ec'] = $this->set_civil_years();

		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_list');		
		foreach($this->data_view['datas'] AS $key=>$data){
			$have =  $this->Infos_model->GetRegistred($data->id);
			//echo debug($have);
			$this->data_view['datas'][$key]->blocked = (($have && count($have))? true: false );
		}
		$this->render_view();
	}	
	
	public function draftvalidation(){
		$this->Admwork_model->DraftPublication( $this->set_civil_years() );
		redirect($this->_controller_name.'/list');
	}

	/**
	 * @param mixed $id_work 
	 * @param bool $override 
	 * @return mixed 
	 * @throws RuntimeException 
	 */
	function MakePdf($id_work = null, $override = true){
		$this->load->library('libpdf');
		if ($work = $this->GetWork($id_work) ){

			//echo debug($dba_data);
			$work->design = $this->render_object->GetDesign($work->type);
			$work->pdf = NameToFilename('Travaux_'.$work->date_travaux.' '.$work->titre).'.pdf';
			//echo debug($work);

			if (!is_file($this->libpdf->_get('pdf_path').$work->pdf) OR $override){
				$this->libpdf->DoPdf($work,'unique/'.$this->_controller_name.'_register_one_pdf', $work->pdf , TRUE);
			} 		
		}
	}

	/** @return void  */
	public function register(){
		
		$this->bootstrap_tools->_SetHead('assets/css/admwork_register.css', 'css');
		$this->bootstrap_tools->_SetHead('assets/js/admwork_register.js',   'js');

		$this->bootstrap_tools->_SetHead('assets/vendor/isotope/isotope.pkgd.min.js','js');
		$this->bootstrap_tools->_SetHead('assets/js/counter.js','js');
		$this->bootstrap_tools->_SetHead('assets/js/isotope.js','js');
		//type de travaux sur l'objet, permet de construire la liste de choix.
		$this->data_view['WorkType'] = $this->Admwork_model->_get('defs')['type']->_get('values');

		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_register');
		$this->{$this->_model_name}->_set('order','date_travaux');

		if ($this->acl->getType() == 'fam'){
			$id_fam = $this->acl->getUserId();
			$family = $this->Familys_model->GetFamily($id_fam);
			$works = $this->{$this->_model_name}->GetFiltered($this->config->item('civil_year'), ['B',$family->ecole]);

		} else {
			$works = $this->{$this->_model_name}->GetFiltered($this->config->item('civil_year'), ['B','M','L']);
			//$works = $this->{$this->_model_name}->get_all();
		} 		
		

		$planified_works = [];
		foreach($works AS $key=>$work){
			if ($work->type == 'URG'){
				$work->delay = -1;
			} else {
				$work->delay = Compare('date',$work->date_travaux, date('Y-m-d'))+1;
			}			
			$work->register = true;
			$work->participant = $this->Infos_model->Decompte($work->id)->nb_participants;
			$work->already_registred =  $this->Infos_model->IsRegister($this->acl->getUserId(), $work->id);
			$work->registreds = $this->Infos_model->GetRegistred($work->id, true);
			
			if ($work->registreds >= $work->nb_inscrits_max)
				$work->register = false;
			//$planified_works[] = $work;

			if ($work->archived !=  1)
				$planified_works[] = $work;
			//echo debug($work);
		}
		$this->data_view['works'] = $planified_works;

		$this->render_view();
	}

	
	/**
	 * Method register_one : user view
	 *
	 * @param $id_work $id_work [explicite description]
	 * @param $state $state [explicite description]
	 *
	 * @return void
	 */
	public function register_one($id_work,$state = null){
		$view_name = 'unique/'.$this->_controller_name.'_register_one';
		$this->_set('view_inprogress', $view_name );

		$this->data_view['msg'] = '';
		if ($id_work){
			$this->data_view['required_field'] = $this->Infos_model->_get('required');
			//TODO : have confirm message + check id
			if ($state){
				$this->delete_registration($id_work);				
			} else {
				$this->ADD_registration($id_work);
			}
			$this->GET_registration($id_work);
			$this->render_view();
		}
	}
	
	/**
	 * Method managed_one : sys view
	 *
	 * @param $id_work $id_work [id work]
	 * @param $state $state [state ]
	 *
	 * @return void
	 */
	public function managed_one($id_work,$state = null){
		$this->_set('_local_redirect',"managed_one");
		$this->_set('_local_unit_id', $state);

		$view_name = 'unique/'.$this->_controller_name.'_register_one_sys';
		$this->_set('view_inprogress', $view_name );

		$this->data_view['msg'] = '';
		if ($id_work){
			$this->data_view['required_field'] = $this->Infos_model->_get('required');
			//TODO : have confirm message + check id
			if ($state){
				$this->delete_registration($id_work);				
			} else {
				$this->ADD_registration($id_work);
			}
			$this->GET_registration($id_work);
			$this->render_view();
		}
	}


	private function delete_registration($id_work){
		$_local_unit_id = $this->_get('_local_unit_id');
		if (!$_local_unit_id)
			$_local_unit_id = $this->input->post('id');

		$this->Infos_model->_set('key_value', $_local_unit_id);
		$this->Infos_model->delete();

		echo "<p>$_local_unit_id $id_work</p>";
		/*$redirect = $this->_get('_local_redirect');
		if ($redirect){
			redirect($this->_controller_name.'/'.$redirect.'/'.$id_work);
		} else {
			redirect($this->_controller_name.'/register');
		}*/
	} 

	private function ADD_registration($id_work){
		if ($this->form_validation->run('Infos_model') === FALSE){ //les champs sont ok

		} else {
			//Injection de règle de gestion
			$_POST['nb_participants'] = (($_POST['type_participant'] == 'Both') ? 2:1);
			//Traitement
			//calcul du nombre final ! 
			$participants = $this->Infos_model->Decompte($id_work)->nb_participants;
			$max = $this->Admwork_model->GetMax($id_work)->nb_inscrits_max;
			if (($participants + $_POST['nb_participants']) > $max){
				$this->data_view['msg'] = $this->lang->line('TOO_MANY_PEOPLE');
			} else {
				$datas = $this->_ProcessPost('Infos_model');	
				$redirect = $this->_get('_local_redirect');
				if ($redirect){
					redirect($this->_controller_name.'/'.$redirect.'/'.$id_work);
				} else {
					redirect($this->_controller_name.'/register');
				}
			}
		}
	}

	private function GET_registration($id_work){
		$this->data_view['work'] = $this->GetWork($id_work);
		$this->data_view['id_fam'] = $this->acl->getUserId();
		$this->data_view['design'] = $this->render_object->GetDesign($this->data_view['work']->type);
	}

	/**
	 * Method view_one 
	 *
	 * @param $id_work $id_work [explicite description]
	 * @param $state $state [explicite description]
	 *
	 * @return void
	 */
	private function view_one($id_work,$state = null){
		$this->data_view['msg'] = '';
		if ($id_work){
			$this->data_view['required_field'] = $this->Infos_model->_get('required');
			//TODO : have confirm message + check id
			if ($state){
				$this->delete_registration($id_work);				
			} else {
				$this->ADD_registration($id_work);
			}
			$this->GET_registration($id_work);
			$this->render_view();
		}
	}

	function GetWork($id_work){
		if ($id_work){
			$this->LoadModel('Trombi_model');

			$this->{$this->_model_name}->_set('key_value',$id_work);
			$work = $this->{$this->_model_name}->get_one();
			$work->already_registred = $this->Infos_model->IsRegister($this->acl->getUserId(), $id_work);
			$work->registred = [];

			/* Récuperation du référent */
			$work->pilot = $this->Trombi_model->GetConsolidateMember($work->referent_travaux);

			//recupération de la liste des participants pour la vue admin
			if ($this->acl->getType()  == "sys"){
				$registreds = $this->Infos_model->GetRegistred($id_work);
				$this->load->model('Familys_model');
				//on recherche la famille pour chaque inscription
				if ($registreds)
				foreach($registreds AS $key=>$registred){
					$this->Familys_model->_set('key_value',$registred->id_famille);
					$registreds[$key]->family = $this->Familys_model->get_one();
				}
				$work->registred = $registreds;
			}
		}
		return $work;
	}



	/**
	 *
 * Ajoute :
 *   - validate_by_token($token)    : route publique (guest) — lien email
 *   - my_sessions()                : liste des sessions où je suis référent
 *   - validate_one($id_work)       : accès via session fam (alternative au lien)
 *   - _IsReferentOfWork($id_work)  : contrôle de sécurité
 *   - _ProcessRefValidation()      : traitement commun du POST
 *
 * ⚠️ Il faut AUSSI :
 *   - déclarer 'admwork_controller/validate_by_token' dans $guestPages
 *     de application/libraries/Acl.php
 *   - dans le __construct() du contrôleur, charger ValidationToken_model
 *
 * ============================================================================ */

	/**
	 * Point d'entrée GUEST : le référent arrive par un lien email.
	 * Pas de login requis — la sécurité tient au token.
	 *
	 * URL type : /Admwork_controller/validate_by_token/abc123...
	 *
	 * @param string $token
	 * @return void
	 */
	public function validate_by_token($token = null)
	{
		$this->LoadModel('ValidationToken_model');
		$this->LoadModel('Admwork_model');
		$this->LoadModel('Infos_model');
		$this->LoadModel('Familys_model');

		$tk = $this->ValidationToken_model->findValid($token);
		if (!$tk) {
			$this->data_view['error'] = $this->lang->line('REF_TOKEN_INVALID');
			$this->_set('view_inprogress', 'unique/Admwork_controller_token_error');
			$this->render_view();
			return;
		}

		// Session validée, on reproduit la logique de validate_one mais sans ACL
		$this->data_view['msg']     = '';
		$this->data_view['token']   = $tk->token;
		$this->data_view['via_token'] = true;

		// Traitement du POST
		if ($this->input->post('elements')) {
			$this->_ProcessRefValidation($tk->id_travaux, $tk->id_fam_ref);
			$this->ValidationToken_model->markUsed($tk->id);
			$this->data_view['msg'] = '<div class="alert alert-success">'
				. $this->lang->line('REF_VALIDATE_SAVED') . '</div>';
		}

		$work = $this->_BuildWorkForRefView($tk->id_travaux);
		$this->data_view['work']   = $work;
		$this->data_view['design'] = $this->render_object->GetDesign($work->type);

		$this->_set('view_inprogress', 'unique/Admwork_controller_validate_one_ref');
		$this->render_view();
	}

	/**
	 * Alternative (menu dans le site) : l'utilisateur connecté "fam"
	 * qui est référent de la session peut aussi y accéder.
	 *
	 * @param int $id_work
	 * @return void
	 */
	public function validate_one($id_work)
	{
		$this->LoadModel('Trombi_model');
		
		if (!$id_work || $this->acl->getType() !== 'fam') {
			redirect('Home/no_right');
		}
		if (!$this->_IsReferentOfWork($id_work)) {
			redirect('Home/no_right');
		}

		$this->data_view['msg']       = '';
		$this->data_view['via_token'] = false;
		$this->data_view['token']     = null;

		if ($this->input->post('elements')) {
			$this->_ProcessRefValidation($id_work, $this->acl->getUserId());
			$this->data_view['msg'] = '<div class="alert alert-success">'
				. $this->lang->line('REF_VALIDATE_SAVED') . '</div>';
		}

		$work = $this->_BuildWorkForRefView($id_work);
		$this->data_view['work']   = $work;
		$this->data_view['design'] = $this->render_object->GetDesign($work->type);

		$this->_set('view_inprogress', 'unique/Admwork_controller_validate_one_ref');
		$this->render_view();
	}

	/**
	 * Liste des sessions où l'utilisateur connecté est référent.
	 *
	 * @return void
	 */
	public function my_sessions()
	{
		if ($this->acl->getType() !== 'fam') {
			redirect('Home/no_right');
		}
		$this->data_view['my_works'] = $this->Admwork_model->GetWorksAsReferent(
			$this->acl->getUserId()
		);
		$this->_set('view_inprogress', 'unique/' . $this->_controller_name . '_my_sessions');
		$this->render_view();
	}

	// -----------------------------------------------------------------------
	// Helpers privés
	// -----------------------------------------------------------------------

	/**
	 * Assemble l'objet $work enrichi des inscrits + familles (pour la vue ref).
	 *
	 * @param int $id_work
	 * @return stdClass
	 */
	private function _BuildWorkForRefView($id_work)
	{
		$this->Admwork_model->_set('key_value', $id_work);
		$work = $this->Admwork_model->get_one();
		$work->pilot = $this->Trombi_model->GetConsolidateMember($work->referent_travaux);

		$registreds = $this->Infos_model->GetRegistred($id_work);
		if ($registreds) {
			foreach ($registreds as $key => $reg) {
				$this->Familys_model->_set('key_value', $reg->id_famille);
				$registreds[$key]->family = $this->Familys_model->get_one();
			}
		}
		$work->registred = $registreds ?: [];
		return $work;
	}

	/**
	 * Vérifie que l'utilisateur connecté est le référent de la session.
	 *
	 * @param int $id_work
	 * @return bool
	 */
	private function _IsReferentOfWork($id_work)
	{
		$refFamily = $this->Admwork_model->GetReferentFamily($id_work);
		if (!$refFamily) return false;
		return ((int) $refFamily->id === (int) $this->acl->getUserId());
	}

	/**
	 * Traitement commun du POST de validation référent.
	 * Gère : présence, nb d'unités, commentaire, désinscription.
	 *
	 * @param int $id_work
	 * @param int $id_fam_ref  famille.id du référent qui valide
	 * @return void
	 */
	private function _ProcessRefValidation($id_work, $id_fam_ref)
	{
		$elements    = $this->input->post('elements');
		$to_delete   = (array) $this->input->post('unregister');
		$global_com  = $this->input->post('commentaire_global');

		if (!is_array($elements)) $elements = [];

		foreach ($elements as $id_info) {
			$id_info = (int) $id_info;

			// 1) Désinscription prioritaire
			if (in_array($id_info, $to_delete)) {
				$this->Infos_model->_set('key_value', $id_info);
				$this->Infos_model->delete();
				continue;
			}

			// 2) Sinon, mise à jour présence + commentaire
			$present     = $this->input->post('present_' . $id_info);
			$nb_units    = $this->input->post('nb_unites_' . $id_info);
			$commentaire = trim((string) $this->input->post('commentaire_' . $id_info));

			// préfixe de commentaire global si fourni
			if ($global_com) {
				$commentaire = trim($global_com . ($commentaire ? ' | ' . $commentaire : ''));
			}

			$datas = [
				'nb_unites_valides' => $present ? (float) $nb_units : 0,
				'commentaire_ref'   => $commentaire ?: null,
				'valide_par_ref'    => (int) $id_fam_ref,
				'valide_ref_at'     => date('Y-m-d H:i:s'),
			];

			$this->Infos_model->valid_unit($id_info, $datas);
		}
	}

}
