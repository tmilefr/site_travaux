<?php

namespace App\Models;
/** @package  */
class Grprelated_model extends Core_model{
	
	protected $table = 'grp_related';
	protected $primaryKey = 'id';
	protected $order = 'id_grp';
	protected $direction = 'desc';
	protected $json = 'Grp_related.json';



}
?>