<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Templates controller
 *
 * @package     WebApp
 * @subpackage  Core
 * @category    Factory
 * @author      Tmile
 * @link        http://www.dev-asso.fr
 */
class Templates_controller extends MY_Controller {


	/**
	 * @return void 
	 * @throws RuntimeException 
	 */
	public function __construct(){
		parent::__construct();
		$this->_controller_name = 'Templates_controller';  //controller name for routing
		$this->_model_name 		= 'Templates_model';	   //DataModel
		$this->_edit_view 		= 'edition/Templates_form';//template for editing
		$this->_list_view		= 'unique/Templates_view.php';
		$this->_autorize 		= array('list'=>true,'add'=>true,'edit'=>true,'delete'=>true,'view'=>true,'valid'=>true);
		$this->title 			.=  $this->lang->line('GESTION').$this->lang->line($this->_controller_name);

		$this->_bg_color = 'nicdark_bg_violet';

		$this->init();
		//pour dire, on affiche pas les boutons ajout et list dans les listes
		$this->render_object->_set('_not_link_list', ['add','list']);

	}
}
