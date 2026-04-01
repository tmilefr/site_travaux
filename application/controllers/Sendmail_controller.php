<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Sendmail Controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.24bis.com
 */
class Sendmail_controller extends MY_Controller {

	public function __construct(){
		parent::__construct();

		$this->_set('_debug', FALSE);

		$this->_controller_name = 'Sendmail_controller';  //controller name for routing
		$this->_model_name 		= 'Sendmail_model';	   //DataModel
		$this->_edit_view 		= 'edition/Sendmail_form';//template for editing
		$this->_list_view		= 'unique/Sendmail_view.php';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>true);
		$this->_search 			= false;
		$this->_bg_color = 'nicdark_bg_orange';
		$this->title .= $this->lang->line('GESTION_'.$this->_controller_name);

		$this->init();
		//pour dire, on affiche pas les boutons ajout et list dans les listes
		//$this->render_object->_set('_not_link_list', ['add','list']);
		$this->LoadModel('Sendmail_statut_model');
		
	}

	

}
