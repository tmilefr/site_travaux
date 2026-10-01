<?php

namespace App\Models;
class Trombi_model extends Core_model{
	
	protected $table = 'trombi';
	protected $primaryKey = 'id';
	protected $order = 'order';
	protected $direction = 'desc';
	protected $json = 'Trombi.json';



	public function GetConsolidateMember($id){
		if($id)
			return $this->db->query('SELECT gm.name,gm.surname,gm.email,gm.phone,gm.picture,gm.thumbnail, gr.title,tr.classif FROM `trombi` tr LEFT JOIN `groupes_member` gm ON tr.ref = gm.id LEFT JOIN `groupes` gr ON tr.id_grp = gr.id WHERE tr.id='.$id)->getRow();
	}


    function GetMembers($id_grp, $classif = false, $exclude = []){
		$Group_members = [];
		if ($id_grp){
			$data=$this->db->table('trombi')->select('*')->where('trombi.id_grp = '.$id_grp.' '.((count($exclude)) ? ' AND classif NOT IN ("'.implode('","',$exclude).'")':''))->orderBy('`trombi`.`id` ASC')->get();
			$this->log();
			if ($data->getNumRows()){
				foreach($data->getResult() AS $member){
					if ($member->ref){
						$details = $this->db->table('groupes_member')->select('*')->where('id = "'.$member->ref.'"')->orderBy('`groupes_member`.`id` ASC')->get();				
						//TODO : si plus de 1 réponse => nok !	
						$this->log();
						$member->details = $details->getRow();
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
		$data=$this->db->table('trombi')->select('*')->join('groupes','`groupes`.`id`= `trombi`.`id_grp`','left')->where('trombi.ref = "'.$id_grpm.'"')->orderBy('`groupes`.`id` ASC')->get();				
		//echo 	 $this->db->last_query();
		if ($data->getNumRows()){
			foreach($data->getResult() AS $group){
				$groups[] = $group;

				//echo debug($group);
			}
		}
		return $groups;
	}

	function GetMemberFromClassif($id_grp, $classif = 'RT'){
		$data=$this->db->table('trombi')->select('*')->join('groupes_member','`groupes_member`.`id`= `trombi`.`ref`','left')->where('trombi.id_grp = "'.$id_grp.'" AND trombi.classif ="'.$classif.'"')->orderBy('`groupes_member`.`id` ASC')->get();				
		//echo 	 $this->db->last_query();
		if ($data->getNumRows()){

			return $data->getRow();
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