<?php

namespace App\Models;
class Acl_actions_model extends Core_model{

	function __construct(){
		parent::__construct();
	
		$this->_set('table'	, 'acl_actions');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'action');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Acl_actions.json');
	}

}
?>

