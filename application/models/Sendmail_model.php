<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Sendmail_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'sendmail');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'created');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Sendmail.json');
	}

	function get4send($size = 10 ){
		$datas = $this->db->select('*')
						->limit($size)
					   ->order_by($this->order, $this->direction )
					   ->where('statut !=', 1)
					   ->get($this->table)
					   ;

		$this->_debug_array[] = $this->db->last_query();
		return $datas->result();	
	}

}
?>