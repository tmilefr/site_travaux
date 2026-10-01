<?php

namespace App\Controllers;
/**
 * User Controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
class Acl_users_controller extends CrudController {

	protected $csv_path = '';

	protected function boot(): void
	{
		$this->_controller_name = 'Acl_users_controller';  //controller name for routing
		$this->_model_name 		= 'Acl_users_model';	   //DataModel
		$this->_edit_view 		= 'edition/Acl_users_form';//template for editing
		$this->_list_view		= 'unique/Acl_users_view.php';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>false);
		$this->title 			= tr('GESTION_'.$this->_controller_name);
		$this->_bg_color = 'nicdark_bg_red';
		$this->_set('_debug',FALSE);
		$this->init();
	}

}
