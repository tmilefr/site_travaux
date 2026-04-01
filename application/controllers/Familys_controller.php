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
class Familys_controller extends MY_Controller {

	/* Déclaration des Models utilisés */
	public $Capacity_model 	= null;
	public $Options_model 	= null;
	public $Familys_model 	= null;
	public $Email_model		= null;
	public $Units_model 	= null;
	public $Infos_model 	= null;


	public function __construct(){
		parent::__construct();
		
		$this->_controller_name = 'Familys_controller';  //controller name for routing
		$this->_model_name 		= 'Familys_model';	   //DataModel
		$this->_edit_view 		= 'edition/Familys_form';//template for editing
		$this->_list_view		= 'unique/Familys_view.php';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>true);
		$this->_search 			= true;

		$this->_bg_color = 'nicdark_bg_orange';
		$this->_set('_debug', FALSE);
		$this->title .= $this->lang->line('GESTION_'.$this->_controller_name);
		
		$this->init();

		$this->LoadModel('Infos_model');
		$this->LoadModel('Capacity_model');
		$this->LoadModel('Units_model');
		$this->LoadModel('Email_model');
		$this->LoadModel('Members_model');
		$this->LoadModel('Options_model');

		$this->LoadModel('Admwork_model');

		//pour dire, on affiche pas les boutons ajout et list dans les listes
		$this->render_object->_set('_not_link_list', ['add','list']);
	}


	/**
	 * @brief Router Default 
	 * @returns 
	 * 
	 * 
	 */
	public function index(){
		redirect($this->_controller_name.'/histo');
	}

	public function skills(){
		$this->bootstrap_tools->_SetHead('assets/vendor/isotope/isotope.pkgd.min.js','js');
		$this->bootstrap_tools->_SetHead('assets/js/counter.js','js');
		$this->bootstrap_tools->_SetHead('assets/js/isotope.js','js');
		
		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_skills');
		//
		
		$familys_skill = [];
		$skills = $this->Capacity_model->get_all();
		foreach($skills AS $skill){
			$familys_skill[$skill->id_fam][] = $skill->id_cap;
		}
		$familys = [];
		foreach($familys_skill AS $id_fam=>$skill){
			$this->{$this->_model_name}->_set('key_value', $id_fam);
			$family = $this->{$this->_model_name}->get_one();
			if (is_object($family)){
				$family->skill = $skill;
				$familys[] = $family;
			}
		}
		$this->data_view['familys'] = $familys;
		 
		$this->data_view['capacitys'] = $this->Options_model->GetOpt('capacity');

		$this->render_view();
	}

	/** 
	 * @return void 
	 * Override view for change filter
	 * 
	 */
	public function list(){
		$this->_set('render_view', false);
		parent::list();
		$this->data_view['civil_year'] = $this->Familys_model->_get('defs')['civil_year']->_get('values');
		$this->data_view['filter_ec'] = $this->set_civil_years();

		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_list');
		$this->render_view();
	}

	public function MassUpdate(){
		/*
		ALTER DATABASE regiomlh_prod CHARACTER SET utf8 COLLATE utf8_general_ci;
		ALTER TABLE famille CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
		*/
		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_massupdate');

		$this->csv_path = str_replace('application','public/files',APPPATH); 
		$datas = file($this->csv_path.'/familles.csv');
		$familys =[];
		foreach($datas as $key=>$lgn){
			if($key > 0) {
				$family_info = explode(';',$lgn);
				$family_info['exist'] = $this->Familys_model->GetFamilyByLogin($family_info[4]);
				if ($family_info['exist'] ){
					$this->Familys_model->SetCivilYears($family_info['exist']->id, '2025-2026');
				} else {
					$familys[] = $family_info;
				}
			}
		}
		$this->data_view['familys'] = $familys;

		$this->render_view();
	}

	public function histo(){
		//js for check all input
		$civil_year = $this->set_civil_years('histo');
		
		$this->LoadModel('Infos_model');
		$this->LoadModel('Admwork_model');
		
		$this->data_view['civil_year'] = $this->Units_model->_get('defs')['civil_year']->_get('values');
		$this->data_view['filter_ec'] = $civil_year;

		$this->bootstrap_tools->_SetHead('assets/js/checkall.js','js');

		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_histo');
		$this->data_view['units']['valid'] = [];
		$this->data_view['units']['coming'] = [];
		$this->data_view['units']['addition'] = [];
		$this->data_view['units']['raf'] = 20;
		$this->data_view['units']['tovalid'] = 0;

		
		if ($this->acl->getType()  == "sys"){ //vue admin
			$id_famille = $this->session->userdata( $this->set_ref_field('id_famille') );
			if ($this->input->post('id_fam')  !== NULL ){
				$id_famille = $this->input->post('id_fam');
				$this->session->set_userdata( $this->set_ref_field('id_famille') , $id_famille );
				$this->session->set_userdata( $this->set_ref_field('filter') , ['unites_id_famille'=>$id_famille]);
			} 
			$filter_ec = $this->session->userdata($this->set_ref_field('filter'));
			if (isset($filter_ec['unites_id_famille']))
				$id_famille = $filter_ec['unites_id_famille'];

			$this->data_view['familys'] = $this->Capacity_model->_get('defs')['id_fam'];
			
			$values = $this->data_view['familys']->_get('values');
			foreach($values AS $key=>$value){
				$values[$key] = UnicodeProcess($value);
			}
			$this->data_view['familys']->_set('values',$values);

			$this->data_view['familys']->_set('value', $id_famille );
			
		} else {
			$id_famille = $this->acl->getUserId();
		}

			
		if ($id_famille){
			$this->Units_model->_set('filter',['unites_id_famille'=>$id_famille, 'civil_year'=>$civil_year]);
			$this->Units_model->_set('order','id');

			$opt = new StdClass();
			$opt->context = 'valid';
			$opt->full = false;
			$opt->id_fam = $id_famille;
			$opt->civil_year = $civil_year;
			$this->Infos_model->_set('filter',['travaux.civil_year'=>$civil_year]);			
			$this->data_view['units']['valid'] = $this->Infos_model->GetUnits($opt);
			if ($this->data_view['units']['valid']){
				foreach($this->data_view['units']['valid'] AS $unit){
					$this->data_view['units']['raf'] -= $unit->nb_unites_valides_effectif;
				}
			}

			$opt->context = 'pending';
			$opt->full = false;
			$opt->id_fam = $id_famille;
			$opt->civil_year = $civil_year;			
			$this->Infos_model->_set('filter',['travaux.civil_year'=>$civil_year]);			
			$this->data_view['units']['coming'] = $this->Infos_model->GetUnits($opt);
			if ($this->data_view['units']['coming']){
				foreach($this->data_view['units']['coming'] AS $unit){
					//echo debug($unit);
					$this->data_view['units']['tovalid'] += $unit->nb_unites_valides;
				}
			}
			//$this->Units_model->_set('filter',['archived !='=>1]);
			$this->data_view['units']['addition'] = $this->Units_model->get_all();
			if ($this->data_view['units']['addition']){
				foreach($this->data_view['units']['addition'] AS $unit){
					$this->data_view['units']['raf'] -= $unit->unites_valides;
				}	
			}			
		}

		$this->render_view();
	}

	function _calc(){
		$this->{$this->_model_name}->_set('order','nom');
		$this->{$this->_model_name}->_set('direction','ASC');
		$datas	= $this->{$this->_model_name}->get_all();
		$civil_year = $this->set_civil_years('_calc');
		

		foreach($datas AS $key=>$famiy){

			$info = new stdClass();
			
			$info->family = $famiy;
			$info->valid= 0;
			$info->coming = 0;
			$info->addition = 0;
			$info->raf = $this->config->item('unit_todo');
			$info->tovalid = 0;

			$opt = new StdClass();
			$opt->context = 'valid';
			$opt->full = false;
			$opt->id_fam = $famiy->id;
			$opt->civil_year = $civil_year;		
			$this->Infos_model->_set('filter',['travaux.civil_year'=>$civil_year]);
			$valid = $this->Infos_model->GetUnits($opt);
			if ($valid){
				foreach($valid AS $unit){
					//echo debug($unit);
					if (!isset($stats[$unit->type])){
						$stats[$unit->type] = new stdclass();
						$stats[$unit->type]->tovalid = 0;
						$stats[$unit->type]->valid = 0;
					}
					$stats[$unit->type]->tovalid += $unit->nb_unites_valides_effectif;
					$stats[$unit->type]->valid += $unit->nb_unites_valides;


					$info->valid += $unit->nb_unites_valides_effectif;
					$info->raf -= $unit->nb_unites_valides_effectif;
				}
			}

			

			$opt->context = 'pending';
			$opt->full = false;
			$opt->id_fam = $famiy->id;
			$opt->civil_year = $civil_year;		
			$this->Infos_model->_set('filter',['travaux.civil_year'=>$civil_year]);
			$coming = $this->Infos_model->GetUnits($opt);
			if ($coming){
				foreach($coming AS $unit){
					//echo debug($unit);
					$info->coming += $unit->nb_unites_valides;
				}
			}

			$this->Units_model->_set('filter',['unites_id_famille'=>$famiy->id,'civil_year'=>$civil_year]);//'archived != '=>1
			$this->Units_model->_set('order','id');
			$addition = $this->Units_model->get_all();
			if ($addition){
				foreach($addition AS $unit){
					$info->addition += $unit->unites_valides;
					$info->raf -= $unit->unites_valides;
				}	
			}	
			//echo debug($info);
			$this->data_view['units'][$famiy->id] = $info;
		}
	}

	function GetConsolidatedStats(){
		$stats= [];
		$valid = $this->Infos_model->GetUnits();
		if ($valid){
			foreach($valid AS $unit){
				if ($unit->nb_unites_valides_effectif || $unit->nb_unites_valides){

					if (!$unit->type){
						$type = 'undef';
					} else {
						$type = $unit->type;
					}
					if (!$unit->civil_year){
						$civil_year = 'undef';
						//echo debug($unit);
					} else {
						$civil_year = $unit->civil_year;
					}

					if (!isset($stats[$type][$civil_year])){
						$stats[$type][$civil_year] = new stdclass();
						$stats[$type][$civil_year]->tovalid = 0;
						$stats[$type][$civil_year]->valid = 0;
					}

					$stats[$type][$civil_year]->tovalid += $unit->nb_unites_valides_effectif;
					$stats[$type][$civil_year]->valid += $unit->nb_unites_valides;
				}
			}
			$this->Units_model->_set('order','id');
			$this->Units_model->_set('filter', []);
			$addition = $this->Units_model->get_all();
			$sql = $this->Units_model->_get('_debug_array');
			//echo debug($sql);

			if ($addition){
				foreach($addition AS $unit){
					$type = 'sup';
					if (!$unit->civil_year){
						$civil_year = 'undef';
					} else {
						$civil_year = $unit->civil_year;
					}

					if (!isset($stats[$type][$civil_year])){
						$stats[$type][$civil_year] = new stdclass();
						$stats[$type][$civil_year]->tovalid = 0;
						$stats[$type][$civil_year]->valid = 0;
					}
					$stats[$type][$civil_year]->valid += $unit->unites_valides;
				}	
			}
			
		}
		$this->data_view['ConsolidatedStats'] = $stats;
	}

	function stats(){
		$this->data_view['civil_years'] = $this->Units_model->_get('defs')['civil_year']->_get('values');
		$this->data_view['filter_ec'] = $this->set_civil_years('stats');

		$this->_set('view_inprogress','unique/'.$this->_controller_name.'_stats');
		$this->_calc();

		if ($this->data_view['filter_ec'] == 'resume'){
			$this->GetConsolidatedStats();
		}
		
		$this->render_view();
	}

	function stats_export()
	{
		
		$this->_calc(); 
		$file_name = 'unites_famille_'.date('Ymd').'.csv'; 
		header("Content-Description: File Transfer"); 
		header("Content-Disposition: attachment; filename=$file_name"); 
		header("Content-Type: application/csv; charset=utf-8"); 
		$file = fopen('php://output', 'w');
		$header = array(
			$this->lang->line('_title_family'),
			$this->lang->line('_title_ecole'),
			$this->lang->line('_title_raf'),
			$this->lang->line('_title_tovalid'),
			$this->lang->line('_title_valid'),
			$this->lang->line('_title_addition')
		); 
		fputcsv($file, $header,";");
		foreach ($this->data_view['units'] as $key => $stats){ 
		  $vals = [$stats->family->nom,$stats->family->ecole,$stats->raf,$stats->tovalid,$stats->valid,$stats->addition];
		  fputcsv( $file,  $vals ,";"); 
		}
		fclose($file); 
		exit; 
	}

}
