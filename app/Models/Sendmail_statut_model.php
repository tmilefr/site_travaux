<?php

namespace App\Models;
class Sendmail_statut_model extends Core_model{
	
	protected $table = 'sendmail_statut';
	protected $primaryKey = 'id';
	protected $order = 'created';
	protected $direction = 'desc';
	protected $json = 'Sendmail_statut.json';



	function get_sendinprogress($id_sen){
		$datas = $this->tb()->where('id_sen', $id_sen)->where('sendstatut', 0)->get()->getRow();
		$this->log();
		return $datas;
	}


}
?>