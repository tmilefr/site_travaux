<?php

namespace App\Controllers;
/**
 * Sendmail Controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
class Publics extends CrudController {

	protected function boot(): void
	{

		$this->_set('_debug', FALSE);

		$this->_controller_name = 'Publics_controller';  //controller name for routing
		$this->_model_name 		= 'Files_model';	   //DataModel
		$this->_edit_view 		= '';//template for editing
		$this->_list_view		= '';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>true);
		$this->_search 			= false;
		$this->_bg_color        = 'nicdark_bg_violet';
		$this->title            .= tr('GESTION_'.$this->_controller_name);

		$this->init();
		//pour dire, on affiche pas les boutons ajout et list dans les listes
		//$this->render_object->_set('_not_link_list', ['add','list']);
		
	}

	function files(){
		$view_name = 'unique/'.$this->_controller_name.'_files';
		$this->data_view['pvca'] 	= $this->Files_model->GetFilesByType('pub','P');
		$this->_set('view_inprogress', $view_name );
		$this->render_view();
	}

	public function index(){
		$this->goTo('publics/files');
	}

	

}
