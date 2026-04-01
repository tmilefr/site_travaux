<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Capacity_model extends Core_model{

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'capacitys');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'id_fam');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Capacity.json');
	}

}
?>