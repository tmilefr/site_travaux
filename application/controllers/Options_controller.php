<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Options_controller extends MY_Controller {

	public function __construct(){
		parent::__construct();
		
		$this->_controller_name = 'Options_controller';  //controller name for routing
		$this->_model_name 		= 'Options_model';	   //DataModel
		$this->_edit_view 		= 'edition/Options_form';//template for editing
		$this->_list_view		= 'unique/Options_view.php';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>false);
		$this->_search 			= false;

		$this->_bg_color = 'nicdark_bg_orange';

		$this->_set('_debug', FALSE);
		
		$this->title .= $this->lang->line('GESTION_'.$this->_controller_name);

		$this->init();
		$this->render_object->_set('_not_link_list', ['add','list']);
	}

}
