<?php

namespace App\Models;

/**
 * CantineConfig_model
 *  - configuration des jours de garde du midi (1 ligne par jour de semaine)
 */
class CantineConfig_model extends Core_model {

	protected $table = 'cantine_config';
	protected $primaryKey = 'id';
	protected $order = 'id_day';
	protected $direction = 'asc';
	protected $json = 'CantineConfig.json';



    /**
     * Récupère la config pour une école et une année scolaire.
     * Garantit qu'on a toujours 5 lignes (lun->ven), crée celles qui manquent.
     *
     * @param string $ecole
     * @param string $civil_year
     * @return array [ 1 => obj, 2 => obj, ... 5 => obj ] indexé par id_day
     */
    function GetConfig($ecole, $civil_year){
        $rows = $this->db->table($this->table)->select('*')->where('ecole', $ecole)->where('civil_year', $civil_year)->get()->getResult();

        $by_day = [];
        foreach($rows AS $r){
            $by_day[(int)$r->id_day] = $r;
        }
        for($d=1; $d<=5; $d++){
            if (!isset($by_day[$d])){
                $this->db->table($this->table)->insert([
                    'id_day'      => $d,
                    'active'      => 0,
                    'nb_slots'    => 0,
                    'nb_units'    => 1,
                    'id_referent' => null,
                    'heure_deb'   => '11:45',
                    'heure_fin'   => '13:30',
                    'ecole'       => $ecole,
                    'civil_year'  => $civil_year,
                    'created'     => date('Y-m-d H:i:s'),
                    'updated'     => date('Y-m-d H:i:s'),
                ]);
                $id = $this->db->insertID();
                $by_day[$d] = (object)[
                    'id'          => $id,
                    'id_day'      => $d,
                    'active'      => 0,
                    'nb_slots'    => 0,
                    'nb_units'    => 1,
                    'id_referent' => null,
                    'heure_deb'   => '11:45',
                    'heure_fin'   => '13:30',
                    'ecole'       => $ecole,
                    'civil_year'  => $civil_year,
                ];
            }
        }
        ksort($by_day);
        return $by_day;
    }

    /**
     * Sauvegarde la config des 5 jours.
     *
     * @param array $days  [ ['id_day'=>1,'active'=>1,'nb_slots'=>2,'nb_units'=>1,'id_referent'=>12,'heure_deb'=>'11:45','heure_fin'=>'13:30'], ... ]
     * @param string $ecole
     * @param string $civil_year
     */
    function SaveConfig($days, $ecole, $civil_year){
        foreach($days AS $d){
            $id_day   = (int)$d['id_day'];
            $active   = !empty($d['active']) ? 1 : 0;
            $nb_slots = max(0, min(20, (int)$d['nb_slots']));
            $nb_units = max(0, (float)$d['nb_units']);
            if (!$active) $nb_slots = 0;

            $payload = [
                'active'      => $active,
                'nb_slots'    => $nb_slots,
                'nb_units'    => $nb_units,
                'id_referent' => !empty($d['id_referent']) ? $d['id_referent'] : null,
                'heure_deb'   => !empty($d['heure_deb']) ? $d['heure_deb'] : '11:45',
                'heure_fin'   => !empty($d['heure_fin']) ? $d['heure_fin'] : '13:30',
                'updated'     => date('Y-m-d H:i:s'),
            ];

            $existing = $this->db->table($this->table)->select('id')->where('id_day', $id_day)->where('ecole', $ecole)->where('civil_year', $civil_year)->get()->getRow();

            if ($existing){
                $this->db->table($this->table)->where('id', $existing->id)->update($payload);
            } else {
                $payload['id_day']     = $id_day;
                $payload['ecole']      = $ecole;
                $payload['civil_year'] = $civil_year;
                $payload['created']    = date('Y-m-d H:i:s');
                $this->db->table($this->table)->insert($payload);
            }
        }
    }
}
