<?php

namespace App\Models;
/** @package  */
class GroupesMembers_model extends Core_model{
	
	protected $table = 'groupes_member';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'GroupesMembers.json';


}
?>