<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cantine_controller
 *
 * Gestion des inscriptions des parents à la garde du midi.
 *  - register         : liste des sessions cantine à venir, pour s'inscrire (vue parent)
 *  - register_one     : inscription à une session
 *  - unregister_one   : désinscription
 *  - config           : paramétrage des jours + formulaire de génération (admin)
 *  - save_config      : sauvegarde config
 *  - generate         : génère les sessions sur une période donnée
 *
 * Chaque session cantine = une ligne dans `travaux` de type 'can'. Une inscription
 * = une ligne dans `infos` (comme pour les travaux classiques). Le référent valide
 * via l'écran existant `Units_controller/valid`.
 *
 * @package     WebApp
 * @author      NL
 */
class Cantine_controller extends MY_Controller {

    public $CantineConfig_model = null;
    public $CantineGeneration_model = null;
    public $Familys_model = null;
    public $Admwork_model = null;
    public $Infos_model   = null;

    public function __construct(){
        parent::__construct();


        $this->_controller_name = 'Cantine_controller';
        $this->_model_name      = 'CantineConfig_model';
        $this->_edit_view       = 'edition/Cantine_form';
        $this->_list_view       = 'unique/Cantine_view.php';
        $this->_autorize        = [
            'register'       => true,
            'register_one'   => true,
            'unregister_one' => true,
            'config'         => true,
            'save_config'    => true,
            'generate'       => true,
        ];
        $this->_bg_color = 'nicdark_bg_green';
        $this->title = $this->lang->line('GESTION_'.$this->_controller_name);

        $this->init();

        $this->LoadModel('CantineConfig_model');
        $this->LoadModel('CantineGeneration_model');
        $this->LoadModel('Familys_model');
        $this->LoadModel('Admwork_model');
        $this->LoadModel('Infos_model');

        $this->lang->load('cantine');
    }

    public function index(){
        redirect($this->_controller_name.'/register');
    }

