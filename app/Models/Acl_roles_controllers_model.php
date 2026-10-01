<?php

namespace App\Models;
class Acl_roles_controllers_model extends Core_model{

	protected $table = 'acl_roles_controllers';
	protected $primaryKey = 'id';
	protected $order = 'id_role';
	protected $direction = 'desc';
	protected $json = 'Acl_roles_controllers.json';



	function GetRole($id_role,$id_ctrl,$id_act){
		$datas = $this->tb()->where('id_role', $id_role)->where('id_ctrl', $id_ctrl)->where('id_act', $id_act)->get()->getRow();
		$this->log();
		return $datas;
	}

	function DelRole($id_role){
		$this->db->table($this->table)->whereIn('id_role', $id_role)->delete();
		$this->log();
	}
}
?>

