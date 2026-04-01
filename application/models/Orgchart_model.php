<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Orgchart_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'groupes');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'title');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Groupes.json');
	}

	function UpdateHit($id){
		$this->db->query('UPDATE groupes SET hit = 0');
		$this->_debug_array[] = $this->db->last_query();
		$this->db->query('UPDATE groupes SET hit = 1 WHERE id = '.$id);
		$this->_debug_array[] = $this->db->last_query();
	}

	function GetHit(){
		$this->db->select('*')
		->from($this->table)
		->where('hit', '1');
		$datas = $this->db->get()->row();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;	
	}

	function GetMembers($id_grp, $classif = false ){
		$members = [];
		$data=$this->db->select('*')
		->from('trombi')
		->where('trombi.id_grp', $id_grp)
		->order_by('`trombi`.`id` ASC')
		->get();
		if ($data->num_rows()){
			//SELECT `famille`.`nom`,CONCAT_WS(\"_\",`members`.`id`,`famille`.`id`) AS id_fam,  CONCAT_WS(\" \",`members`.`nom`, `members`.`prenom`) AS nom_prenom FROM `famille` LEFT JOIN `members` ON `members`.`id_fam`= `famille`.`id` ORDER BY `nom_prenom` DESC
			foreach($data->result() AS $member){
				if ($member->nom){
					$family = $this->db->select('CONCAT_WS("_",`members`.`id`,`members`.`id_fam`) AS reference, `famille`.`nom`, CONCAT_WS(" ",`members`.`nom`, `members`.`prenom`) AS nom_prenom')
					->from('famille')
					->join('members','`members`.`id_fam`= `famille`.`id`','left')
					->where('CONCAT_WS("_",`members`.`id`,`members`.`id_fam`) = "'.$member->nom.'" OR `famille`.`id` = "'.$member->nom.'"' )
					->order_by('`members`.`id` ASC')
					->get();				
					//TODO : si plus de 1 réponse => nok !	
					$member->family = $family->row();
				}
				if ($classif)
					$members[$member->classif][] = $member;
				else
					$members[] = $member; 
			}
		}
		return $members;
	}

	function GetGroupe($id_grp){
		$this->_set('key_value', $id_grp);
		$grp = $this->get_one();
		if ($grp)
			$grp->members = $this->GetMembers($id_grp);
		return $grp;
	}

}
?>