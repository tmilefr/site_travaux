<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Sendmail_statut_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'sendmail_statut');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'created');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Sendmail_statut.json');
	}

	function get_sendinprogress($id_sen){
		$this->db->select('*')
		->from($this->table)
		->where('id_sen', $id_sen)
		->where('sendstatut',0);
		$datas = $this->db->get()->row();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}


}
?>