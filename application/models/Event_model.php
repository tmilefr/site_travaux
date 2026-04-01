<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Event_model extends Core_model{

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'events');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'title');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Events.json');
	}
	
	/**
	 * Method GetFilesByType
	 *
	 * @param $type $type [explicite description]
	 * @param $statut $statut [B Brouillon ,P Publié ,A Archivés]
	 *
	 * @return void
	 */
	function GetEventByType($type = null, $statut = 'P', $limit = 5){
		$data=$this->db->select('*')
		->from('events')
		->where('events.type = "'.$type.'" AND statut ="'.$statut.'"' )
		->order_by('`events`.`id` DESC')
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