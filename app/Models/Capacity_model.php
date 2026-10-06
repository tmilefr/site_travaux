<?php

namespace App\Models;
class Capacity_model extends Core_model{

	protected $table = 'capacitys';
	protected $primaryKey = 'id';
	protected $order = 'id_fam';
	protected $direction = 'desc';
	protected $json = 'Capacity.json';



}
?>