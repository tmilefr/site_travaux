<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cantine_controller
 *
 * Gestion des inscriptions des parents à la garde du midi.
 *  - register : vue agenda semaine pour les familles (et consultation admin)
 *  - config   : paramétrage des jours nécessaires et du nombre de parents par jour (admin)
 *
 * Chaque inscription crée :
 *   - un pseudo-travail de type 'can' (Cantine) à cette date s'il n'existe pas,
 *   - une ligne dans `infos` avec nb_unites_valides > 0 et nb_unites_valides_effectif = 0,
 *   - puis le référent configuré valide l'unité via l'écran existant Units_controller/valid.
 *
 * @package     WebApp
 * @author      NL
 */
class Cantine_controller extends MY_Controller {

    public $CantineConfig_model = null;
    public $CantineInscriptions_model = null;
    public $Familys_model = null;
    public $Admwork_model = null;
    public $Infos_model   = null;
    public $Trombi_model  = null;

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
        $this->LoadModel('Admwork_model');
        $this->LoadModel('Infos_model');
        $this->LoadModel('Trombi_model');
    }

    public function index(){
        redirect($this->_controller_name.'/register');
    }

    // ---------------------------------------------------------------
    // VUE PARENT : agenda hebdomadaire d'inscription
    // ---------------------------------------------------------------
    public function register($week_offset = 0){
        $week_offset = (int)$week_offset;

        $ecole = 'B';
        if ($this->acl->getType() == 'fam'){
            $family = $this->Familys_model->GetFamily($this->acl->getUserId());
            if ($family && !empty($family->ecole)) $ecole = $family->ecole;
        }

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

        $days = [];
        $id_fam = $this->acl->getUserId();
        for($i = 0; $i < 5; $i++){
            $date = clone $monday; $date->modify("+{$i} days");
            $id_day = $i + 1;
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
            $day->nb_units    = (float)$cfg->nb_units;
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
     * Crée la ligne dans infos pour générer une unité à valider.
     */
    public function register_day($date = null){
        $date = $this->_sanitize_date($date);
        if (!$date){ redirect($this->_controller_name.'/register'); }

        if ($this->acl->getType() != 'fam'){
            redirect($this->_controller_name.'/register');
        }

        $id_fam = $this->acl->getUserId();
        $family = $this->Familys_model->GetFamily($id_fam);
        $ecole = ($family && !empty($family->ecole)) ? $family->ecole : 'B';
        $civil_year = $this->config->item('civil_year');

        $id_day = (int)date('N', strtotime($date));
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
        if ($this->CantineInscriptions_model->IsRegistered($id_fam, $date, $ecole)){
            redirect($this->_controller_name.'/register');
        }

        // 1) Créer (ou récupérer) le pseudo-travail pour cette date de cantine
        $id_travaux = $this->_ensureWork($date, $ecole, $civil_year, $config[$id_day]);

        // 2) Créer la ligne infos (= unité à valider)
        $id_info = $this->_createInfoUnit($id_fam, $id_travaux, $config[$id_day], $family);

        // 3) Créer l'inscription cantine en liant les deux
        $this->CantineInscriptions_model->Register(
            $id_fam, $date, $ecole, $civil_year, $id_info, $id_travaux
        );

        redirect($this->_controller_name.'/register/'.$this->_weekOffsetFor($date));
    }

    /**
     * Désinscription du parent connecté pour une date.
     * Retire aussi la ligne infos associée (si l'unité n'est pas déjà validée).
     */
    public function unregister_day($date = null){
        $date = $this->_sanitize_date($date);
        if (!$date){ redirect($this->_controller_name.'/register'); }
        if ($this->acl->getType() != 'fam'){
            redirect($this->_controller_name.'/register');
        }

        $id_fam = $this->acl->getUserId();
        $family = $this->Familys_model->GetFamily($id_fam);
        $ecole = ($family && !empty($family->ecole)) ? $family->ecole : 'B';

        // Récupérer l'inscription avec les ids liés avant suppression
        $ins = $this->CantineInscriptions_model->GetOne($id_fam, $date, $ecole);

        // Ne pas permettre la désinscription si l'unité a déjà été validée
        $locked = false;
        if ($ins && !empty($ins->id_info)){
            $info = $this->db->select('nb_unites_valides_effectif')
                ->from('infos')->where('id', $ins->id_info)->get()->row();
            if ($info && (float)$info->nb_unites_valides_effectif > 0){
                $locked = true;
            }
        }

        if (!$locked){
            // Supprimer la ligne infos si elle existe et n'est pas validée
            if ($ins && !empty($ins->id_info)){
                $this->db->where('id', $ins->id_info)
                    ->where('nb_unites_valides_effectif', 0)
                    ->delete('infos');
            }
            $this->CantineInscriptions_model->Unregister($id_fam, $date, $ecole);

            // Si plus personne n'est inscrit sur cette date, supprimer le pseudo-travail
            if ($ins && !empty($ins->id_travaux)){
                $still = $this->db->from('cantine_inscriptions')
                    ->where('id_travaux', $ins->id_travaux)
                    ->count_all_results();
                $has_infos = $this->db->from('infos')
                    ->where('id_travaux', $ins->id_travaux)
                    ->count_all_results();
                if ($still == 0 && $has_infos == 0){
                    $this->db->where('id', $ins->id_travaux)->delete('travaux');
                }
            }
        }

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
        $this->data_view['referents']  = $this->_getReferents();

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

    // ---------------------------------------------------------------
    // Helpers : intégration au système unités/travaux existant
    // ---------------------------------------------------------------

    /**
     * Crée (ou récupère) le pseudo-travail cantine pour une date + école donnée.
     * Le titre est normalisé pour pouvoir le retrouver : 'Cantine YYYY-MM-DD'.
     *
     * @return int id du travail
     */
    private function _ensureWork($date, $ecole, $civil_year, $cfg){
        $titre = 'Cantine '.$date;

        // Recherche d'un travail existant pour cette date, cette école, de type 'can'
        $existing = $this->db->select('id')
            ->from('travaux')
            ->where('date_travaux', $date)
            ->where('type', 'can')
            ->where('accespar', $ecole)
            ->where('civil_year', $civil_year)
            ->get()->row();

        if ($existing){ return (int)$existing->id; }

        // Création
        $this->db->insert('travaux', [
            'date_travaux'     => $date,
            'heure_deb_trav'   => !empty($cfg->heure_deb) ? $cfg->heure_deb : '11:45',
            'heure_fin_trav'   => !empty($cfg->heure_fin) ? $cfg->heure_fin : '13:30',
            'type'             => 'can',
            'nb_units'         => (float)$cfg->nb_units,
            'titre'            => $titre,
            'description'      => 'Garde du midi - inscription automatique depuis l\'agenda cantine.',
            'nb_inscrits_max'  => (int)$cfg->nb_slots,
            'referent_travaux' => !empty($cfg->id_referent) ? $cfg->id_referent : '',
            'ecole'            => $ecole,
            'accespar'         => $ecole,
            'type_session'     => 1,     // 1 = Horaire (cohérent avec travaux existants)
            'statut'           => 1,     // 1 = publié
            'archived'         => 0,
            'civil_year'       => $civil_year,
            'created'          => date('Y-m-d H:i:s'),
            'updated'          => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    /**
     * Crée une ligne dans `infos` : ça génère une unité à valider pour la famille.
     *  - nb_unites_valides          = nb_units du jour (unité déclarée par le parent)
     *  - nb_unites_valides_effectif = 0 (pas encore validée par le référent)
     *
     * @return int id de l'info
     */
    private function _createInfoUnit($id_fam, $id_travaux, $cfg, $family){
        $this->db->insert('infos', [
            'id_famille'                 => $id_fam,
            'id_travaux'                 => $id_travaux,
            'heure_debut_prevue'         => !empty($cfg->heure_deb) ? $cfg->heure_deb : '11:45',
            'heure_fin_prevue'           => !empty($cfg->heure_fin) ? $cfg->heure_fin : '13:30',
            'nb_unites_valides'          => (float)$cfg->nb_units,
            'nb_unites_valides_effectif' => 0,
            'nb_participants'            => 1,
            'type_participant'           => 'Mr',
            'type_session'               => 1,
            'civil_year'                 => $this->config->item('civil_year'),
            'created'                    => date('Y-m-d H:i:s'),
            'updated'                    => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    /**
     * Récupère la liste des référents possibles (classif reftra/RT) pour le select admin.
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

    // ---------------------------------------------------------------
    // Helpers date
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
