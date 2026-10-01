<?php

namespace App\Models;
class Templates_model extends Core_model{
	
	protected $table = 'templates';
	protected $primaryKey = 'id';
	protected $order = 'id_type';
	protected $direction = 'desc';
	protected $json = 'Template.json';



}
?>