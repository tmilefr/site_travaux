<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cantine_controller
 *
 * Gestion des inscriptions des parents à la garde du midi.
 *  - register : vue agenda semaine pour les familles (et consultation admin)
 *  - config   : paramétrage des jours nécessaires et du nombre de parents par jour (admin)
 *
 * @package     WebApp
 * @subpackage  Core
 * @author      NL
 */
class Cantine_controller extends MY_Controller {

    public $CantineConfig_model = null;
    public $CantineInscriptions_model = null;
    public $Familys_model = null;

    public function __construct(){
        parent::__construct();
        $this->_controller_name = 'Cantine_controller';
        $this->_model_name      = 'CantineInscriptions_model';
        $this->_edit_view       = 'edition/Cantine_form';
        $this->_list_view       = 'unique/Cantine_view.php';
        $this->_autorize        = [
            'register'       => true,
            'register_day'   => true,
            'unregister_day' => true,
            'config'         => true,
            'save_config'    => true,
        ];
        $this->_bg_color = 'nicdark_bg_green';
        $this->title = $this->lang->line('GESTION_'.$this->_controller_name);

        $this->init();

        $this->LoadModel('CantineConfig_model');
        $this->LoadModel('CantineInscriptions_model');
        $this->LoadModel('Familys_model');
    }

    public function index(){
        redirect($this->_controller_name.'/register');
    }

    // ---------------------------------------------------------------
    // VUE PARENT : agenda hebdomadaire d'inscription
    // ---------------------------------------------------------------
    public function register($week_offset = 0){
        $week_offset = (int)$week_offset;

        // École : pour une famille connectée, celle de la famille ; sinon toutes
        $ecole = 'B';
        if ($this->acl->getType() == 'fam'){
            $family = $this->Familys_model->GetFamily($this->acl->getUserId());
            if ($family && !empty($family->ecole)) $ecole = $family->ecole;
        }

        // Calcul du lundi de la semaine demandée
        $monday = $this->_getMonday($week_offset);
        $friday = clone $monday;
        $friday->modify('+4 days');

        $civil_year = $this->config->item('civil_year');
        $config   = $this->CantineConfig_model->GetConfig($ecole, $civil_year);
        $inscrits = $this->CantineInscriptions_model->GetByRange(
            $monday->format('Y-m-d'),
            $friday->format('Y-m-d'),
            $ecole
        );

        // Construction des 5 jours de la semaine pour la vue
        $days = [];
        $id_fam = $this->acl->getUserId();
        for($i = 0; $i < 5; $i++){
            $date = clone $monday; $date->modify("+{$i} days");
            $id_day = $i + 1; // 1=Lundi .. 5=Vendredi
            $key = $date->format('Y-m-d');
            $cfg = $config[$id_day];

            $day = new stdClass();
            $day->date        = $key;
            $day->date_fr     = $this->_frDate($date);
            $day->day_label   = $this->lang->line('cantine_day_'.$id_day);
            $day->day_num     = $date->format('j');
            $day->month_fr    = $this->_frMonth($date);
            $day->active      = ((int)$cfg->active === 1);
            $day->nb_slots    = (int)$cfg->nb_slots;
            $day->inscrits    = isset($inscrits[$key]) ? $inscrits[$key] : [];
            $day->nb_inscrits = count($day->inscrits);
            $day->full        = ($day->nb_inscrits >= $day->nb_slots);
            $day->passed      = (strtotime($key) < strtotime(date('Y-m-d')));
            $day->mine        = false;
            foreach($day->inscrits AS $ins){
                if ((int)$ins->id_famille === (int)$id_fam){ $day->mine = true; break; }
            }
            $days[] = $day;
        }

        // Stats
        $stats = new stdClass();
        $stats->active_days = 0; $stats->mine = 0; $stats->open = 0;
        foreach($days AS $d){
            if ($d->active){
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

        $this->_set('view_inprogress','unique/Cantine_controller_register');
        $this->render_view();
    }

    /**
     * Inscription du parent connecté à une date donnée (YYYY-MM-DD).
     */
    public function register_day($date = null){
        $date = $this->_sanitize_date($date);
        if (!$date){ redirect($this->_controller_name.'/register'); }

        if ($this->acl->getType() != 'fam'){
            redirect($this->_controller_name.'/register');
        }

        $family = $this->Familys_model->GetFamily($this->acl->getUserId());
        $ecole = ($family && !empty($family->ecole)) ? $family->ecole : 'B';
        $civil_year = $this->config->item('civil_year');

        // Vérif jour actif + non complet + date future
        $id_day = (int)date('N', strtotime($date)); // 1=Lun .. 7=Dim
        if ($id_day < 1 || $id_day > 5){ redirect($this->_controller_name.'/register'); }
        if (strtotime($date) < strtotime(date('Y-m-d'))){ redirect($this->_controller_name.'/register'); }

        $config = $this->CantineConfig_model->GetConfig($ecole, $civil_year);
        if (empty($config[$id_day]) || !$config[$id_day]->active){
            redirect($this->_controller_name.'/register');
        }
        $nb = $this->CantineInscriptions_model->CountForDate($date, $ecole);
        if ($nb >= $config[$id_day]->nb_slots){
            redirect($this->_controller_name.'/register');
        }

        $this->CantineInscriptions_model->Register($this->acl->getUserId(), $date, $ecole, $civil_year);
        redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($date));
    }

    /**
     * Désinscription du parent connecté pour une date.
     */
    public function unregister_day($date = null){
        $date = $this->_sanitize_date($date);
        if (!$date){ redirect($this->_controller_name.'/register'); }

        if ($this->acl->getType() != 'fam'){
            redirect($this->_controller_name.'/register');
        }
        $family = $this->Familys_model->GetFamily($this->acl->getUserId());
        $ecole = ($family && !empty($family->ecole)) ? $family->ecole : 'B';

        $this->CantineInscriptions_model->Unregister($this->acl->getUserId(), $date, $ecole);
        redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($date));
    }

    // ---------------------------------------------------------------
    // VUE ADMIN : paramétrage des jours
    // ---------------------------------------------------------------
    public function config(){
        if ($this->acl->getType() != 'sys'){
            redirect($this->_controller_name.'/register');
        }

        $ecole = $this->input->get('ecole');
        if (!in_array($ecole, ['B','M','L'])) $ecole = 'B';
        $civil_year = $this->config->item('civil_year');

        $this->data_view['config']     = $this->CantineConfig_model->GetConfig($ecole, $civil_year);
        $this->data_view['ecole']      = $ecole;
        $this->data_view['civil_year'] = $civil_year;

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
                'id_day'   => $d,
                'active'   => $this->input->post('active_'.$d) ? 1 : 0,
                'nb_slots' => (int)$this->input->post('nb_slots_'.$d),
            ];
        }
        $this->CantineConfig_model->SaveConfig($days, $ecole, $civil_year);
        redirect($this->_controller_name.'/config?ecole='.$ecole);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    private function _getMonday($week_offset = 0){
        $d = new DateTime('today');
        $dow = (int)$d->format('N'); // 1=Lun
        $diff = 1 - $dow; // décalage vers lundi de la semaine en cours
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

    private function _sanitize_date($date){
        if (!$date) return null;
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if (!$d || $d->format('Y-m-d') !== $date) return null;
        return $date;
    }

    private function _frMonth(DateTime $d){
        $names = ['janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
        return $names[(int)$d->format('n')-1];
    }

    private function _frDate(DateTime $d){
        return $d->format('j').' '.$this->_frMonth($d);
    }
}
