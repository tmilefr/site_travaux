<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
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