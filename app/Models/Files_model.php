<?php

namespace App\Models;
class Files_model extends Core_model{

	protected $table = 'files';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Files.json';


	
	/**
	 * Method GetFilesByType
	 *
	 * @param $type $type [explicite description]
	 * @param $statut $statut [B Brouillon ,P Publié ,A Archivés]
	 *
	 * @return void
	 */
	function GetFilesByType($type = null, $statut = 'P', $limit = 5){
		$data=$this->db->table('files')->select('*')->where('files.type IN ("'.$type.'") AND statut ="'.$statut.'"')->orderBy('`files`.`id` DESC')->limit($limit)->get();				
		//echo 	 $this->db->last_query();
		if ($data->getNumRows()){

			return $data->getResult();
		}
		return false;
	}

}
?>