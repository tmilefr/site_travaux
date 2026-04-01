<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * @brief
 *
 *
 */
class Parameters extends MY_Controller {

	protected $params = []; //list of parameters
	/**
	 * @brief
	 * @returns
	 *
	 *
	 */
	public function __construct(){
		parent::__construct();
		$this->_model_name 		= 'Parameters_model';	   //DataModel
		$this->_controller_name = 'Parameters';  //controller name for routing
		$this->init();

		 //['app_name','slogan','debug_app','protocol','smtp_host','smtp_port','smtp_user','smtp_pass','smtp_crypto','charset','mailtype','wordwrap','newline','crlf'];

	}

	public function list(){
		$fields = $this->Parameters_model->_get('autorized_fields');
		$dba_data = new StdClass();
		foreach($fields AS $field){
			if ($item_value = $this->input->post($field)){
				$this->config->set_item($field, $item_value);
			}
			
			$dba_data->{$field} 	= $this->config->item($field);
		}
		$this->render_object->_set('dba_data', $dba_data);

		$this->_set('view_inprogress','edition/Parameters_form');
		$this->render_view();
	}
}