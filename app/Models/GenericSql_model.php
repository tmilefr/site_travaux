<?php

namespace App\Models;

use Exception;
#[\AllowDynamicProperties]
class GenericSql_model extends \App\Libraries\Compat\Model {
	public function __construct()
	{
		parent::__construct();
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