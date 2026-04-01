<?php
defined('BASEPATH') || exit('No direct script access allowed');


//require_once(APPPATH.'libraries/Bootstrap_tools.php'); //on casse l'utilisation de $this->CI

Class Render_object{

	protected $CI 		= NULL; //Controller instance 
	protected $datamodel= NULL; //Name of datamodel
	protected $id 		= NULL; //id of active element
	protected $dba_data = NULL; //Data from DATABASE from id element
	protected $post_data= NULL; //Data from POST
	protected $_debug 	= FALSE;//Debug 
	protected $_model 	= [];
	protected $_ui_rules= [];
	protected $form_mod = FALSE;
	protected $notime	= TRUE;
	protected $_reset   = [];
	protected $_not_link_list = ['add','list'];
	protected $_render_model = 'html';
	
	protected $RenderTools = NULL; //Objet de rendu

	protected $_options = []; //herite from WV_Controller, all option in progress
	
	private static $instance;

	/**
	 * Get the Bootstrap_tools singleton
	 *
	 * @static
	 * @return	object
	 */
	public static function &get_instance()
	{
		return self::$instance;
	}

	public function __construct()
	{
		//$this->CI =& get_instance();
		self::$instance =& $this;
		//$this->CI =& get_instance();
		$this->RenderTools = new Bootstrap_tools();

	}
	
	public function SetOption($_options){
		$this->_options['filter'] 		= $_options['filter']; //$this->CI->session->userdata($this->CI->set_ref_field('filter'));
		$this->_options['direction'] 	= $_options['direction']; //$this->CI->session->userdata($this->CI->set_ref_field('direction'));
		$this->_options['config'] 		= $_options['config'];//$this->CI->config
	}

	public function _set($field,$value)
	{
		$this->$field = $value;
	}

	public function _get($field)
	{
		return $this->{$field};
	}
	
	public function _reset_value($field)
	{
		$this->_reset[$field] = true;
	}	
	
	public function label($name)
	{
		return $this->RenderTools->label($name);
	}	

	public function In_maintenance(){
		if ($this->_options['config']->item('maintenance'))
			return true;
		else
			return false;
	}

	public function render_element_menu($data = null, $blocked = false )
	{
		if (!$this->datamodel){
			return false;
		}		
		$key_value ='';
		$element_menu = '';
		if ($data){	
			$key_value = $data->{$this->_model[$this->datamodel]->_get('key')};
		} else {
			if (isset($this->dba_data)){ // try to check database
				$key_value = $this->dba_data->{$this->_model[$this->datamodel]->_get('key')};
			}
		}		
		if ($key_value)
		{
			foreach($this->_ui_rules AS $rule){
				if (!in_array($rule->term , $this->_not_link_list ) AND $rule->autorize AND  (!$blocked || $rule->term != 'delete') ){
					$element_menu .= $this->RenderTools->render_icon_link($rule->url , $key_value , $rule->icon, $rule->class.' '.((isset($data->override_class)) ? $data->override_class:'') );
				}
			}
		}
		return $element_menu;
	}
	
	/**
	 * @param mixed $field 
	 * @param string $mode 
	 * @param bool $datamodel 
	 * @return string 
	 */
	public function render_link($field, $mode = 'list', $datamodel = false)
	{
		if (!$datamodel)
			$datamodel = $this->datamodel;

		if(isset($this->_model[$datamodel]) ){
			if ( $this->_model[$datamodel]->_get('defs')[$field]->dbforge->type == 'INT'){
				$null_value = 0;
			} else {
				$null_value = '';
			}
			$add_string =  '';
			if (isset($this->_options['filter'][$field])){
				$add_string = '<span class="badge badge-success">'.((isset($this->_model[$this->datamodel]->_get('defs')[$field]->values[$this->_options['filter'][$field]])) ? $this->_model[$this->datamodel]->_get('defs')[$field]->values[$this->_options['filter'][$field]]:$this->_options['filter'][$field]).'</span>';
			}
			$string_render_link = '<div class="btn-group">';
			$string_render_link .= $this->RenderTools->render_head_link($field, $this->_options['direction'],$this->_ui_rules[$mode]->url, $add_string);
			if ($this->_model[$datamodel]->_get('defs')[$field]->_get('values')){
				$string_render_link .= $this->RenderTools->render_dropdown($field, $this->_model[$datamodel]->_get('defs')[$field]->_get('values'), $this->_ui_rules[$mode]->url, $null_value );
			}
			$string_render_link .= '</div>';
			return $string_render_link;
		} else {
			return  $datamodel.' not set in render_link';
		}
	}
	
	/* Find How to ... */
	public function _getCi($field){
		$this->CI =& get_instance();
		$data = $this->CI->_get($field);
		unset($this->CI);
		return $data;
	}

	public function Set_Rules_elements($name, Core_model $DataModelToUse )
	{
		//$this->datamodel = $name;
		$this->_model[$name] = $DataModelToUse;
		//set Validation Rules config by DataModel
		$config = [];
		foreach($this->_model[$name]->_get('defs') AS $field=>$defs){
			if (isset($defs->rules) AND $defs->rules){
				$config[] = ['field' => $field,'label' => Lang($field),'rules' =>  $defs->rules];
				//$this->CI->form_validation->set_rules($field, $this->CI->lang->line($field) , $defs->rules); changed for multi-forms !
			}	
		}	
		return $config;
	}

	// design by type
	function GetColors($type = ""){
		return $this->RenderTools->GetColors($type);
	}
	// design by type
	function GetDesign($type = ""){
		return $this->RenderTools->GetDesign($type);
	}

	/**
	 * @param mixed $DataModelToUse 
	 * @param mixed $field 
	 * @param mixed $id 
	 * @param mixed $value 
	 * @param mixed $value_test 
	 * @param mixed $datatarget 
	 * @return mixed element time with override js for link timepicker on valid
	 */
	function RenderFormElementTimeWithLink($DataModelToUse, $field, $id, $value, $value_test , $datatarget ){
		
		$obj = $this->_model[$DataModelToUse]->_get('defs')[$field];	
		$obj->_set('form_mod', $this->form_mod);

		if ( $value_test )
			$obj->_set('value',  $value_test );
		else 
			$obj->_set('value', $value);

		$obj->_set('overridename', $field.$id);		 

		$obj->_set('change', "function(time) {
			// the input field
			var element = $(this), text;
			// get access to this Timepicker instance
			var timepicker = element.timepicker();
			unitcalc([timepicker.format(time),'".json_encode($datatarget)."','".$id."']); //js on unit_calc.js
		}");

		return $this->_model[$DataModelToUse]->_get('defs')[$field]->RenderFormElement();
	}



	//need to make a real element object.
	/**
	 * @param mixed $field 
	 * @param bool $val 
	 * @param mixed $DataModelToUse 
	 * @param bool $disabled 
	 * @param string $overridename (mutli form )
	 * @return mixed 
	 * @todo : use param class object !
	 */
	function RenderFormElement($field, $val = false, $DataModelToUse = null, $disabled = false, $overridename = '', $datatarget = '')
	{
		if ($DataModelToUse == null){ //mutli model !
			$DataModelToUse = $this->datamodel;
		}		
		$value = null;
		if ($val){
			$value = $val;
		} else {
			if (isset($this->_reset[$field]) AND  $this->_reset[$field]){
				
			} else {
				if ($value = set_value($field)){ //in first, POST data

				} else {
					if (isset($this->dba_data)){ // try to check database
						$value = $this->dba_data->{$field};
					}
				}
			}
		}
		$obj = $this->_model[$DataModelToUse]->_get('defs')[$field];	
		$obj->_set('form_mod', $this->form_mod);
		$obj->_set('value', $value);
		$obj->_set('disabled', $disabled);	
		$obj->_set('overridename', $overridename);		 
		$obj->_set('datatarget', $datatarget);		  
		return $this->_model[$DataModelToUse]->_get('defs')[$field]->RenderFormElement();
	}
	
	/**
	 * @param mixed $object 
	 * @param mixed $method 
	 * @return mixed 
	 */
	function Render($object, $method, $id = null){
		if (isset($object->$method)){
			$object->_set('parent_id', $id);
			return $object->$method;
		}
	}

	/**
	 * @param mixed $arr 
	 * @param mixed $value 
	 * @param mixed $default 
	 * @return mixed 
	 */
	function RenderArray($arr, $value, $default = null){
		if (isset($arr[$value])) {
			return $arr[$value];
		} else {
			return $default;
		}
	}

	/**
	 * @param mixed $field 
	 * @param mixed $value 
	 * @param mixed $parent_id 
	 * @param mixed $DataModelToUse 
	 * @return mixed 
	 */
	function RenderElement($field, $value = null, $parent_id = null, $DataModelToUse = null, $class= '')
	{

		if ($DataModelToUse == null){ //mutli model !
			$DataModelToUse = $this->datamodel;
		}
		if($DataModelToUse && isset($this->_model[$DataModelToUse])){
			if (!$value) {
				if (isset($this->dba_data)){ // try to check database
					$value = $this->dba_data->{$field};
				}
			}	
			if (isset($this->_model[$DataModelToUse]->_get('defs')[$field])){
				$obj = $this->_model[$DataModelToUse]->_get('defs')[$field];

				$obj->_set('class', $class);	
				if ($parent_id){
					$obj->_set('parent_id',$parent_id);
				}
				$obj->_set('form_mod', $this->form_mod);	
				$obj->_set('value', $value);
				return $obj->Render($this->_render_model);
			} else {
				return $field.' not set in '.$DataModelToUse;
			}	


		} else {
			return  $DataModelToUse.' not set in '.print_r(array_keys($this->_model), TRUE);
		}
	}
	
	function RenderImg($file, $alt = "", $path = ""){
		if ($path)
		$this->RenderTools->_set('_asset_img', $path);
		return $this->RenderTools->RenderImg($file, $alt);
	}


	public function __destruct()
	{
		if ($this->_debug == TRUE){
			echo debug($this, __file__ );
		}
	}
	
}
