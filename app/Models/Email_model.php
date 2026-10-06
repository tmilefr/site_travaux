<?php

namespace App\Models;
class Email_model extends Core_model{
	
	protected $table = 'emails';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Emails.json';



}
?>