    // ---------------------------------------------------------------
    // VUE PARENT : agenda hebdomadaire des sessions cantine
    // ---------------------------------------------------------------
    public function register($week_offset = 0){
        $week_offset = (int)$week_offset;

        $ecole = 'B';
        if ($this->acl->getType() == 'fam'){
            $family = $this->Familys_model->GetFamily($this->acl->getUserId());
            if ($family && !empty($family->ecole)) $ecole = $family->ecole;
        }

        $civil_year = $this->config->item('civil_year');
        $id_fam = $this->acl->getUserId();

        // Lundi → vendredi de la semaine demandée
        $monday = $this->_getMonday($week_offset);
        $friday = clone $monday; $friday->modify('+4 days');

        // Récupère toutes les sessions cantine de la semaine pour l'école du parent
        $schools = ($this->acl->getType() == 'fam') ? ['B', $ecole] : ['B','M','L'];
        $sessions = $this->db->select('*')
            ->from('travaux')
            ->where('type', 'can')
            ->where('statut', 1)
            ->where('archived !=', 1)
            ->where('date_travaux >=', $monday->format('Y-m-d'))
            ->where('date_travaux <=', $friday->format('Y-m-d'))
            ->where('civil_year', $civil_year)
            ->where_in('accespar', $schools)
            ->order_by('date_travaux','ASC')
            ->get()->result();

        // Indexation par date Y-m-d (max 1 session par date par école côté règles,
        // mais on supporte plusieurs au cas où écoles B+M+L cohabitent)
        $by_date = [];
        foreach($sessions AS $s){ $by_date[$s->date_travaux][] = $s; }

        // Inscrits par session (id_travaux)
        $session_ids = array_map(function($s){ return (int)$s->id; }, $sessions);
        $inscrits_by_work = [];
        if (!empty($session_ids)){
            $rows = $this->db->select('i.id, i.id_travaux, i.id_famille, i.nb_unites_valides_effectif, f.nom, f.login')
                ->from('infos i')
                ->join('famille f', 'f.id = i.id_famille', 'left')
                ->where_in('i.id_travaux', $session_ids)
                ->order_by('i.created','ASC')
                ->get()->result();
            foreach($rows AS $r){ $inscrits_by_work[(int)$r->id_travaux][] = $r; }
        }

        // Construction des 5 jours de la semaine
        $days = [];
        for($i = 0; $i < 5; $i++){
            $date = clone $monday; $date->modify("+{$i} days");
            $key = $date->format('Y-m-d');
            $id_day = $i + 1;

            $day = new stdClass();
            $day->date      = $key;
            $day->day_label = $this->lang->line('cantine_day_'.$id_day);
            $day->day_num   = $date->format('j');
            $day->month_fr  = $this->_frMonth($date);
            $day->passed    = (strtotime($key) < strtotime(date('Y-m-d')));
            $day->session   = isset($by_date[$key]) ? $by_date[$key][0] : null;

            if ($day->session){
                $sid = (int)$day->session->id;
                $day->inscrits    = isset($inscrits_by_work[$sid]) ? $inscrits_by_work[$sid] : [];
                $day->nb_inscrits = count($day->inscrits);
                $day->nb_slots    = (int)$day->session->nb_inscrits_max;
                $day->nb_units    = (float)$day->session->nb_units;
                $day->full        = ($day->nb_inscrits >= $day->nb_slots);
                $day->mine        = false;
                $day->my_validated= false;
                foreach($day->inscrits AS $ins){
                    if ((int)$ins->id_famille === (int)$id_fam){
                        $day->mine = true;
                        if ((float)$ins->nb_unites_valides_effectif > 0) $day->my_validated = true;
                        break;
                    }
                }
            }
            $days[] = $day;
        }

        // Stats
        $stats = new stdClass();
        $stats->active_days = 0; $stats->mine = 0; $stats->open = 0;
        foreach($days AS $d){
            if ($d->session){
                $stats->active_days++;
                $stats->open += max(0, $d->nb_slots - $d->nb_inscrits);
                if ($d->mine) $stats->mine++;
            }
        }

        $this->data_view['days']         = $days;
        $this->data_view['week_offset']  = $week_offset;
        $this->data_view['week_label']   = 'Semaine du '.$this->_frDate($monday).' au '.$this->_frDate($friday);
        $this->data_view['stats']        = $stats;
        $this->data_view['is_admin']     = ($this->acl->getType() == 'sys');
        $this->data_view['id_fam']       = $id_fam;
        $this->data_view['ecole']        = $ecole;
        $this->data_view['can_register'] = ($this->acl->getType() == 'fam');

        $this->_set('view_inprogress','unique/Cantine_controller_register');
        $this->render_view();
    }

