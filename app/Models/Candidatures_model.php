<?php

namespace App\Models;
/** @package  */
class Candidatures_model extends Core_model{
	
	protected $table = 'candidatures';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Candidatures.json';



	function GetFrom($id_grp,$id_fam){
		$data=$this->db->table('candidatures')->select('*')->where('candidatures.id_grp = "'.$id_grp.'" AND candidatures.id_fam ="'.$id_fam.'"')->orderBy('`candidatures`.`id` ASC')->get();				
		//echo 	 $this->db->last_query();
		if ($data->getNumRows()){

			return $data->getRow();
		}
		return false;
	}
	
}
?>