<?php

namespace App\Models;
class Units_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'unites');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Unites.json');
	}

}
?>