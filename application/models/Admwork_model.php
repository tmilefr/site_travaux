<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Admwork_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'travaux');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Travaux.json');
	}


	function DraftPublication($civil_year){
		$this->db->set('statut', 1);
		$this->db->where('statut', 0);
		$this->db->where("civil_year", $civil_year );
		$this->db->update($this->table);
	}

	function GetFiltered($civil_year, $schools){
		$this->db->order_by('date_travaux','DESC');
		$query = $this->db->select('*')
		->from($this->table)
		->where("civil_year IN ('".$civil_year."','2025-2026')")
		->where("statut", 1 )
		->where_in('accespar',$schools)
		->get();
		$this->_debug_array[] = $this->db->last_query();
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		return false;
	}

	function GetMax($id_travaux){
		//nb_inscrits_max
		$data=$this->db->select('nb_inscrits_max')
				->from($this->table)
				->where('id',$id_travaux)
				->get();
		$this->_debug_array[] = $this->db->last_query();
		return (($data->num_rows()) ? $data->result()[0]:FALSE);				
	}

	function stats(){
		$datas = $this->db->select("count(*) AS nb,type")
		->group_by("type")
		->get($this->table)
		->result();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}

	/**
	 * @brief 
	 * @returns 
	 * 
	 * 
	 */
	public function __destruct(){
		//echo debug($this->_debug_array, __file__);
	}	
}
?>