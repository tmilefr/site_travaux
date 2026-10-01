<?php

namespace App\Models;
class Options_model extends Core_model{

	protected $table = 'options';
	protected $primaryKey = 'id';
	protected $order = 'filter';
	protected $direction = 'desc';
	protected $json = 'Options.json';



	function GetOpt($type){
		$options = [];
		$this->_set('filter',['filter'=>$type]);
		$opts = $this->get_all();
		if($opts){
			foreach($opts AS $opt){
				$options[$opt->cle] = $opt;
			}
		}
		return $options;
	}


}
?>