    /**
     * Inscription à une session cantine donnée (id du travail).
     */
    public function register_one($id_work = null){
        $id_work = (int)$id_work;
        if (!$id_work || $this->acl->getType() != 'fam'){
            redirect($this->_controller_name.'/register');
        }

        $id_fam = $this->acl->getUserId();

        $work = $this->db->from('travaux')->where('id', $id_work)->where('type','can')->get()->row();
        if (!$work){ redirect($this->_controller_name.'/register'); }
        if (strtotime($work->date_travaux) < strtotime(date('Y-m-d'))){
            redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($work->date_travaux));
        }
        if ($this->Infos_model->IsRegister($id_fam, $id_work)){
            redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($work->date_travaux));
        }
        $nb = (int) $this->db->from('infos')->where('id_travaux', $id_work)->count_all_results();
        if ($nb >= (int)$work->nb_inscrits_max){
            redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($work->date_travaux));
        }

        // Création de l'inscription (ligne infos) = unité à valider
        $this->db->insert('infos', [
            'id_famille'                 => $id_fam,
            'id_travaux'                 => $id_work,
            'heure_debut_prevue'         => $work->heure_deb_trav,
            'heure_fin_prevue'           => $work->heure_fin_trav,
            'nb_unites_valides'          => (float)$work->nb_units,
            'nb_unites_valides_effectif' => 0,
            'nb_participants'            => 1,
            'type_participant'           => 'Mr',
            'type_session'               => 1,
            'civil_year'                 => $this->config->item('civil_year'),
            'created'                    => date('Y-m-d H:i:s'),
            'updated'                    => date('Y-m-d H:i:s'),
        ]);

        redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($work->date_travaux));
    }

    /**
     * Désinscription d'une session (refusée si unité déjà validée).
     */
    public function unregister_one($id_work = null){
        $id_work = (int)$id_work;
        if (!$id_work || $this->acl->getType() != 'fam'){
            redirect($this->_controller_name.'/register');
        }

        $id_fam = $this->acl->getUserId();
        $work = $this->db->from('travaux')->where('id', $id_work)->get()->row();

        $this->db->where('id_travaux', $id_work)
            ->where('id_famille', $id_fam)
            ->where('nb_unites_valides_effectif', 0)
            ->delete('infos');

        $offset = $work ? $this->_weekOffsetFor($work->date_travaux) : 0;
        redirect($this->_controller_name.'/register/'.$offset);
    }

    // ---------------------------------------------------------------
    // VUE ADMIN : paramétrage des jours + génération
    // ---------------------------------------------------------------
    public function config(){
        if ($this->acl->getType() != 'sys'){
            redirect($this->_controller_name.'/register');
        }

        $ecole = $this->input->get('ecole');
        if (!in_array($ecole, ['B','M','L'])) $ecole = 'M';
        $civil_year = $this->config->item('civil_year');

        // Mois affiché dans l'agenda (?ym=YYYY-MM), par défaut mois courant
        $ym = $this->input->get('ym');
        if (!$ym || !preg_match('/^\d{4}-\d{2}$/', $ym)){
            $ym = date('Y-m');
        }

        $this->data_view['config']      = $this->CantineConfig_model->GetConfig($ecole, $civil_year);
        $this->data_view['ecole']       = $ecole;
        $this->data_view['civil_year']  = $civil_year;
        $this->data_view['referents']   = $this->_getReferents();
        $this->data_view['generations'] = $this->CantineGeneration_model->GetLastGenerations($ecole, $civil_year, 5);
        $this->data_view['nb_upcoming'] = $this->CantineGeneration_model->CountUpcoming($ecole, $civil_year);

        // Agenda mensuel
        $first = $ym.'-01';
        $this->data_view['agenda_ym']        = $ym;
        $this->data_view['agenda_prev_ym']   = date('Y-m', strtotime($first.' -1 month'));
        $this->data_view['agenda_next_ym']   = date('Y-m', strtotime($first.' +1 month'));
        $this->data_view['agenda_label']     = $this->_frMonthLabel($first);
        $this->data_view['agenda_sessions']  = $this->CantineGeneration_model->GetSessionsByDate(
            $ecole, $civil_year, $first
        );

        // Dates par défaut pour le formulaire de génération : aujourd'hui -> fin d'année scolaire
        $this->data_view['default_date_deb'] = date('Y-m-d');
        $this->data_view['default_date_fin'] = $this->_endOfSchoolYear($civil_year);

        $this->_set('view_inprogress','unique/Cantine_controller_config');
        $this->render_view();
    }

    public function save_config(){
        if ($this->acl->getType() != 'sys'){
            redirect($this->_controller_name.'/register');
        }
        $ecole = $this->input->post('ecole');
        if (!in_array($ecole, ['B','M','L'])) $ecole = 'B';
        $civil_year = $this->config->item('civil_year');

        $days = [];
        for($d = 1; $d <= 5; $d++){
            $days[] = [
                'id_day'      => $d,
                'active'      => $this->input->post('active_'.$d) ? 1 : 0,
                'nb_slots'    => (int)$this->input->post('nb_slots_'.$d),
                'nb_units'    => (float)$this->input->post('nb_units_'.$d),
                'id_referent' => $this->input->post('id_referent_'.$d),
                'heure_deb'   => $this->input->post('heure_deb_'.$d),
                'heure_fin'   => $this->input->post('heure_fin_'.$d),
            ];
        }
        $this->CantineConfig_model->SaveConfig($days, $ecole, $civil_year);
        redirect($this->_controller_name.'/config?ecole='.$ecole);
    }

    /**
     * Génère les sessions cantine pour une période donnée, selon la config.
     * Deux modes de période :
     *   - school_end : aujourd'hui → 31/05 de l'année scolaire (par défaut)
     *   - custom     : dates saisies par l'admin
     */
    public function generate(){
        if ($this->acl->getType() != 'sys'){
            redirect($this->_controller_name.'/register');
        }

        $ecole = $this->input->post('ecole');
        if (!in_array($ecole, ['B','M','L'])) $ecole = 'B';
        $civil_year = $this->config->item('civil_year');

        $mode = $this->input->post('period_mode');
        if ($mode === 'custom'){
            $date_deb = $this->_sanitize_date($this->input->post('date_deb'));
            $date_fin = $this->_sanitize_date($this->input->post('date_fin'));
        } else {
            // Mode school_end par défaut : aujourd'hui → 31/05 de l'année scolaire
            $date_deb = date('Y-m-d');
            $date_fin = $this->_endOfSchoolYear($civil_year);
        }

        if (!$date_deb || !$date_fin){
            redirect($this->_controller_name.'/config?ecole='.$ecole);
        }

        $config = $this->CantineConfig_model->GetConfig($ecole, $civil_year);
        $this->CantineGeneration_model->Generate($date_deb, $date_fin, $ecole, $config, $civil_year);

        redirect($this->_controller_name.'/config?ecole='.$ecole);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Liste des référents (classif reftra/RT) pour le select admin.
     */
    private function _getReferents(){
        $rows = $this->db->select('tr.id, CONCAT_WS(" ", gr.short, gm.name, gm.surname) AS title', false)
            ->from('trombi tr')
            ->join('groupes_member gm', 'tr.ref = gm.id', 'left')
            ->join('groupes gr', 'tr.id_grp = gr.id', 'left')
            ->where_in('tr.classif', ['reftra','RT'])
            ->order_by('gm.name','ASC')
            ->get()->result();
        $out = [];
        foreach($rows AS $r){ $out[$r->id] = $r->title; }
        return $out;
    }

    /**
     * Retourne la date de fin de l'année scolaire en cours (31 mai de l'année de fin).
     * civil_year format : "2025-2026" → "2026-05-31"
     */
    private function _endOfSchoolYear($civil_year){
        if (preg_match('/^\d{4}-(\d{4})$/', $civil_year, $m)){
            return $m[1].'-05-31';
        }
        return date('Y-m-d', strtotime('+6 months'));
    }

    private function _sanitize_date($date){
        if (!$date) return null;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) return null;
        return $date;
    }

    // ---------------------------------------------------------------
    // Helpers date (agenda hebdo)
    // ---------------------------------------------------------------
    private function _getMonday($week_offset = 0){
        $d = new DateTime('today');
        $dow = (int)$d->format('N');
        $diff = 1 - $dow;
        $d->modify(($diff >= 0 ? '+' : '').$diff.' days');
        if ($week_offset !== 0){
            $d->modify(($week_offset >= 0 ? '+' : '').$week_offset.' weeks');
        }
        return $d;
    }

    private function _weekOffsetFor($date){
        $target = new DateTime($date);
        $monday_target = clone $target;
        $dow = (int)$target->format('N');
        $monday_target->modify((1-$dow).' days');
        $monday_now = $this->_getMonday(0);
        $interval = $monday_now->diff($monday_target);
        $days = (int)$interval->format('%r%a');
        return (int) round($days / 7);
    }

    private function _frMonth(DateTime $d){
        $names = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
        return $names[(int)$d->format('n')-1];
    }

    private function _frDate(DateTime $d){
        return $d->format('j').' '.$this->_frMonth($d);
    }

    private function _frWeekday(DateTime $d){
        $names = [1=>'Lundi',2=>'Mardi',3=>'Mercredi',4=>'Jeudi',5=>'Vendredi',6=>'Samedi',7=>'Dimanche'];
        return $names[(int)$d->format('N')];
    }

        /**
     * Retourne un libellé "Mois Année" en français, ex: "Avril 2026".
     */
    private function _frMonthLabel($ymd){
        $months = [
            1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',5=>'Mai',6=>'Juin',
            7=>'Juillet',8=>'Août',9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre',
        ];
        $t = strtotime($ymd);
        return $months[(int)date('n', $t)].' '.date('Y', $t);
    }
}
