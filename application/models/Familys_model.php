<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Familys_model extends Core_model{

	

	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'famille');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'login');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Familys.json');
	}

	function GetFamily($id_fam){
		$query = $this->db->select('*')
		->from($this->table)
		->where("id", $id_fam )
		->get();
		$this->_debug_array[] = $this->db->last_query();
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
		return false;
	}


	function GetFamilyByLogin($email){
		$email = strtolower(str_replace("\r\n","",$email));
		$query = $this->db->select('*')
		->from($this->table)
		->where("LOWER(e_mail)", $email )
		->get();
		$this->_debug_array[] = $this->db->last_query();
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
		return false;
	}

	function SetCivilYears($id,$civil_year){
		$this->db->set('civil_year', $civil_year);
		$this->db->where('id', $id);
		$this->db->update($this->table); 
	}


	function verifyLogin($login, $password){
	    $query = $this->db->select('*')
			->from($this->table)
			->where("login", $login )
			->get();

		$this->_debug_array[] = $this->db->last_query();
		// User was found
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			//nouveau système et ancien (md5).
			if (hash_equals($row['password'], crypt($password, PASSWORD_SALT)) || md5($password) == $row['password'] ) {
				return $row;
			}
		}
		return false;
	}

	function verifyLoginAPI($idfamille){
	    $query = $this->db->select('*')
			->from($this->table)
			->where("idfamille", $idfamille )
			->get();
		$this->_debug_array[] = $this->db->last_query();
		// User was found
		if ($query->num_rows() > 0)
		{
			$row = $query->row_array();
			return $row;
		}
		return false;
	}

}
?>