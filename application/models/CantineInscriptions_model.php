<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

/**
 * CantineInscriptions_model
 *  - inscriptions des familles à la garde du midi
 */
class CantineInscriptions_model extends Core_model {

    function __construct(){
        parent::__construct();
        $this->_set('table' , 'cantine_inscriptions');
        $this->_set('key'   , 'id');
        $this->_set('order' , 'date_garde');
        $this->_set('direction' , 'asc');
        $this->_set('json'  , 'CantineInscriptions.json');
    }

    /**
     * Retourne les inscriptions entre 2 dates (incluses) pour une école.
     * Jointure avec la famille pour récupérer le nom.
     *
     * @return array indexé par date Y-m-d, chaque valeur = array d'objets {id,id_famille,nom}
     */
    function GetByRange($date_start, $date_end, $ecole){
        $rows = $this->db->select('ci.id, ci.date_garde, ci.id_famille, f.nom, f.login')
            ->from($this->table.' ci')
            ->join('famille f', 'f.id = ci.id_famille', 'left')
            ->where('ci.date_garde >=', $date_start)
            ->where('ci.date_garde <=', $date_end)
            ->where('ci.ecole', $ecole)
            ->order_by('ci.date_garde','ASC')
            ->order_by('ci.created','ASC')
            ->get()->result();

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
        return (int) $this->db->from($this->table)
            ->where('date_garde', $date)
            ->where('ecole', $ecole)
            ->count_all_results();
    }

    /**
     * Vérifie si la famille est déjà inscrite pour cette date.
     */
    function IsRegistered($id_famille, $date, $ecole){
        return (bool) $this->db->from($this->table)
            ->where('date_garde', $date)
            ->where('id_famille', $id_famille)
            ->where('ecole', $ecole)
            ->count_all_results();
    }

    /**
     * Ajoute une inscription.
     */
    function Register($id_famille, $date, $ecole, $civil_year){
        if ($this->IsRegistered($id_famille, $date, $ecole)) return false;
        $this->db->insert($this->table, [
            'date_garde' => $date,
            'id_famille' => $id_famille,
            'ecole'      => $ecole,
            'civil_year' => $civil_year,
            'created'    => date('Y-m-d H:i:s'),
            'updated'    => date('Y-m-d H:i:s'),
        ]);
        return $this->db->insert_id();
    }

    /**
     * Désinscrit une famille d'une date.
     */
    function Unregister($id_famille, $date, $ecole){
        $this->db->where('date_garde', $date)
            ->where('id_famille', $id_famille)
            ->where('ecole', $ecole)
            ->delete($this->table);
        return $this->db->affected_rows();
    }
}
