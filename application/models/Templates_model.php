<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Templates_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'templates');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'id_type');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Template.json');
	}

}
?>