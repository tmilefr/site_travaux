<?php

namespace App\Models;
class Acl_controllers_model extends Core_model{

	protected $table = 'acl_controllers';
	protected $primaryKey = 'id';
	protected $order = 'controller';
	protected $direction = 'desc';
	protected $json = 'Acl_controllers.json';



	/*function GetCtrl(){
		$datas = $this->db->select( '*' )
		->join('acl_actions', $this->table.'.id=acl_actions.id_ctrl' ,'left')
		->get($this->table);
		$this->log();

		return $datas->getResult();
	}*/

}
?>

