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
class Units_controller extends MY_Controller {


	/**
	 * @return void 
	 * @throws RuntimeException 
	 */
	public function __construct(){
		parent::__construct();
		$this->_controller_name = 'Units_controller';  //controller name for routing
		$this->_model_name 		= 'Units_model';	   //DataModel
		$this->_edit_view 		= 'edition/Units_controller_form';//template for editing
		$this->_list_view		= 'unique/Units_controller_view.php';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>true,'valid'=>true);
		$this->title 			.=  $this->lang->line('GESTION').$this->lang->line($this->_controller_name);

		$this->_bg_color = 'nicdark_bg_violet';

		$this->init();
		//pour dire, on affiche pas les boutons ajout et list dans les listes
		$this->render_object->_set('_not_link_list', ['add','list']);

	}


	/** 
	 * @return void 
	 * Override view for change filter
	 * 
	 */
	public function list(){
		$this->_set('render_view', false);
		parent::list();
		$this->data_view['civil_year'] = $this->{$this->_model_name}->_get('defs')['civil_year']->_get('values');
		$this->data_view['filter_ec'] = $this->set_civil_years();

		//$this->_set('view_inprogress','unique/'.$this->_controller_name.'_list');
		$this->render_view();
	}

	/**
	 * @return void 
	 * @throws RuntimeException 
	 */
	public function valids(){
		$this->bootstrap_tools->_SetHead('assets/js/unit_calc.js','js');

		$this->LoadModel('Infos_model');
		$this->LoadModel('Admwork_model');

		$this->Infos_model->_set('order','travaux.date_travaux');
		$this->Infos_model->_set('direction','desc');
		$elements = $this->input->post('elements');
		//@todo gestion des erreurs nl 
		if ($this->input->post('form_mod') == 'valid' ){
			foreach($elements AS $id){
				$info = [];
				$info['heure_debut_effective'] = $this->input->post('heure_debut_prevue'.$id);
				$info['heure_fin_effective'] = $this->input->post('heure_fin_prevue'.$id);
				$info['nb_unites_valides_effectif'] = $this->input->post('nb_units'.$id);
				$info['nb_unites_valides'] = 0;
				$this->Infos_model->valid_unit($id,$info);
			}
			redirect($this->_get('_controller_name').'/valid');
		}
		if (!$elements)
			redirect($this->_get('_controller_name').'/valid');
		//objet options dans le  model Infos_model
		$opt = new StdClass();
		$opt->ids = $elements;
		$opt->full = false;
		$this->data_view['units'] = $this->populate($opt);
		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_valids');
		$this->render_view();
	}

	/**
	 * @param mixed $opt : options dans le model Infos_model
	 * @return stdClass $res->works    //travaux dans les sessions de temps (un par id_travaux)
	 *                  $res->familys  //famille dans les sessions de temps
	 *                  $res->sessions //une entrée par id_travaux (les unités du travail)
	 *                  $res->dates    //dates distinctes
	 */
	private function populate($opt){
		// pour le filtrage des familles dans la vue
		$this->Infos_model->_set('order',     'travaux.date_travaux');
		$this->Infos_model->_set('direction', 'desc');

		$familys = $this->Infos_model->_get('defs')['id_famille']->_get('values');

		$res = new stdclass();
		$res->works    = [];
		$res->sessions = [];
		$res->familys  = [];
		$res->dates    = [];

		$units = $this->Infos_model->GetUnits($opt);
		if (count($units)) {
			foreach ($units as $unit) {
				// Groupement systématique par id_travaux, peu importe le type_session.
				// Le type_session ne sert qu'à savoir si on affiche les heures dans la vue.
				$ref = $unit->id_travaux;

				$res->sessions[$ref][] = $unit;

				// Familles concernées (utile pour les filtres)
				if (isset($familys[$unit->id_famille])) {
					$res->familys[$unit->id_famille] = $familys[$unit->id_famille];
				}

				// Dates distinctes (utile pour les filtres)
				$res->dates[$unit->date_travaux] = $unit->date_travaux;

				// Métadonnées du travail (un seul jeu par id_travaux)
				if (!isset($res->works[$ref])) {
					$def = new stdClass();
					$def->titre            = $unit->titre;
					$def->referent_travaux = $unit->referent_travaux;
					$def->type_session     = $unit->type_session;
					$def->date_travaux     = $unit->date_travaux;
					$res->works[$ref]      = $def;
				}
			}
		}

		return $res;
	}


	/**
	 * @return void
	 * @throws RuntimeException
	 */
	public function valid(){
		$this->LoadModel('Infos_model');
		$this->LoadModel('Admwork_model');

		// JS / CSS dédiés à la refonte
		$this->bootstrap_tools->_SetHead('assets/js/modeucheckall.js'    , 'js');  // conservé pour compat éventuelle
		$this->bootstrap_tools->_SetHead('assets/js/units_valid.js' , 'js');
		$this->bootstrap_tools->_SetHead('assets/css/units_valid.css', 'css');

		$this->_set('view_inprogress', 'unique/'.$this->_controller_name.'_valid');

		// var are stored in session
		$this->Infos_model->_set('global_search', $this->session->userdata($this->set_ref_field('global_search')));
		$this->Infos_model->_set('order'        , $this->session->userdata($this->set_ref_field('order')));
		$this->Infos_model->_set('filter'       , $this->session->userdata($this->set_ref_field('filter')));
		$this->Infos_model->_set('direction'    , $this->session->userdata($this->set_ref_field('direction')));

		// options dans le model Infos_model
		$opt = new StdClass();
		$opt->context = 'pending';
		$opt->full    = false;

		$units = $this->populate($opt);
		$this->data_view['units'] = $units;

		// filtrage des familles / dates pour la vue
		$this->Infos_model->_get('defs')['id_famille']->_set('values'   , $units->familys);
		$this->Admwork_model->_get('defs')['date_travaux']->_set('values', $units->dates);

		$this->render_view();
	}
}
