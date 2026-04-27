<?php
defined('BASEPATH') || exit('No direct script access allowed');

//require_once(APPPATH.'libraries/Bootstrap_tools.php');

require_once(APPPATH.'libraries/Autoload.php');

/**
 * MY_Controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
class MY_Controller extends CI_Controller {
	
	/* VARS*/
	protected $_autorised_get_key 	= array('order','direction','filter','page','repertoire','search','id','per_page');

	protected $_redirect			= true; //redirect page after POST
	protected $_model_name			= FALSE; 
	protected $_debug_array  		= array();
	protected $_debug 				= FALSE;
	/* used for right */
	protected $_controller_name 	= null; 
	protected $_action			 	= null;
	protected $_rules				= null;
	protected $_autorize			= array();
	protected $_search  			= false;
	protected $_dba_data				= null;
		
	protected $_edit_view = '';
	protected $_list_view = '';
	protected $_bg_color = '';
	protected $view_inprogress 		= null;
	protected $data_view 			= array();
	protected $title 				= '';
	protected $json = null;
	protected $json_path = APPPATH.'models/json/';
	protected $per_page	= 15;//pagination
	protected $next_view = 'list';
	protected $render_view = true; //render view @ end of process or not ? used for decoration.
	protected $_api  = FALSE; //trois mode possible, HTML par defaut, CLI dépendant de PHP_SAPI et API dépendant de cette variable.

	/* Each CI STD Object for PHP 8*/
	public $lang = null;
	public $config = null;
	public $render_object = null;
	public $bootstrap_tools = null;
	public $form_validation = null;
	public $acl = null;
	public $input = null;
	public $output = null;
	public $render_menu = null;
	public $session = null;
	public $uri = null;
	public $router = null;
	public $pagination = null;
	
	/**
	 * @brief Generic Constructor
	 * @returns  void()
	 * 
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('tools');
		$this->load->library('Render_object');

		$this->bootstrap_tools = Bootstrap_tools::get_instance(); //need to change $this->load method for singleton loading

		$this->load->library('acl');
		$this->load->library('Render_menu');

		$this->lang->load('traduction');
		
		$this->config->load('app');
		$this->config->load('secured');
	}
	
	public function SaveToJson($name, $data){
		$txt = '{"'.str_replace(['_data','.json'] ,['',''] ,$name).'":'.json_encode($data).'}';
		file_put_contents($this->json_path.$name, $txt);
	}
	
	public function Jsondata($field,$model_from_url = null){
		$json = '[';
		$model = $this->_model_name;
		//autre model utilisé pour l'objet
		if ($pos = strpos($field,'_')){
			$model2 = substr($field,$pos+1);
			if (isset($this->$model2)) {
				$model = substr($field,$pos+1);
				$field = substr($field,0,$pos);	
			}
		}
		if ($model_from_url){
			$model = $model_from_url;
		}
		
		$def = $this->{$model}->_get('defs')[$field];
		if ( method_exists( $def ,'JsonData') &&  $def->_get('query') ){//new methode for set datas
			$json .= $def->Jsondata();
		} else {
			$tmp = '';
			if (count($def->_get('values')))
			foreach($def->_get('values') AS $key => $value){
				$tmp .= '{ "id":"'.$key.'", "label":"'.$value.'"},';
			}
			$json .=  substr($tmp,0,-1);
		}
		//echo debug($datas->values);
		echo $json.']';
	}	
	
	/**
	 * @brief Load Json 
	 * @param $json 
	 * @param $model 
	 * @param $path 
	 * @returns 
	 * 
	 * 
	 */
	public function LoadJsonData($json,$model,$path){
		$this->load->model($model);
		$json = file_get_contents($this->json_path.$json);
		$json = json_decode($json);
		foreach($json->{$path} AS $element){
			echo '<pre>'.print_r($element, true).'</pre>';

			//$this->{$model}->post($family);
		}
	}	
	
	/**
	 * @brief Controller initialisation
	 * @returns 
	 * 
	 * 
	 */
	function init(){
		$this->_process_url();
		//echo debug( $this->uri->segment_array());

		$this->data_view['app_name'] 	= $this->config->item('app_name'); 
		$this->data_view['slogan'] 		= $this->config->item('slogan'); 
		$this->data_view['title'] 		= $this->title;
		
		$this->data_view['raw_url']		= $this->_controller_name.'/'.$this->_action;

		$this->data_view['footer_line'] = '';
		switch($this->config->item('debug_app')){
			case 'debug':
				$this->_set('_debug', TRUE);
			break;
			case 'profiler':
				$this->output->enable_profiler(TRUE);
			break;
		}

		$option = [
			'filter'=>$this->session->userdata($this->set_ref_field('filter')),
			'direction'=>$this->session->userdata($this->set_ref_field('direction')),
			'config'=>$this->config
		];

		$this->render_object->SetOption($option);


		if ($this->_model_name){
			$this->LoadModel($this->_model_name);
			$this->render_object->_set('datamodel', $this->_model_name);
			$this->bootstrap_tools->_set('_controller_name', $this->_controller_name);
			/* TODO */
			$this->data_view['_model_name'] = $this->_model_name;// Need ?
		}

		/* FIX FILED NOT IN TABLE IN LIST => todo : filter by page */
		$filter = $this->session->userdata($this->set_ref_field('filter'));
		$autorized_fields = $this->{$this->_model_name}->_get('autorized_fields');
		$autorized_fields[] = 'civil_year'; //cas particulier
		if (is_array($filter) && count($filter)){
			foreach($filter AS $key=>$value){
				if (!in_array($key,$autorized_fields))
					unset($filter[$key]);
			}	
			$this->session->set_userdata( $this->set_ref_field('filter') , $filter);
		}	

		//Create CRUD URL		
		foreach($this->_autorize AS $key=>$value){
			$this->_set_ui_rules($key , $value);
		}
		$this->render_menu->init();
		//to permit use it in view.
		$this->render_object->_set('_ui_rules' , $this->_rules);
		$this->_debug($this->_rules, __FUNCTION__, '_ui_rules', __FILE__,181);

		$search_object 					= new StdClass();
		$search_object->url 			= $this->router->class.'/'.$this->router->method;
		$search_object->global_search 	= $this->session->userdata($this->set_ref_field('global_search'));
		$search_object->autorize 		= FALSE;
		$this->data_view['search_object'] = $search_object;
	}
		
	/**
	 * Method LoadModel
	 *
	 * @param $model $model [explicite description]
	 *
	 * @return void
	 */
	public function LoadModel($model){
		//echo debug($model);
		$this->load->model($model);
		$this->{$model}->_set('_debug', $this->_debug);
		$this->{$model}->_init_def(); //here for option event

		$this->_debug($model, __FUNCTION__, 'init', __FILE__,203);
		
		$config = $this->render_object->Set_Rules_elements($model, $this->{$model}); //loading Infos_model ELements	
		$this->form_validation->_SetRules($config,$model);
	}
	
	
	/**
	 * @brief 		Render View in Template
	 * @param       $this->view_inprogress
	 * @param		$this->data_view
	 * @return      void()
	 * 
	 * 
	 */
	function render_view(){
		if ($this->input->is_ajax_request()){
			$this->load->view($this->view_inprogress,	$this->data_view);
		} else {
			$this->load->view('template/head',			$this->data_view);
			$this->load->view($this->view_inprogress,	$this->data_view);
			$this->load->view('template/footer',		$this->data_view);	
		}
	}

	/**
	 * @brief Attach variable to controller name
	 * @param $name 
	 * @returns 
	 * 
	 * 
	 */
	public function set_ref_field($name){
		//echo "<p>".$name.'_'.$this->_controller_name."</p>";
		return $name.'_'.$this->_controller_name;
	}
	
	/**
	 * @brief Generic list view ( Need PHP 7)
	 * @returns 
	 * 
	 * 
	 */
	public function list()
	{
		if ($this->_search)
			$this->data_view['search_object']->autorize = true;

		// Lecture du per_page courant : session > défaut contrôleur
		$session_pp = (int) $this->session->userdata($this->set_ref_field('per_page'));
		$effective_pp = $session_pp > 0 ? $session_pp : $this->per_page;

		$this->{$this->_model_name}->_set('global_search'	, $this->session->userdata($this->set_ref_field('global_search')));
		$this->{$this->_model_name}->_set('order'			, $this->session->userdata($this->set_ref_field('order')));
		$this->{$this->_model_name}->_set('filter'			, $this->session->userdata($this->set_ref_field('filter')));
		$this->{$this->_model_name}->_set('direction'		, $this->session->userdata($this->set_ref_field('direction')));
		$this->{$this->_model_name}->_set('per_page'		, $effective_pp);
		$this->{$this->_model_name}->_set('page'			, $this->session->userdata($this->set_ref_field('page')));

		$config = array();
		$config['use_page_numbers'] = TRUE;
		$config['per_page'] 	= $effective_pp;
		$config['cur_page'] 	= (($this->{$this->_model_name}->_get('page')) ? $this->{$this->_model_name}->_get('page'):1);
		$config['base_url'] 	= $this->config->item('base_url').$this->_controller_name.'/list/page/';
		$config['total_rows'] 	= $this->{$this->_model_name}->get_pagination();

		if ($config['per_page'] > $config['total_rows'] ){
			$config['cur_page'] 	=  1;
			$this->{$this->_model_name}->_set('page', 1 );
		}
		$this->pagination->initialize($config);

		// GET DATAS
		$this->data_view['fields']      = $this->{$this->_model_name}->_get('autorized_fields');
		$this->data_view['datas']       = $this->{$this->_model_name}->get();

		// Exposition pour la vue : compteur + sélecteur per_page + filtres actifs
		$this->data_view['total_rows']     = (int) $config['total_rows'];
		$this->data_view['per_page']       = $effective_pp;
		$this->data_view['per_page_options'] = array(15, 30, 50, 100);
		$this->data_view['cur_page']       = (int) $config['cur_page'];
		$this->data_view['active_filters'] = $this->session->userdata($this->set_ref_field('filter')) ?: array();
		$this->data_view['global_search']  = $this->session->userdata($this->set_ref_field('global_search'));

		$this->_set('view_inprogress','unique/list_view');
		if ($this->render_view)
			$this->render_view();
	}	
	
	/**
	 * @brief Réinitialise les filtres de colonnes, la recherche globale
	 *        et la page courante pour la liste du contrôleur appelant.
	 * @returns void
	 */
	public function clear_filters()
	{
		$this->session->set_userdata( $this->set_ref_field('filter')        , array() );
		$this->session->set_userdata( $this->set_ref_field('global_search') , ''      );
		$this->session->set_userdata( $this->set_ref_field('page')          , 1       );
		redirect($this->_controller_name . '/list');
	}

	/**
	 * @brief Genric View Method
	 * @param $id 
	 * @returns 
	 * 
	 * 
	 */
	public function view($id){
		if ($id){
			$this->render_object->_set('id',		$id);
			$this->{$this->_model_name}->_set('key_value',$id);
			$this->_dba_data = $this->{$this->_model_name}->get_one();
			$this->render_object->_set('dba_data',$this->_dba_data);
		}	
		$this->_set('view_inprogress',$this->_list_view);
		if ($this->render_view)
			$this->render_view();	
		
	}	
	
	/**
	 * @brief DELETE Method 
	 * @param $id 
	 * @returns 
	 * 
	 * 
	 */
	public function delete($id = 0){
		if ($id){

			$fields = $this->{$this->_model_name}->_get('defs');
			foreach($fields AS $field){
				if (!in_array($field->_get('name'),['id','created','updated'])){
					$child_model = $field->_get('model');
					if($child_model != ''){
						//effacement des elements lié (TODO : utilisation de clé de contrainte dans la base de donnée)
						if (method_exists($this->{$child_model},'DeleteLink'))
							$this->{$child_model}->DeleteLink($field->_get('foreignkey'), $id);
					}
				}
			}
			$this->{$this->_model_name}->_set('key_value',$id);
			$this->{$this->_model_name}->delete();
		}
		redirect($this->_get('_rules')[$this->next_view]->url);
	}
	
	/**
	 * @brief ADD Method
	 * @returns 
	 * 
	 * 
	 */
	public function add(){
		$this->render_object->_set('form_mod', 'add');
		$this->edit();
	}
	
	/**
	 * @brief Edition Method
	 * @param $id 
	 * @returns 
	 * 
	 * 
	 */
	public function edit($id = 0)
	{		
		$this->data_view['id'] = '';
		if (!$id){
			if ($this->input->post('id') ){
				$id = $this->input->post('id');
			}
		}
		if ($id){
			$this->render_object->_set('id',		$id);
			$this->{$this->_model_name}->_set('key_value',$id);
			$dba_data = $this->{$this->_model_name}->get_one();
			$this->render_object->_set('dba_data',$dba_data);
			$this->render_object->_set('form_mod', 'edit');
			$this->data_view['id'] = $id;
		}		
		
		//$this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim|required|matches[password]');
		if ($this->input->post('form_mod')){
			if ($this->form_validation->run($this->_model_name) === FALSE){
				$this->_debug(validation_errors(),'edit','form_validation',__FILE__,__LINE__);
			} else {
				$datas = $this->_ProcessPost($this->_model_name);
				if ($this->_redirect){
					redirect($this->_get('_rules')[$this->next_view]->url);
				}
			}
		}
		
		$this->data_view['required_field'] = $this->{$this->_model_name}->_get('required');

		$this->_set('view_inprogress',$this->_edit_view);
		$this->render_view();
	}

	/**
	 * @brief Router Default 
	 * @returns 
	 * 
	 * 
	 */
	public function index(){
		redirect($this->_get('_rules')['list']->url);
	}

	/**
	 * Method _debug : Set Debug Array
	 *
	 * @param $message $message [explicite description]
	 * @param $from $from [explicite description]
	 * @param $type $type [explicite description]
	 * @param $file $file [explicite description]
	 * @param $line $line [explicite description]
	 *
	 * @return void
	 */
	function _debug($message , $from = null , $type = null, $file = null, $line = null){
		$msg = new Stdclass();
		$msg->message = $message;
		$msg->from = $from;
		$msg->type = $type;
		$msg->file = $file;
		$msg->line = $line;
		
		$this->_debug_array[] = $msg;
	}
 		
	/**
	 * Method _process_url : Processing variable on url
	 *
	 * @return void
	 */
	private function _process_url(){

		

		if ($this->input->post('global_search')){
			$this->session->set_userdata( $this->set_ref_field('global_search') ,$this->input->post('global_search'));
		}
		$this->_action = $this->uri->segment(2, 0);

		/* FIX FILED NOT IN TABLE IN LIST */
		$filter = $this->session->userdata($this->set_ref_field('filter'));
		if (isset($filter['filter']))
			unset($filter['filter']);
		$this->session->set_userdata( $this->set_ref_field('filter') , $filter);

		$array = $this->uri->uri_to_assoc(3);

		foreach($array AS $field=>$value){
			if (in_array($field,$this->_autorised_get_key)){
				switch($field){
					case 'search':
						$this->session->set_userdata( $this->set_ref_field('global_search') ,'');
					break;
					case 'filter':
						$filtered = $this->session->userdata( $this->set_ref_field('filter') );
						if ($array['filter_value'] == 'all'){
							unset($filtered[$value]);
						} else {
							$filtered[$value] = $array['filter_value'];
						}
						$this->session->set_userdata( $this->set_ref_field('filter') , $filtered);
					break;
					case 'per_page':
						// Liste blanche pour éviter qu'un utilisateur stocke n'importe quoi en session
						$allowed_pp = array(15, 30, 50, 100);
						$pp = (int) $value;
						if (in_array($pp, $allowed_pp, true)) {
							$this->session->set_userdata(
								$this->set_ref_field('per_page'),
								$pp
							);
							// Si on change le per_page, repartir page 1 pour ne pas tomber hors plage
							$this->session->set_userdata(
								$this->set_ref_field('page'),
								1
							);
						}
					break;
					default:
						$this->session->set_userdata( $this->set_ref_field($field) , $value );
					break;
				}
			}
		}
	}

	/**
	 * Method _ProcessPost
	 *
	 * @param $model_name $model_name [explicite description]
	 * @param $override_fields $override_fields [explicite description]
	 *
	 * @return void
	 */
	function _ProcessPost($model_name, $override_fields = null){
		$datas = array();
		if ($override_fields){
			$fields =  $override_fields;
		} else{
			$fields = $this->{$model_name}->_get('autorized_fields');
		}
		$this->render_object->_set('post_data', $this->input->post());
		foreach($fields AS $field){
			if (method_exists($this->{$model_name}->_get('defs')[$field],'PrepareForDBA')){
				$datas[$field] 	= $this->{$model_name}->_get('defs')[$field]->PrepareForDBA($this->input->post($field));
			} else {
				$datas[$field] 	= $this->input->post($field);
			}
		}

		if ($this->input->post('form_mod') == 'edit'){
			if (isset($datas['id']) AND $id = $datas['id']){
				$this->{$model_name}->_set('key_value', $id);	
				$this->{$model_name}->_set('datas', $datas);
				$this->{$model_name}->put();
			} 
		} else if ($this->input->post('form_mod') == 'add'){
			//$this->_debug($datas);
			$this->data_view['id'] = $this->{$model_name}->post($datas);
			$datas['id'] = $this->data_view['id'];
		}

		foreach($this->{$model_name}->_get('autorized_fields') AS $field){
			if (method_exists($this->{$model_name}->_get('defs')[$field],'AfterExec')){
				$this->{$model_name}->_get('defs')[$field]->AfterExec($datas);
			} 
		}
		return $datas;
	}

	//must push out this stuff
	function set_civil_years($from = null){
		$filtered = $this->session->userdata( $this->set_ref_field('filter') );
	
		//echo "<p>$from:</p>".debug($this->session->userdata());

		if (isset($filtered['civil_year'])){
			return $filtered['civil_year'];
		} else {
			$this->session->set_userdata( $this->set_ref_field('filter') , ['civil_year'=>$this->config->item('civil_year')]);
			return $this->config->item('civil_year');
		}
	}

	/**
	 * @brief Set Rules for CRUD URL
	 * @param $key 
	 * @param $value 
	 * @returns 
	 * 
	 * 
	 */
	function _set_ui_rules($key,$value){
		$rules = new StdClass();
		$rules->url 	=  base_url($this->_controller_name.'/'.$key);
		$rules->term 	= $key;
		$rules->name 	= $this->lang->line(strtoupper($key).'_'.$this->_controller_name);
		if (!$this->acl->hasAccess(strtolower($this->_controller_name.'/'.$key))){
			$value = FALSE;
		} 
		$rules->autorize= $value;
		$rules->icon 	= $this->lang->line($key.'_icon');
		$rules->class  = $this->lang->line($key.'_class');
		$this->_rules[$key] = $rules;
	}

	/**
	 * @brief 		Destructor
	 * @param       $this->_debug boolean
	 * @return      void()
	 * 
	 * 
	 */
	function __destruct(){
		if ($this->_debug){
			echo debug($this->_debug_array, __file__);
		}
	}	

	/**
	 * Simple Render METHOD JS output
	 * 
	 * @param mixed $code 
	 * @param mixed $data 
	 * @return void 
	 */
	public function _renderJson($code, $data){
		http_response_code($code);
		echo json_encode($data);
	}

	
	/**
	 * @brief Generic SETTER
	 * @param $field 
	 * @param $value 
	 * @returns 
	 * 
	 * 
	 */
	public function _set($field,$value){
		$this->$field = $value;
	}

	/**
	 * @brief Generic GETTER
	 * @param $field 
	 * @returns 
	 * 
	 * 
	 */
	public function _get($field){
		return $this->$field;
	} 
}

?>
