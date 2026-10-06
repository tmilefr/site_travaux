<?php

namespace App\Models;
class Sendmail_model extends Core_model{
	
	protected $table = 'sendmail';
	protected $primaryKey = 'id';
	protected $order = 'created';
	protected $direction = 'desc';
	protected $json = 'Sendmail.json';



	function get4send($size = 10 ){
		$datas = $this->db->table($this->table)->select('*')->limit((int) $size)->orderBy($this->order, $this->direction)->where('statut !=', 1)->get()
					   ;

		$this->log();
		return $datas->getResult();	
	}

}
?>