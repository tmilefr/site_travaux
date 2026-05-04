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
		$this->LoadModel('Trombi_model');
		

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

		//->where('type', 'can')
		parent::list();

		$this->data_view['civil_year'] = $this->{$this->_model_name}->_get('defs')['civil_year']->_get('values');
		$this->data_view['filter_ec'] = $this->set_civil_years();

		//$this->_set('view_inprogress','unique/'.$this->_controller_name.'_list');		
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

	public function register() {

		/* --- Nouveaux assets à ajouter --- */
		$this->bootstrap_tools->_SetHead('assets/css/admwork_register.css', 'css');
		$this->bootstrap_tools->_SetHead('assets/js/admwork_register.js',   'js');
    	//$this->bootstrap_tools->_SetHead('assets/js/index.global.min.js', 'js');
	
		/* Archivage automatique des anciens travaux (1 fois / jour max) */
		$this->_maybe_archive_old_works(30);

		$this->data_view['WorkType'] = $this->Admwork_model->_get('defs')['type']->_get('values');
		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_register');
		$this->{$this->_model_name}->_set('order','date_travaux');
	
		if ($this->acl->getType() == 'fam') {
			$id_fam = $this->acl->getUserId();
			$family = $this->Familys_model->GetFamily($id_fam);
			$works  = $this->{$this->_model_name}->GetFiltered($this->config->item('civil_year'), ['B', $family->ecole]);
		} else {
			$works = $this->{$this->_model_name}->GetFiltered($this->config->item('civil_year'), ['B','M','L']);
		}
	
		$today = date('Y-m-d');
		$planified_works = [];
		foreach ($works as $key => $work) {
			if ($work->type == 'URG') {
				$work->delay = -1;
			} else {
				$work->delay = Compare('date', $work->date_travaux, date('Y-m-d')) + 1;
			}
			$work->register       = true;
			$work->participant    = $this->Infos_model->Decompte($work->id)->nb_participants;
			$work->already_registred = $this->Infos_model->IsRegister($this->acl->getUserId(), $work->id);
			$work->registreds     = $this->Infos_model->GetRegistred($work->id, true);
	
			if ($work->registreds >= $work->nb_inscrits_max) {
				$work->register = false;
			}

			$is_archived = ((int)$work->archived === 1);
			$is_past     = ($work->type !== 'URG' && $work->date_travaux < $today);
			if (!$is_archived && !$is_past) {
				$planified_works[] = $work;
			}
		}
		$this->data_view['works'] = $planified_works;
		$this->render_view();
	}


	/**
	 * Lance l'archivage des anciens travaux au plus une fois par jour.
	 * Utilise un fichier témoin dans application/cache/ pour éviter de
	 * solliciter la base à chaque requête.
	 */
	private function _maybe_archive_old_works($grace_days = 30){
		$flag = APPPATH.'cache/last_archive_run.txt';
		$today = date('Y-m-d');
		if (is_file($flag) && trim(@file_get_contents($flag)) === $today){
			return;
		}
		$this->Admwork_model->ArchiveOldWorks($grace_days);
		@file_put_contents($flag, $today);
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
			if ($this->acl->hasAccess('Admwork_controller/managed_one')){
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



	// -----------------------------------------------------------------------
	// Point d'entrée GUEST : accès par lien email tokenisé
	// -----------------------------------------------------------------------

	public function validate_by_token($token = null)
	{
		$this->LoadModel('ValidationToken_model');
		$this->LoadModel('Admwork_model');
		$this->LoadModel('Infos_model');
		$this->LoadModel('Familys_model');
		$this->LoadModel('Trombi_model');

		$tk = $this->ValidationToken_model->findValid($token);
		if (!$tk) {
			$this->data_view['error'] = $this->lang->line('REF_TOKEN_INVALID');
			$this->_set('view_inprogress', 'unique/Admwork_controller_token_error');
			$this->render_view();
			return;
		}

		$this->_HandleRefActions($tk->id_travaux, $tk->id_fam_ref, $tk->token, $tk->id);
	}

	// -----------------------------------------------------------------------
	// Point d'entrée connecté : l'utilisateur "fam" accède via le menu
	// -----------------------------------------------------------------------

	public function validate_one($id_work)
	{
		if (!$id_work || $this->acl->getType() !== 'fam') {
			redirect('Home/no_right');
		}
		if (!$this->_IsReferentOfWork($id_work)) {
			redirect('Home/no_right');
		}
		$this->_HandleRefActions($id_work, $this->acl->getUserId(), null, null);
	}

	// -----------------------------------------------------------------------
	// Liste des sessions où l'utilisateur connecté est référent
	// -----------------------------------------------------------------------

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
	// Orchestration commune aux deux points d'entrée
	// -----------------------------------------------------------------------

	/**
	 * Gère l'affichage + les actions POST (add / remove / validate).
	 *
	 * @param int    $id_work    Session concernée
	 * @param int    $id_fam_ref famille.id du référent qui agit
	 * @param string $token      Token si accès par lien email, null sinon
	 * @param int    $token_id   PK du token en base (pour markUsed), null sinon
	 * @return void
	 */
	private function _HandleRefActions($id_work, $id_fam_ref, $token, $token_id)
	{
		$this->data_view['msg']       = '';
		$this->data_view['via_token'] = ($token !== null);
		$this->data_view['token']     = $token;

		// Pré-chargement pour connaître la date
		$this->Admwork_model->_set('key_value', $id_work);
		$work_meta = $this->Admwork_model->get_one();
		$is_open   = (strtotime($work_meta->date_travaux) <= strtotime('today'));
		$this->data_view['is_validation_open'] = $is_open;

		// ---- Traitement du POST ----
		$action = $this->input->post('action');

		if ($action === 'validate' && $is_open && $this->input->post('elements')) {
			$this->_ProcessRefValidation($id_work, $id_fam_ref);
			if ($token_id) {
				$this->ValidationToken_model->markUsed($token_id);
			}
			$this->data_view['msg'] = '<div class="alert alert-success">'
				. $this->lang->line('REF_VALIDATE_SAVED') . '</div>';

		} elseif ($action === 'remove' && !$is_open) {
			$this->_ProcessRefRemove($id_work);
			$this->data_view['msg'] = '<div class="alert alert-success">'
				. $this->lang->line('REF_REMOVE_SAVED') . '</div>';

		} elseif ($action === 'add' && !$is_open) {
			$added = $this->_ProcessRefAdd($id_work);
			if ($added === true) {
				$this->data_view['msg'] = '<div class="alert alert-success">'
					. $this->lang->line('REF_ADD_SAVED') . '</div>';
			} else {
				$this->data_view['msg'] = '<div class="alert alert-warning">'
					. $added . '</div>';
			}
		}

		// ---- Rechargement complet après toute action ----
		$work = $this->_BuildWorkForRefView($id_work);
		$this->data_view['work']   = $work;
		$this->data_view['design'] = $this->render_object->GetDesign($work->type);

		// ---- Familles disponibles pour inscription (mode gestion) ----
		if (!$is_open) {
			$this->data_view['available_families'] = $this->_GetFamiliesAvailableForWork($work);
			// Options pour type_participant (depuis Infos_model)
			$this->data_view['type_participant_values'] =
				$this->Infos_model->_get('defs')['type_participant']->_get('values');
		}

		$this->_set('view_inprogress', 'unique/Admwork_controller_validate_one_ref');
		$this->render_view();
	}

	// -----------------------------------------------------------------------
	// Traitements POST
	// -----------------------------------------------------------------------

	/**
	 * Inscrit une famille à la session (mode gestion).
	 *
	 * @param int $id_work
	 * @return true|string  true si OK, message d'erreur sinon
	 */
	private function _ProcessRefAdd($id_work)
	{
		$id_fam            = (int) $this->input->post('id_famille');
		$type_participant  = $this->input->post('type_participant');
		if (!$id_fam || !$type_participant) {
			return $this->lang->line('REF_ADD_MISSING_FIELDS');
		}

		// Déjà inscrite ?
		if ($this->Infos_model->IsRegister($id_fam, $id_work)) {
			return $this->lang->line('REF_ADD_ALREADY_REGISTERED');
		}

		// Capacité disponible ?
		$nb_participants = ($type_participant === 'Both') ? 2 : 1;
		$current = $this->Infos_model->Decompte($id_work);
		$current = $current ? (int) $current->nb_participants : 0;
		$max     = (int) $this->Admwork_model->GetMax($id_work)->nb_inscrits_max;
		if (($current + $nb_participants) > $max) {
			return $this->lang->line('TOO_MANY_PEOPLE');
		}

		// Insertion directe dans infos
		$this->db->insert('infos', [
			'id_famille'            => $id_fam,
			'id_travaux'            => $id_work,
			'type_participant'      => $type_participant,
			'nb_participants'       => $nb_participants,
			'nb_unites_valides'     => 0,
			'type_session'          => (int) $this->input->post('type_session') ?: 1,
		]);
		return true;
	}

	/**
	 * Retire une famille de la session (mode gestion).
	 *
	 * @param int $id_work
	 * @return void
	 */
	private function _ProcessRefRemove($id_work)
	{
		$id_info = (int) $this->input->post('id_info');
		if (!$id_info) return;

		// Sécurité : l'info doit bien appartenir à CETTE session
		$info = $this->db->select('*')
			->from('infos')
			->where('id', $id_info)
			->where('id_travaux', $id_work)
			->get()
			->row();
		if (!$info) return;

		$this->Infos_model->_set('key_value', $id_info);
		$this->Infos_model->delete();
	}

	/**
	 * Traitement du POST de validation des présences (jour J et après).
	 *
	 * @param int $id_work
	 * @param int $id_fam_ref
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

			if (in_array($id_info, $to_delete)) {
				$this->Infos_model->_set('key_value', $id_info);
				$this->Infos_model->delete();
				continue;
			}

			$present     = $this->input->post('present_' . $id_info);
			$nb_units    = $this->input->post('nb_unites_' . $id_info);
			$commentaire = trim((string) $this->input->post('commentaire_' . $id_info));

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

	// -----------------------------------------------------------------------
	// Helpers
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

		// Décompte des places
		$decompte = $this->Infos_model->Decompte($id_work);
		$work->nb_inscrits = $decompte ? (int) $decompte->nb_participants : 0;
		$work->places_restantes = max(0, (int) $work->nb_inscrits_max - $work->nb_inscrits);

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
	 * Liste des familles non encore inscrites à cette session et
	 * compatibles avec l'école de la session (accespar).
	 *
	 * @param stdClass $work
	 * @return array
	 */
	private function _GetFamiliesAvailableForWork($work)
	{
		$this->db->select('famille.id, famille.nom, famille.ecole')
			->from('famille')
			->where("famille.id NOT IN (SELECT id_famille FROM infos WHERE id_travaux = " . (int) $work->id . ")", null, false);

		// Filtre école : si la session est sur une école spécifique, seules
		// les familles compatibles sont affichées (B = les deux, L ou M = une seule)
		if (!empty($work->accespar) && $work->accespar !== 'B') {
			$this->db->group_start()
				->where('famille.ecole', $work->accespar)
				->or_where('famille.ecole', 'B')
				->group_end();
		}

		$this->db->order_by('famille.nom', 'ASC');
		$rows = $this->db->get()->result();

		return $rows ?: [];
	}

}
