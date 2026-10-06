<?php

namespace App\Models;
class Acl_roles_model extends Core_model{

	protected $table = 'acl_roles';
	protected $primaryKey = 'id';
	protected $order = 'role_name';
	protected $direction = 'desc';
	protected $json = 'Acl_roles.json';



	function SetRoles($id = null){
		if ($id){
			$this->tb()->set('id', $id)->where('id_ctrl', 0)->update();	
			$this->log();
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
			$query = $this->db->table('acl_roles_controllers arc')->select([
				"controller",
				"action",
				"arc.id_role"
			])->join('acl_controllers AC', "arc.id_ctrl = AC.ID")->join('acl_actions AA', "arc.id_act = AA.ID")->where("arc.id_role", $roleId)->get();
		} else {
			$query = $this->db->table('acl_roles_controllers arc')->select([
				"controller",
				"action",
				"arc.id_role"
			])->join('acl_controllers AC', "arc.id_ctrl = AC.ID")->join('acl_actions AA', "arc.id_act = AA.ID")->get();
		}
		$this->log();
		$permissions = array();
		if ($query && $query->getNumRows() > 0)
		{
			// Add to the list of permissions
			foreach ($query->getResultArray() as $row)
			{		    
				$permissions[$row['id_role']][] = strtolower($row['controller'] . '/' . $row['action']);
			}
		}
		return $permissions;
	}

}