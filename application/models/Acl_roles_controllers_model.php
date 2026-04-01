<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Acl_roles_controllers_model extends Core_model{

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'acl_roles_controllers');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'id_role');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Acl_roles_controllers.json');

	}

	function GetRole($id_role,$id_ctrl,$id_act){
		$this->db->select('*')
		->from($this->table)
		->where('id_role', $id_role)
		->where('id_ctrl', $id_ctrl)
		->where('id_act', $id_act);
		$datas = $this->db->get()->row();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}

	function DelRole($id_role){
		$this->db->where_in('id_role', $id_role)->delete($this->table);
		$this->_debug_array[] = $this->db->last_query();
	}
}
?>

