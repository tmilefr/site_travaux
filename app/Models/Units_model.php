<?php

namespace App\Models;
class Units_model extends Core_model{
	
	protected $table = 'unites';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Unites.json';



}
?>