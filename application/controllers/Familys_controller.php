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

		//$this->_set('view_inprogress','unique/'.$this->_controller_name.'_list');
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

	/**
	 * Étape 1 : upload du CSV ABCM, parsing, et preview du diff.
	 *
	 * GET  : affiche le formulaire d'upload.
	 * POST : reçoit le fichier, le valide, le stocke sous
	 *        public/files/imports/abcm_YYYYMMDD_HHmmss.csv,
	 *        parse, calcule le diff, affiche la preview.
	 *
	 * L'utilisateur doit ensuite cliquer "Confirmer" pour passer à
	 * import_apply().
	 */
	public function import()
	{
		$this->_set('view_inprogress', 'unique/'.$this->_controller_name.'_import');
		$this->data_view['error']      = null;
		$this->data_view['preview']    = null;
		$this->data_view['stored_path']= null;

		// GET : juste le formulaire d'upload
		if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
			$this->render_view();
			return;
		}

		// POST : on attend un fichier "csv_file"
		if (empty($_FILES['csv_file']) || empty($_FILES['csv_file']['tmp_name'])){
			$this->data_view['error'] = $this->lang->line('IMPORT_NO_FILE');
			$this->render_view();
			return;
		}

		$tmp_name = $_FILES['csv_file']['tmp_name'];
		$orig_name= $_FILES['csv_file']['name'];

		if ($_FILES['csv_file']['size'] === 0){
			$this->data_view['error'] = $this->lang->line('IMPORT_EMPTY_FILE');
			$this->render_view();
			return;
		}
		// Garde-fou simple sur l'extension (le mime CSV est très permissif)
		$ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
		if ($ext !== 'csv'){
			$this->data_view['error'] = $this->lang->line('IMPORT_BAD_EXTENSION');
			$this->render_view();
			return;
		}

		// Stockage du fichier sous public/files/imports/
		$import_dir = str_replace('application', 'public/files/imports', APPPATH);
		if (!is_dir($import_dir)){
			@mkdir($import_dir, 0755, true);
		}
		$stored_filename = 'abcm_'.date('Ymd_His').'.csv';
		$stored_full     = rtrim($import_dir, '/').'/'.$stored_filename;
		if (!@move_uploaded_file($tmp_name, $stored_full)){
			// Fallback : copy (cas des serveurs où move_uploaded_file échoue)
			if (!@copy($tmp_name, $stored_full)){
				$this->data_view['error'] = $this->lang->line('IMPORT_STORE_FAILED');
				$this->render_view();
				return;
			}
		}

		// Parsing + diff
		$parse_result = $this->_parse_csv_abcm($stored_full);
		if ($parse_result === false || empty($parse_result['families'])){
			$this->data_view['error'] = $this->lang->line('IMPORT_PARSE_FAILED');
			$this->render_view();
			return;
		}

		$diff = $this->_build_diff($parse_result);

		// Stocke le chemin relatif pour repasser à l'étape 2 (sans avoir
		// à re-uploader). Le stockage relatif évite d'exposer APPPATH.
		$rel_path = 'imports/'.$stored_filename;

		$this->data_view['preview']     = $diff;
		$this->data_view['parse_stats'] = $parse_result['stats'];
		$this->data_view['stored_path'] = $rel_path;
		$this->data_view['orig_name']   = $orig_name;

		$this->render_view();
	}

	/**
	 * Étape 2 : applique le diff confirmé.
	 *
	 * Reçoit en POST le stored_path (chemin relatif sous public/files/),
	 * re-parse le fichier (pour éviter toute manipulation côté client
	 * du diff), applique les changements en transaction, et logge dans
	 * family_import.
	 */
	public function import_apply()
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
			redirect($this->_controller_name.'/import');
			return;
		}

		$stored_path = $this->input->post('stored_path');
		if (empty($stored_path) || strpos($stored_path, '..') !== false){
			$this->session->set_flashdata('import_error', $this->lang->line('IMPORT_BAD_PATH'));
			redirect($this->_controller_name.'/import');
			return;
		}

		$base_dir = str_replace('application', 'public/files', APPPATH);
		$full_path = rtrim($base_dir, '/').'/'.$stored_path;
		if (!is_file($full_path)){
			$this->session->set_flashdata('import_error', $this->lang->line('IMPORT_FILE_GONE'));
			redirect($this->_controller_name.'/import');
			return;
		}

		// Re-parse pour la vérité — on ne fait PAS confiance au POST
		$parse_result = $this->_parse_csv_abcm($full_path);
		if ($parse_result === false || empty($parse_result['families'])){
			$this->session->set_flashdata('import_error', $this->lang->line('IMPORT_PARSE_FAILED'));
			redirect($this->_controller_name.'/import');
			return;
		}

		$diff   = $this->_build_diff($parse_result);
		$report = $this->_apply_diff($diff);

		// Log
		$civil_year = $this->config->item('civil_year');
		$user_id    = method_exists($this->acl, 'getUserId') ? (int) $this->acl->getUserId() : null;

		$now = date('Y-m-d H:i:s');
		$this->db->insert('family_import', [
			'filename'      => basename($stored_path),
			'stored_path'   => $stored_path,
			'civil_year'    => $civil_year,
			'nb_lines'      => $parse_result['stats']['nb_lines'],
			'nb_families'   => $parse_result['stats']['nb_families'],
			'nb_created'    => $report['nb_created'],
			'nb_updated'    => $report['nb_updated'],
			'nb_marked'     => $report['nb_marked'],
			'nb_reactivated'=> $report['nb_reactivated'],
			'nb_errors'     => $report['nb_errors'],
			'report'        => json_encode($report['details'], JSON_UNESCAPED_UNICODE),
			'imported_by'   => $user_id,
			'created'       => $now,
			'updated'       => $now,
		]);

		$this->session->set_flashdata('import_success', sprintf(
			$this->lang->line('IMPORT_APPLIED_X'),
			$report['nb_created'], $report['nb_updated'],
			$report['nb_marked'], $report['nb_reactivated']
		));

		redirect($this->_controller_name.'/import_history');
	}

	/**
	 * Historique des imports CSV ABCM.
	 */
	public function import_history()
	{
		$this->_set('view_inprogress', 'unique/'.$this->_controller_name.'_import_history');
		$this->load->model('FamilyImport_model');
		$this->data_view['imports'] = $this->FamilyImport_model->GetLastImports(50);
		$this->render_view();
	}


	// =================================================================
	// === BLOC 3 : MÉTHODES PRIVÉES (parsing / diff / apply) =========
	// =================================================================

	/**
	 * -------------------------------------------------------------
	 * Index 0-based des colonnes du CSV ABCM exploitées
	 * (le format est figé par l'export ABCM, 69 colonnes en tout)
	 * -------------------------------------------------------------
	 *   [0]  CODE FAMILLE
	 *   [1]  CODE MEMBRE
	 *   [2]  NOM D'USAGE DE L'ENFANT
	 *   [3]  PRENOM (enfant)
	 *   [9]  CLASSE
	 *   [10] ECOLE   (MUL / LUT → mappé en M / L)
	 *   [28] NOM D'USAGE R1
	 *   [29] PRENOM R1
	 *   [31] ADRESSE R1
	 *   [32] CODE POSTAL R1
	 *   [33] VILLE R1
	 *   [38] MAIL R1   (clé de fallback si pas de code_famille_abcm)
	 *   [56] MAIL R2   (→ e_mail_comp)
	 * Toutes les autres colonnes sont volontairement ignorées
	 * (téléphones, RGPD-sensibles, autorisations image, banque...).
	 */

	/**
	 * Parse le CSV ABCM (encodé ISO-8859-1, séparateur ';', lignes
	 * terminées CRLF) et regroupe les lignes par CODE FAMILLE.
	 *
	 * @param  string $full_path  chemin absolu du fichier
	 * @return array|false
	 *         [
	 *             'families' => [
	 *                 'AABFEJ' => (object) [
	 *                     'code'     => 'AABFEJ',
	 *                     'nom'      => 'BURGELIN',
	 *                     'prenom'   => 'Céline, Julie',
	 *                     'e_mail'   => 'julie.burgelin@gmail.com',
	 *                     'e_mail_comp' => 'nicolas.laresser@gmail.com',
	 *                     'adresse'  => "42 rue d'Ensisheim",
	 *                     'cp'       => '68110',
	 *                     'ville'    => 'ILLZACH',
	 *                     'ecole'    => 'M',
	 *                     'members'  => [
	 *                         (object) [
	 *                             'code'   => 'AABFEJMARGOT-LEILA',
	 *                             'nom'    => 'LARESSER BURGELIN',
	 *                             'prenom' => 'Margot Leïla',
	 *                             'classe' => '2025 2026 MUL CL07',
	 *                         ],
	 *                         ...
	 *                     ],
	 *                 ],
	 *                 ...
	 *             ],
	 *             'errors' => [...],
	 *             'stats'  => ['nb_lines' => 1, 'nb_families' => 1],
	 *         ]
	 */
	private function _parse_csv_abcm($full_path)
	{
		$fh = @fopen($full_path, 'r');
		if (!$fh) return false;

		$families = [];
		$errors   = [];
		$nb_lines = 0;
		$line_no  = 0;

		// Note sur l'encodage : on lit ligne par ligne en latin1->utf8
		// (ISO-8859-1 / Windows-1252 indifféremment, les exports ABCM
		// sont en CP1252 mais iconv en latin1 reste tolérant).
		while (($raw = fgets($fh)) !== false){
			$line_no++;
			if ($line_no === 1) continue; // header
			$raw = trim($raw, "\r\n");
			if ($raw === '') continue;

			// Conversion encodage
			$line = @iconv('CP1252', 'UTF-8//TRANSLIT', $raw);
			if ($line === false){
				$line = @iconv('ISO-8859-1', 'UTF-8//TRANSLIT', $raw);
			}
			if ($line === false){
				$errors[] = ['line' => $line_no, 'reason' => 'encoding'];
				continue;
			}

			// CSV ABCM : séparateur ';', échappement '\' (cf. "d\'Ensisheim").
			// str_getcsv gère ; comme délim ; le 4e arg fixe l'escape sur '\'.
			$row = str_getcsv($line, ';', '"', '\\');
			if (count($row) < 57){
				$errors[] = ['line' => $line_no, 'reason' => 'too_few_cols', 'count' => count($row)];
				continue;
			}

			$code_fam = trim($row[0]);
			if ($code_fam === ''){
				$errors[] = ['line' => $line_no, 'reason' => 'no_code_famille'];
				continue;
			}

			// Normalisation des champs
			$mail_r1 = $this->_norm_email($row[38]);
			if ($mail_r1 === ''){
				$errors[] = ['line' => $line_no, 'reason' => 'no_mail_r1', 'code' => $code_fam];
				continue;
			}

			$nb_lines++;

			// Famille : on prend la première ligne qui apparaît pour ce
			// CODE FAMILLE, on ignore les suivantes (les frères/sœurs
			// portent les mêmes infos parents).
			if (!isset($families[$code_fam])){
				$ecole_csv = trim($row[10]);
				$ecole_app = $this->_map_ecole($ecole_csv);

				$fam = new stdClass();
				$fam->code        = $code_fam;
				$fam->nom         = $this->_clean(trim($row[28]));
				$fam->prenom      = $this->_clean(trim($row[29]));
				$fam->e_mail      = $mail_r1;
				$fam->e_mail_comp = $this->_norm_email($row[56]);
				$fam->adresse     = $this->_clean(trim($row[31]));
				$fam->cp          = trim($row[32]);
				$fam->ville       = $this->_clean(trim($row[33]));
				$fam->ecole       = $ecole_app;
				$fam->_ecole_set  = [$ecole_csv]; // pour tracker fratrie split
				$fam->members     = [];
				$families[$code_fam] = $fam;
			} else {
				// Fratrie : tracker l'école pour passer à 'B' si split
				$ecole_csv = trim($row[10]);
				if (!in_array($ecole_csv, $families[$code_fam]->_ecole_set, true)){
					$families[$code_fam]->_ecole_set[] = $ecole_csv;
				}
				if (count($families[$code_fam]->_ecole_set) > 1){
					$families[$code_fam]->ecole = 'B';
				}
			}

			// Member (enfant)
			$mb = new stdClass();
			$mb->code   = trim($row[1]);
			$mb->nom    = $this->_clean(trim($row[2]));
			$mb->prenom = $this->_clean(trim($row[3]));
			$mb->classe = $this->_clean(trim($row[9]));
			$families[$code_fam]->members[] = $mb;
		}
		fclose($fh);

		// Nettoyage de la prop. interne _ecole_set
		foreach($families AS $code => $fam){
			unset($families[$code]->_ecole_set);
		}

		return [
			'families' => $families,
			'errors'   => $errors,
			'stats'    => [
				'nb_lines'    => $nb_lines,
				'nb_families' => count($families),
			],
		];
	}

	/**
	 * Construit le diff entre les familles parsées et la base existante.
	 *
	 * @param  array $parse_result  retour de _parse_csv_abcm()
	 * @return array
	 *         [
	 *             'to_create'     => [ (object) {csv: ..., reason: ...}, ... ],
	 *             'to_update'     => [ (object) {csv, existing, fields_diff: [field => [old, new]], members_diff}, ... ],
	 *             'to_mark'       => [ (object) {existing}, ... ],
	 *             'to_reactivate' => [ (object) {csv, existing}, ... ],
	 *             'errors'        => [...],
	 *         ]
	 */
	private function _build_diff($parse_result)
	{
		$diff = [
			'to_create'     => [],
			'to_update'     => [],
			'to_mark'       => [],
			'to_reactivate' => [],
			'errors'        => $parse_result['errors'],
		];

		// 1. Charger toutes les familles existantes en index par code ABCM + par mail
		$all = $this->db->select('id, code_famille_abcm, e_mail, nom, prenom, adresse, cp, ville, ecole, e_mail_comp, to_deactivate, civil_year')
			->from('famille')
			->get()->result();

		$by_code = [];
		$by_mail = [];
		foreach($all AS $f){
			if (!empty($f->code_famille_abcm)){
				$by_code[strtoupper($f->code_famille_abcm)] = $f;
			}
			if (!empty($f->e_mail)){
				$by_mail[strtolower($f->e_mail)] = $f;
			}
		}

		// 2. Pour chaque famille du CSV, chercher en base
		$matched_ids = [];
		foreach($parse_result['families'] AS $code => $csv_fam){
			$existing = null;

			// Cascade : 1) code ABCM
			if (isset($by_code[strtoupper($code)])){
				$existing = $by_code[strtoupper($code)];
			}
			// 2) mail R1
			elseif (isset($by_mail[strtolower($csv_fam->e_mail)])){
				$existing = $by_mail[strtolower($csv_fam->e_mail)];
			}

			if ($existing === null){
				$diff['to_create'][] = (object) [
					'csv'    => $csv_fam,
					'reason' => 'no_match',
				];
				continue;
			}

			$matched_ids[(int)$existing->id] = true;

			// Calcul des champs modifiés
			$fields_diff = [];
			$mappings = [
				'code_famille_abcm' => $csv_fam->code,
				'nom'               => $csv_fam->nom,
				'prenom'            => $csv_fam->prenom,
				'e_mail'            => $csv_fam->e_mail,
				'e_mail_comp'       => $csv_fam->e_mail_comp,
				'adresse'           => $csv_fam->adresse,
				'cp'                => $csv_fam->cp,
				'ville'             => $csv_fam->ville,
				'ecole'             => $csv_fam->ecole,
			];
			foreach($mappings AS $field => $new){
				$old = isset($existing->$field) ? (string) $existing->$field : '';
				if ((string) $new !== $old){
					$fields_diff[$field] = ['old' => $old, 'new' => (string) $new];
				}
			}

			// Diff sur members : on regarde si il y a des enfants à créer / màj
			$members_diff = $this->_diff_members((int)$existing->id, $csv_fam->members);

			$is_reactivation = ((int) $existing->to_deactivate === 1);

			$entry = (object) [
				'csv'          => $csv_fam,
				'existing'     => $existing,
				'fields_diff'  => $fields_diff,
				'members_diff' => $members_diff,
			];

			if ($is_reactivation){
				$diff['to_reactivate'][] = $entry;
			} else if (!empty($fields_diff) || $members_diff['has_changes']){
				$diff['to_update'][] = $entry;
			}
			// Si rien ne change ET pas de réactivation, on ne l'ajoute à rien
			// (aucune action à mener) — on ne pollue pas la preview.
		}

		// 3. Familles en base avec un code ABCM mais absentes du CSV → to_mark
		//    (on ne touche pas aux familles SANS code ABCM : ce sont les
		//     anciennes pas encore matchées, on attend qu'un import les
		//     rattache par mail avant de les considérer comme "ABCM-tracked")
		foreach($all AS $f){
			if (empty($f->code_famille_abcm)) continue;
			if (isset($matched_ids[(int)$f->id])) continue;
			if ((int) $f->to_deactivate === 1) continue; // déjà marquée
			$diff['to_mark'][] = (object) ['existing' => $f];
		}

		return $diff;
	}

	/**
	 * Diff des membres pour une famille donnée (par id_fam).
	 * Matching cascade :
	 *   1. par code_membre_abcm
	 *   2. par (LOWER(nom), LOWER(prenom))
	 *
	 * @return array ['to_create' => [...], 'to_update' => [...], 'has_changes' => bool]
	 */
	private function _diff_members($id_fam, $csv_members)
	{
		$existing = $this->db->select('id, code_membre_abcm, nom, prenom, classe, type')
			->from('members')
			->where('id_fam', $id_fam)
			->get()->result();

		$by_code = [];
		$by_namepair = [];
		foreach($existing AS $m){
			if (!empty($m->code_membre_abcm)){
				$by_code[strtoupper($m->code_membre_abcm)] = $m;
			}
			$key = strtolower(trim($m->nom)).'|'.strtolower(trim($m->prenom));
			$by_namepair[$key] = $m;
		}

		$result = ['to_create' => [], 'to_update' => [], 'has_changes' => false];

		foreach($csv_members AS $cm){
			$match = null;
			if ($cm->code !== '' && isset($by_code[strtoupper($cm->code)])){
				$match = $by_code[strtoupper($cm->code)];
			} else {
				$key = strtolower($cm->nom).'|'.strtolower($cm->prenom);
				if (isset($by_namepair[$key])){
					$match = $by_namepair[$key];
				}
			}

			if ($match === null){
				$result['to_create'][] = $cm;
				$result['has_changes'] = true;
				continue;
			}

			$mb_diff = [];
			if ((string)$match->code_membre_abcm !== (string)$cm->code) $mb_diff['code_membre_abcm'] = [(string)$match->code_membre_abcm, $cm->code];
			if ((string)$match->nom              !== (string)$cm->nom)    $mb_diff['nom']    = [(string)$match->nom, $cm->nom];
			if ((string)$match->prenom           !== (string)$cm->prenom) $mb_diff['prenom'] = [(string)$match->prenom, $cm->prenom];
			if ((string)$match->classe           !== (string)$cm->classe) $mb_diff['classe'] = [(string)$match->classe, $cm->classe];

			if (!empty($mb_diff)){
				$result['to_update'][] = (object) ['existing' => $match, 'csv' => $cm, 'fields_diff' => $mb_diff];
				$result['has_changes'] = true;
			}
		}

		return $result;
	}

	/**
	 * Applique le diff en base. Transactionnel.
	 *
	 * @param  array $diff  retour de _build_diff()
	 * @return array  ['nb_created', 'nb_updated', 'nb_marked', 'nb_reactivated', 'nb_errors', 'details']
	 */
	private function _apply_diff($diff)
	{
		$report = [
			'nb_created'    => 0,
			'nb_updated'    => 0,
			'nb_marked'     => 0,
			'nb_reactivated'=> 0,
			'nb_errors'     => count($diff['errors']),
			'details'       => [
				'created_ids'     => [],
				'updated_ids'     => [],
				'marked_ids'      => [],
				'reactivated_ids' => [],
				'parse_errors'    => $diff['errors'],
			],
		];

		$civil_year = $this->config->item('civil_year');
		$now = date('Y-m-d H:i:s');

		$this->db->trans_start();

		// 1. CRÉATIONS
		foreach($diff['to_create'] AS $entry){
			$csv = $entry->csv;
			$row = [
				'code_famille_abcm' => $csv->code,
				'nom'               => $csv->nom,
				'prenom'            => $csv->prenom,
				'login'             => $csv->e_mail, // login = mail par convention existante
				'e_mail'            => $csv->e_mail,
				'e_mail_comp'       => $csv->e_mail_comp,
				'adresse'           => $csv->adresse,
				'cp'                => $csv->cp,
				'ville'             => $csv->ville,
				'ecole'             => $csv->ecole,
				'civil_year'        => $civil_year,
				'to_deactivate'     => 0,
				'role_id'           => $this->config->item('role_famille') ?: 2,
				'nb_enfants'        => count($csv->members),
				'created'           => $now,
				'updated'           => $now,
			];
			$this->db->insert('famille', $row);
			$id_fam = (int) $this->db->insert_id();
			if ($id_fam > 0){
				foreach($csv->members AS $cm){
					$this->db->insert('members', [
						'id_fam'           => $id_fam,
						'code_membre_abcm' => $cm->code,
						'type'             => 'E',
						'nom'              => $cm->nom,
						'prenom'           => $cm->prenom,
						'classe'           => $cm->classe,
						'created'          => $now,
						'updated'          => $now,
					]);
				}
				$report['nb_created']++;
				$report['details']['created_ids'][] = $id_fam;
			}
		}

		// 2. MISES À JOUR (champs famille + membres)
		$updates = array_merge($diff['to_update'], $diff['to_reactivate']);
		foreach($updates AS $entry){
			$id_fam = (int) $entry->existing->id;
			$set = [];
			foreach($entry->fields_diff AS $field => $vv){
				$set[$field] = $vv['new'];
			}
			// Pour la réactivation : on remet to_deactivate = 0
			$set['to_deactivate'] = 0;
			$set['updated']       = $now;
			$this->db->where('id', $id_fam)->update('famille', $set);

			// Sync members
			$this->_apply_members_diff($id_fam, $entry->members_diff, $now);

			// Mise à jour du nb_enfants (cohérent avec les enfants du CSV)
			if (isset($entry->csv->members)){
				$this->db->where('id', $id_fam)->update('famille', [
					'nb_enfants' => count($entry->csv->members),
				]);
			}

			if ((int) $entry->existing->to_deactivate === 1){
				$report['nb_reactivated']++;
				$report['details']['reactivated_ids'][] = $id_fam;
			} else {
				$report['nb_updated']++;
				$report['details']['updated_ids'][] = $id_fam;
			}
		}

		// 3. À MARQUER
		foreach($diff['to_mark'] AS $entry){
			$id_fam = (int) $entry->existing->id;
			$this->db->where('id', $id_fam)->update('famille', [
				'to_deactivate' => 1,
				'updated'       => $now,
			]);
			$report['nb_marked']++;
			$report['details']['marked_ids'][] = $id_fam;
		}

		$this->db->trans_complete();
		return $report;
	}

	/**
	 * Applique le diff des membres pour une famille (créations + maj).
	 */
	private function _apply_members_diff($id_fam, $members_diff, $now)
	{
		foreach($members_diff['to_create'] AS $cm){
			$this->db->insert('members', [
				'id_fam'           => $id_fam,
				'code_membre_abcm' => $cm->code,
				'type'             => 'E',
				'nom'              => $cm->nom,
				'prenom'           => $cm->prenom,
				'classe'           => $cm->classe,
				'created'          => $now,
				'updated'          => $now,
			]);
		}
		foreach($members_diff['to_update'] AS $entry){
			$set = [];
			foreach($entry->fields_diff AS $field => $vv){
				$set[$field] = $vv[1]; // [old, new]
			}
			$set['updated'] = $now;
			$this->db->where('id', (int) $entry->existing->id)->update('members', $set);
		}
	}

	// -----------------------------------------------------------------
	// Helpers de normalisation
	// -----------------------------------------------------------------

	private function _norm_email($v){
		$v = strtolower(trim((string) $v));
		$v = str_replace(["\r","\n"], '', $v);
		return $v;
	}

	private function _clean($v){
		$v = (string) $v;
		// Le CSV ABCM échappe les apostrophes en \' — str_getcsv en gère
		// la plupart, mais on normalise par sécurité.
		$v = str_replace(["\\'", "\\\""], ["'", '"'], $v);
		$v = trim($v);
		return $v;
	}

	private function _map_ecole($csv_value){
		$v = strtoupper(trim((string) $csv_value));
		switch($v){
			case 'MUL': return 'M';
			case 'LUT': return 'L';
			case 'M': case 'L': case 'B': return $v;
			default: return ''; // valeur inconnue, l'admin verra dans la preview
		}
	}

}
