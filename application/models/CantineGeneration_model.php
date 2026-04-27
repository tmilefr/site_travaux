<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

/**
 * CantineGeneration_model
 *  - génération en masse de sessions cantine sur une période donnée
 *  - chaque session générée = 1 ligne dans la table `travaux` de type 'can'
 *  - historique des générations dans `cantine_generation`
 */
class CantineGeneration_model extends Core_model {

    function __construct(){
        parent::__construct();
        $this->_set('table' , 'cantine_generation');
        $this->_set('key'   , 'id');
        $this->_set('order' , 'created');
        $this->_set('direction' , 'desc');
        $this->_set('json'  , 'CantineGeneration.json');
    }

    /**
     * Génère les sessions cantine entre 2 dates pour une école, selon la config.
     *
     * @param string $date_deb   format Y-m-d
     * @param string $date_fin   format Y-m-d
     * @param string $ecole      'B', 'M' ou 'L'
     * @param array  $config     config par jour (1..5) venant de CantineConfig_model::GetConfig()
     * @param string $civil_year année scolaire
     * @return stdClass  { nb_created, nb_skipped, first_date, last_date }
     */
    function Generate($date_deb, $date_fin, $ecole, $config, $civil_year){
        $result = new stdClass();
        $result->nb_created = 0;
        $result->nb_skipped = 0;
        $result->first_date = null;
        $result->last_date  = null;

        $start = new DateTime($date_deb);
        $end   = new DateTime($date_fin);
        if ($end < $start) return $result;

        $cursor = clone $start;
        while($cursor <= $end){
            $id_day = (int)$cursor->format('N'); // 1=Lundi .. 7=Dimanche
            $date   = $cursor->format('Y-m-d');

            // Uniquement du lundi au vendredi, et uniquement si le jour est actif en config
            if ($id_day >= 1 && $id_day <= 5
                && isset($config[$id_day])
                && (int)$config[$id_day]->active === 1
                && (int)$config[$id_day]->nb_slots > 0){

                $cfg = $config[$id_day];

                // Vérification qu'il n'existe pas déjà
                $existing = $this->db->select('id')
                    ->from('travaux')
                    ->where('date_travaux', $date)
                    ->where('type', 'can')
                    ->where('accespar', $ecole)
                    ->where('civil_year', $civil_year)
                    ->get()->row();

                if ($existing){
                    $result->nb_skipped++;
                } else {
                    $this->db->insert('travaux', [
                        'date_travaux'     => $date,
                        'heure_deb_trav'   => !empty($cfg->heure_deb) ? $cfg->heure_deb : '11:45',
                        'heure_fin_trav'   => !empty($cfg->heure_fin) ? $cfg->heure_fin : '13:30',
                        'type'             => 'can',
                        'nb_units'         => (float)$cfg->nb_units,
                        'titre'            => 'Cantine - '.$this->_frWeekday($cursor).' '.$cursor->format('d/m/Y'),
                        'description'     => 'Garde du midi à l\'école. '.(int)$cfg->nb_slots.' parent(s) recherché(s).',
                        'nb_inscrits_max'  => (int)$cfg->nb_slots,
                        'referent_travaux' => !empty($cfg->id_referent) ? $cfg->id_referent : '',
                        'ecole'            => $ecole,
                        'accespar'         => $ecole,
                        'type_session'     => 1,
                        'statut'           => 1,
                        'archived'         => 0,
                        'civil_year'       => $civil_year,
                        'created'          => date('Y-m-d H:i:s'),
                        'updated'          => date('Y-m-d H:i:s'),
                    ]);
                    $result->nb_created++;
                    if (!$result->first_date) $result->first_date = $date;
                    $result->last_date = $date;
                }
            }
            $cursor->modify('+1 day');
        }

        // Log de la génération
        $this->db->insert($this->table, [
            'date_deb'   => $date_deb,
            'date_fin'   => $date_fin,
            'ecole'      => $ecole,
            'civil_year' => $civil_year,
            'nb_created' => $result->nb_created,
            'nb_skipped' => $result->nb_skipped,
            'created'    => date('Y-m-d H:i:s'),
        ]);

        return $result;
    }

    /**
     * Récupère les dernières générations effectuées, pour affichage dans la vue config.
     */
    function GetLastGenerations($ecole, $civil_year, $limit = 5){
        return $this->db->select('*')
            ->from($this->table)
            ->where('ecole', $ecole)
            ->where('civil_year', $civil_year)
            ->order_by('created','DESC')
            ->limit($limit)
            ->get()->result();
    }

    /**
     * Compte les sessions cantine à venir pour une école (non passées, non archivées).
     */
    function CountUpcoming($ecole, $civil_year){
        return (int) $this->db->from('travaux')
            ->where('type', 'can')
            ->where('accespar', $ecole)
            ->where('civil_year', $civil_year)
            ->where('date_travaux >=', date('Y-m-d'))
            ->where('archived !=', 1)
            ->count_all_results();
    }

    private function _frWeekday(DateTime $d){
        $names = [1=>'lundi',2=>'mardi',3=>'mercredi',4=>'jeudi',5=>'vendredi',6=>'samedi',7=>'dimanche'];
        return $names[(int)$d->format('N')];
    }
}
