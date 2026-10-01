<?php

namespace App\Models;
class Parameters_model extends Core_model{

	protected $table = 'parameters';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Parameters.json';


}
?>