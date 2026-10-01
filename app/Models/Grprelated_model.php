<?php

namespace App\Models;
/** @package  */
class Grprelated_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'grp_related');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'id_grp');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Grp_related.json');
	}

}
?>