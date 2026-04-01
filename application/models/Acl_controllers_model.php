<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Acl_controllers_model extends Core_model{

	function __construct(){
		parent::__construct();
		$this->_set('table'	, 'acl_controllers');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'controller');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Acl_controllers.json');
	}

	/*function GetCtrl(){
		$datas = $this->db->select( '*' )
		->join('acl_actions', $this->table.'.id=acl_actions.id_ctrl' ,'left')
		->get($this->table);
		$this->_debug_array[] = $this->db->last_query();

		return $datas->result();
	}*/

}
?>

