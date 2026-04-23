<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

/**
 * CantineConfig_model
 *  - configuration des jours de garde du midi (1 ligne par jour de semaine)
 */
class CantineConfig_model extends Core_model {

    function __construct(){
        parent::__construct();
        $this->_set('table' , 'cantine_config');
        $this->_set('key'   , 'id');
        $this->_set('order' , 'id_day');
        $this->_set('direction' , 'asc');
        $this->_set('json'  , 'CantineConfig.json');
    }

    /**
     * Récupère la config pour une école et une année scolaire.
     * Garantit qu'on a toujours 5 lignes (lun->ven), crée celles qui manquent.
     *
     * @param string $ecole
     * @param string $civil_year
     * @return array [ 1 => obj, 2 => obj, ... 5 => obj ] indexé par id_day
     */
    function GetConfig($ecole, $civil_year){
        $rows = $this->db->select('*')
            ->from($this->table)
            ->where('ecole', $ecole)
            ->where('civil_year', $civil_year)
            ->get()->result();

        $by_day = [];
        foreach($rows AS $r){
            $by_day[(int)$r->id_day] = $r;
        }
        for($d=1; $d<=5; $d++){
            if (!isset($by_day[$d])){
                $this->db->insert($this->table, [
                    'id_day'     => $d,
                    'active'     => 0,
                    'nb_slots'   => 0,
                    'ecole'      => $ecole,
                    'civil_year' => $civil_year,
                    'created'    => date('Y-m-d H:i:s'),
                    'updated'    => date('Y-m-d H:i:s'),
                ]);
                $id = $this->db->insert_id();
                $by_day[$d] = (object)[
                    'id'         => $id,
                    'id_day'     => $d,
                    'active'     => 0,
                    'nb_slots'   => 0,
                    'ecole'      => $ecole,
                    'civil_year' => $civil_year,
                ];
            }
        }
        ksort($by_day);
        return $by_day;
    }

    /**
     * Sauvegarde la config des 5 jours.
     *
     * @param array $days  [ ['id_day'=>1,'active'=>1,'nb_slots'=>2], ... ]
     * @param string $ecole
     * @param string $civil_year
     */
    function SaveConfig($days, $ecole, $civil_year){
        foreach($days AS $d){
            $id_day   = (int)$d['id_day'];
            $active   = !empty($d['active']) ? 1 : 0;
            $nb_slots = max(0, min(20, (int)$d['nb_slots']));
            if (!$active) $nb_slots = 0;

            $existing = $this->db->select('id')
                ->from($this->table)
                ->where('id_day', $id_day)
                ->where('ecole', $ecole)
                ->where('civil_year', $civil_year)
                ->get()->row();

            if ($existing){
                $this->db->where('id', $existing->id)->update($this->table, [
                    'active'   => $active,
                    'nb_slots' => $nb_slots,
                    'updated'  => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->insert($this->table, [
                    'id_day'     => $id_day,
                    'active'     => $active,
                    'nb_slots'   => $nb_slots,
                    'ecole'      => $ecole,
                    'civil_year' => $civil_year,
                    'created'    => date('Y-m-d H:i:s'),
                    'updated'    => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
