<?php

namespace App\Models;
class Infos_model extends Core_model{
	
	protected $table = 'infos';
	protected $primaryKey = 'id';
	protected $order = 'id_famille';
	protected $direction = 'desc';
	protected $json = 'Infos.json';

	function __construct(){
		parent::__construct();
		$this->_set('_model_name'	, 'Infos_model');
	}

	/**
	 * @param mixed $data 
	 * @return void 
	 */
	function valid_unit($id, $datas){
		if ($id){

			$this->tb()->where($this->primaryKey, $id)->update($datas);
			$this->log();
			//echo debug($this->db->last_query());
		}
	}

	/**
	 * @param mixed		$opt->context //pending , valid
	 * @param mixed		$opt->id_fam //family
	 * @param bool   	$opt->full //join with family
	 * @param mixed  	$opt->valideur (pour le filtrage)
	 * @param string 	$order 
	 * @param string 	$direction 
	 * @return mixed 
	 */
	function GetUnits($opt = null ){  //$context = null,$id_fam = null, $full = false, $ids = null
		//$this->_set_filter();
		//$this->_set_search();

		$b = $this->tb()->select('infos.*,travaux.civil_year, travaux.type, travaux.type_session, (travaux.nb_units * infos.nb_participants) AS nb_units,travaux.date_travaux,travaux.titre,travaux.description,travaux.nb_inscrits_max,travaux.referent_travaux ')->join('travaux','travaux.id=infos.id_travaux','left');
		if (isset($opt->full))
			$b->join('famille','famille.id=infos.id_famille','left');
		//filtering
		if(isset($opt->context))
			switch($opt->context){
				case 'pending':
					$b->where('nb_unites_valides !=',0);
				break;
				case "valid":
					$b->where('nb_unites_valides_effectif !=',0);
				break;
			}
		//$this->db->where('travaux.archived !=',1);
		//famille
		if(isset($opt->id_fam))
			$b->where('infos.id_famille', $opt->id_fam);
		if(isset($opt->civil_year))
			$b->where('travaux.civil_year',$opt->civil_year);		
		//ids
		if(isset($opt->ids))
			$b->whereIn('infos.id', $opt->ids);

		$data = $b->orderBy($this->order, $this->direction)->get();
		$this->log();
		return (($data->getNumRows()) ? $data->getResult():FALSE);
	}

	/**
	 * @param mixed $id_travaux 
	 * @return mixed 
	 */
	function Decompte($id_travaux){
		$data=$this->db->table($this->table)->selectSum('nb_participants')->where('id_travaux',$id_travaux)->get();
		$this->log();
		return (($data->getNumRows()) ? $data->getResult()[0]:FALSE);
	}

	/**
	 * @param mixed $id_fam 
	 * @param mixed $id_work 
	 * @return mixed 
	 */
	function IsRegister($id_fam,$id_work){
		$data = $this->db->table($this->table)->select('*')->where('id_travaux', $id_work)->where('id_famille', $id_fam)->get();
		$this->log();
		
		return (($data->getNumRows()) ? $data->getResult()[0]:FALSE);
	}

	/**
	 * @param mixed $id_work id of work
	 * @param bool $count : to have count or data
	 * @return mixed 
	 */
	function GetRegistred($id_work, $count = false){
		$data = $this->db->table($this->table)->select('*')->where('id_travaux', $id_work)->get();
		$this->log();
		if ($count)
			return (($data->getNumRows()) ? $data->getNumRows():FALSE);
		else
			return (($data->getNumRows()) ? $data->getResult():FALSE);
	}

	/**
     * Insère une inscription (ligne `infos`).
     *
     * @param  array $data
     * @return int|false  id généré, ou false en cas d'échec
     */
    function Register($data){
        if (empty($data)) return false;
        $this->db->table($this->table)->insert($data);
        $this->log();
        return $this->db->insertID() ?: false;
    }

    /**
     * Désinscrit une famille d'une session.
     *
     * @param int  $id_fam
     * @param int  $id_work
     * @param bool $only_if_not_validated  refuse la suppression si une unité
     *                                     est déjà validée (cas auto-désinscription)
     * @return int  nombre de lignes effectivement supprimées
     */
    function Unregister($id_fam, $id_work, $only_if_not_validated = true){
        $b = $this->tb()->where('id_famille', (int) $id_fam)
                        ->where('id_travaux', (int) $id_work);
        if ($only_if_not_validated){
            $b->where('nb_unites_valides_effectif', 0);
        }
        $b->delete();
        $this->log();
        return (int) $this->db->affectedRows();
    }

    /**
     * Récupère une inscription par son id.
     *
     * @param  int $id_info
     * @return stdClass|null
     */
    function GetInfoById($id_info){
        $row = $this->db->table($this->table)->where($this->primaryKey, (int) $id_info)->get()->getRow();
        $this->log();
        return $row ?: null;
    }

    /**
     * Récupère une inscription en vérifiant qu'elle appartient bien à
     * la session $id_work (garde-fou contre une falsification de paramètre
     * côté contrôleur).
     *
     * @param  int $id_info
     * @param  int $id_work
     * @return stdClass|null
     */
    function GetInfoForWork($id_info, $id_work){
        $row = $this->db->table($this->table)->where($this->primaryKey, (int) $id_info)->where('id_travaux', (int) $id_work)->get()->getRow();
        $this->log();
        return $row ?: null;
    }
}
?>