<?php

namespace App\Models;

/**
 * CantineInscriptions_model
 *  - inscriptions des familles à la garde du midi
 */
class CantineInscriptions_model extends Core_model {

	protected $table = 'cantine_inscriptions';
	protected $primaryKey = 'id';
	protected $order = 'date_garde';
	protected $direction = 'asc';
	protected $json = 'CantineInscriptions.json';



    /**
     * Retourne les inscriptions entre 2 dates (incluses) pour une école.
     * Jointure avec la famille pour récupérer le nom.
     *
     * @return array indexé par date Y-m-d, chaque valeur = array d'objets {id,id_famille,nom}
     */
    function GetByRange($date_start, $date_end, $ecole){
        $rows = $this->db->table($this->table.' ci')->select('ci.id, ci.date_garde, ci.id_famille, f.nom, f.login')->join('famille f', 'f.id = ci.id_famille', 'left')->where('ci.date_garde >=', $date_start)->where('ci.date_garde <=', $date_end)->where('ci.ecole', $ecole)->orderBy('ci.date_garde','ASC')->orderBy('ci.created','ASC')->get()->getResult();

        $by_date = [];
        foreach($rows AS $r){
            $by_date[$r->date_garde][] = $r;
        }
        return $by_date;
    }

    /**
     * Compte les inscrits pour une date donnée.
     */
    function CountForDate($date, $ecole){
        return (int) $this->db->table($this->table)->where('date_garde', $date)->where('ecole', $ecole)->countAllResults();
    }

    /**
     * Vérifie si la famille est déjà inscrite pour cette date.
     */
    function IsRegistered($id_famille, $date, $ecole){
        return (bool) $this->db->table($this->table)->where('date_garde', $date)->where('id_famille', $id_famille)->where('ecole', $ecole)->countAllResults();
    }

    /**
     * Récupère l'inscription d'une famille pour une date (ou null).
     */
    function GetOne($id_famille, $date, $ecole){
        return $this->db->table($this->table)->where('date_garde', $date)->where('id_famille', $id_famille)->where('ecole', $ecole)->get()->getRow();
    }

    /**
     * Ajoute une inscription.
     */
    function Register($id_famille, $date, $ecole, $civil_year, $id_info = null, $id_travaux = null){
        if ($this->IsRegistered($id_famille, $date, $ecole)) return false;
        $this->db->table($this->table)->insert([
            'date_garde' => $date,
            'id_famille' => $id_famille,
            'ecole'      => $ecole,
            'id_info'    => $id_info,
            'id_travaux' => $id_travaux,
            'civil_year' => $civil_year,
            'created'    => date('Y-m-d H:i:s'),
            'updated'    => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insertID();
    }

    /**
     * Désinscrit une famille d'une date.
     */
    function Unregister($id_famille, $date, $ecole){
        $this->db->table($this->table)->where('date_garde', $date)->where('id_famille', $id_famille)->where('ecole', $ecole)->delete();
        return $this->db->affectedRows();
    }
}
