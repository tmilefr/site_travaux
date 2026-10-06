<?php

namespace App\Models;
class Event_model extends Core_model{

	protected $table = 'events';
	protected $primaryKey = 'id';
	protected $order = 'title';
	protected $direction = 'desc';
	protected $json = 'Events.json';


	
	/**
	 * Method GetFilesByType
	 *
	 * @param $type $type [explicite description]
	 * @param $statut $statut [B Brouillon ,P Publié ,A Archivés]
	 *
	 * @return void
	 */
	function GetEventByType($type = null, $statut = 'P', $limit = 5){
		$data=$this->db->table('events')->select('*')->where('events.type = "'.$type.'" AND statut ="'.$statut.'"')->orderBy('`events`.`id` DESC')->limit($limit)->get();				
		//echo 	 $this->db->last_query();
		if ($data->getNumRows()){

			return $data->getResult();
		}
		return false;
	}

}
?>