<?php

namespace App\Models;
/** @package  */
class Members_model extends Core_model{
	
	protected $table = 'members';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Member.json';



}
?>