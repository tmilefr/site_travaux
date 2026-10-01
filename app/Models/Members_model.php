<?php

namespace App\Models;
/** @package  */
class Members_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'members');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Member.json');
	}

}
?>