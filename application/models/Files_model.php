<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Files_model extends Core_model{

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'files');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Files.json');
	}
	
	/**
	 * Method GetFilesByType
	 *
	 * @param $type $type [explicite description]
	 * @param $statut $statut [B Brouillon ,P Publié ,A Archivés]
	 *
	 * @return void
	 */
	function GetFilesByType($type = null, $statut = 'P', $limit = 5){
		$data=$this->db->select('*')
		->from('files')
		->where('files.type IN ("'.$type.'") AND statut ="'.$statut.'"' )
		->order_by('`files`.`id` DESC')
		->limit($limit)
		->get();				
		//echo 	 $this->db->last_query();
		if ($data->num_rows()){

			return $data->result();
		}
		return false;
	}

}
?>