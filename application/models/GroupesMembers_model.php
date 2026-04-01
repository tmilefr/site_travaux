<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
/** @package  */
class GroupesMembers_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'groupes_member');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'GroupesMembers.json');
	}
}
?>