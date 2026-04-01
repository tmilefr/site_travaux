<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Trombi_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		$this->_set('table'	, 'trombi');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'order');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Trombi.json');
	}

	public function GetConsolidateMember($id){
		if($id)
			return $this->db->query('SELECT gm.name,gm.surname,gm.email,gm.phone,gm.picture,gm.thumbnail, gr.title,tr.classif FROM `trombi` tr LEFT JOIN `groupes_member` gm ON tr.ref = gm.id LEFT JOIN `groupes` gr ON tr.id_grp = gr.id WHERE tr.id='.$id)->row();
	}


    function GetMembers($id_grp, $classif = false, $exclude = []){
		$Group_members = [];
		if ($id_grp){
			$data=$this->db->select('*')
			->from('trombi')
			->where('trombi.id_grp = '.$id_grp.' '.((count($exclude)) ? ' AND classif NOT IN ("'.implode('","',$exclude).'")':''))
			->order_by('`trombi`.`id` ASC')
			->get();
			$this->_debug_array[] = $this->db->last_query();
			if ($data->num_rows()){
				foreach($data->result() AS $member){
					if ($member->ref){
						$details = $this->db->select('*')
						->from('groupes_member')
						->where('id = "'.$member->ref.'"')
						->order_by('`groupes_member`.`id` ASC')
						->get();				
						//TODO : si plus de 1 réponse => nok !	
						$this->_debug_array[] = $this->db->last_query();
						$member->details = $details->row();
					}
					if ($classif)
						$Group_members[$member->classif][] = $member;
					else
						$Group_members[] = $member; 
				}
			}
		}
		return $Group_members;
    }

	function GetGroupeFromMember($id_grpm, $classif = 'RT' ){
		$groups = [];
		$data=$this->db->select('*')
		->from('trombi')
		->join('groupes','`groupes`.`id`= `trombi`.`id_grp`','left')
		->where('trombi.ref = "'.$id_grpm.'"' )
		->order_by('`groupes`.`id` ASC')
		->get();				
		//echo 	 $this->db->last_query();
		if ($data->num_rows()){
			foreach($data->result() AS $group){
				$groups[] = $group;

				//echo debug($group);
			}
		}
		return $groups;
	}

	function GetMemberFromClassif($id_grp, $classif = 'RT'){
		$data=$this->db->select('*')
		->from('trombi')
		->join('groupes_member','`groupes_member`.`id`= `trombi`.`ref`','left')
		->where('trombi.id_grp = "'.$id_grp.'" AND trombi.classif ="'.$classif.'"' )
		->order_by('`groupes_member`.`id` ASC')
		->get();				
		//echo 	 $this->db->last_query();
		if ($data->num_rows()){

			return $data->row();
		}
		return false;
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