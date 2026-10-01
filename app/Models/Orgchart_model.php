<?php

namespace App\Models;
class Orgchart_model extends Core_model{
	
	protected $table = 'groupes';
	protected $primaryKey = 'id';
	protected $order = 'title';
	protected $direction = 'desc';
	protected $json = 'Groupes.json';



	function UpdateHit($id){
		$this->db->query('UPDATE groupes SET hit = 0');
		$this->log();
		$this->db->query('UPDATE groupes SET hit = 1 WHERE id = '.$id);
		$this->log();
	}

	function GetHit(){
		$datas = $this->tb()->where('hit', '1')->get()->getRow();
		$this->log();
		return $datas;	
	}

	function GetMembers($id_grp, $classif = false ){
		$members = [];
		$data=$this->db->table('trombi')->select('*')->where('trombi.id_grp', $id_grp)->orderBy('`trombi`.`id` ASC')->get();
		if ($data->getNumRows()){
			//SELECT `famille`.`nom`,CONCAT_WS(\"_\",`members`.`id`,`famille`.`id`) AS id_fam,  CONCAT_WS(\" \",`members`.`nom`, `members`.`prenom`) AS nom_prenom FROM `famille` LEFT JOIN `members` ON `members`.`id_fam`= `famille`.`id` ORDER BY `nom_prenom` DESC
			foreach($data->getResult() AS $member){
				if ($member->nom){
					$family = $this->db->table('famille')->select('CONCAT_WS("_",`members`.`id`,`members`.`id_fam`) AS reference, `famille`.`nom`, CONCAT_WS(" ",`members`.`nom`, `members`.`prenom`) AS nom_prenom')->join('members','`members`.`id_fam`= `famille`.`id`','left')->where('CONCAT_WS("_",`members`.`id`,`members`.`id_fam`) = "'.$member->nom.'" OR `famille`.`id` = "'.$member->nom.'"')->orderBy('`members`.`id` ASC')->get();				
					//TODO : si plus de 1 réponse => nok !	
					$member->family = $family->getRow();
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