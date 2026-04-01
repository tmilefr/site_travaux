<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Acl_roles_model extends Core_model{

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'acl_roles');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'role_name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Acl_roles.json');
	}

	function SetRoles($id = null){
		if ($id){
			$this->db->set('id', $id);
			$this->db->where('id_ctrl', 0);
			$this->db->update($this->table);	
			$this->_debug_array[] = $this->db->last_query();
		}
	}

	/**
	 * @brief Get permissions from database for  particular role
	 *
	 * SELECT `id_role`,`controller`,`action` FROM `acl_roles_controllers` AS ARC LEFT JOIN `acl_controllers` AC ON ARC.id_ctrl = AC.ID LEFT JOIN `acl_actions` AA ON ARC.id_act = AA.ID
	 * 
	 * @param   int $roleId
	 * @return  array
	 */
	public function getRolePermissions($roleId = 0)
	{
		if ($roleId){
			$query = $this->db->select([
				"controller",
				"action",
				"arc.id_role"
			])
			->from('acl_roles_controllers arc')
			->join('acl_controllers AC', "arc.id_ctrl = AC.ID")
			->join('acl_actions AA', "arc.id_act = AA.ID")
			->where("arc.id_role", $roleId)
			->get();
		} else {
			$query = $this->db->select([
				"controller",
				"action",
				"arc.id_role"
			])
			->from('acl_roles_controllers arc')
			->join('acl_controllers AC', "arc.id_ctrl = AC.ID")
			->join('acl_actions AA', "arc.id_act = AA.ID")
			->get();
		}
		$this->_debug_array[] = $this->db->last_query();
		$permissions = array();
		if ($query && $query->num_rows() > 0)
		{
			// Add to the list of permissions
			foreach ($query->result_array() as $row)
			{		    
				$permissions[$row['id_role']][] = strtolower($row['controller'] . '/' . $row['action']);
			}
		}
		return $permissions;
	}

}