<?php

namespace App\Models;
class Acl_actions_model extends Core_model{

	protected $table = 'acl_actions';
	protected $primaryKey = 'id';
	protected $order = 'action';
	protected $direction = 'desc';
	protected $json = 'Acl_actions.json';



}
?>

