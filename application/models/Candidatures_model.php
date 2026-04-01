<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
/** @package  */
class Candidatures_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'candidatures');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Candidatures.json');
	}

	function GetFrom($id_grp,$id_fam){
		$data=$this->db->select('*')
		->from('candidatures')
		->where('candidatures.id_grp = "'.$id_grp.'" AND candidatures.id_fam ="'.$id_fam.'"' )
		->order_by('`candidatures`.`id` ASC')
		->get();				
		//echo 	 $this->db->last_query();
		if ($data->num_rows()){

			return $data->row();
		}
		return false;
	}
	
}
?>