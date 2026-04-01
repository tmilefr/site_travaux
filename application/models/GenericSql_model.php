<?php
defined('BASEPATH') || exit('No direct script access allowed');
class GenericSql_model extends CI_Model {
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}
	
	public function distinct($table,$id,$value){
		$this->db->distinct();
		return $this->db->select("$id,$value")->get($table)->result();
	}
	
	public function exec($sql){
		try {
			if ($datas = $this->db->query($sql)){
				return $datas ;
			} else {
				return false;
			}
		} catch (Exception $e) {
			return  'Exception reçue : '. $e->getMessage(). "\n";
		}
	}
	
	public function get($table,$order,$direction ){
		$datas = $this->db->select('*')
                           ->order_by($this->order, $this->direction )
                           ->get($table)
						   ->result();
		return $datas;
	}
	
}