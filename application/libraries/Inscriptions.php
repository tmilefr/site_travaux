<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Inscriptions
 *
 * Service centralisé pour la gestion des inscriptions et désinscriptions
 * des familles aux sessions (travaux classiques + cantine).
 *
 * ## Pourquoi cette librairie ?
 *
 * Avant : la logique d'inscription était dupliquée et mélangée à l'accès
 * base de données dans les contrôleurs (Cantine_controller, Admwork_controller).
 * Chaque cas appelait directement `$this->db->insert/delete`, recodait les
 * mêmes vérifications (date passée, capacité, déjà inscrit, déjà validé),
 * et déclenchait ses notifications de son côté. Difficile à tester, difficile
 * à faire évoluer (par ex. : ajouter un nouveau type de session).
 *
 * Après : un seul point d'entrée, des codes de retour standardisés, plus
 * aucun `$this->db->...` dans les contrôleurs concernés. Les modèles portent
 * les requêtes, la librairie porte la logique métier, les contrôleurs
 * orchestrent et choisissent la vue / redirection.
 *
 * ## Convention de retour
 *
 * Toutes les méthodes publiques renvoient un stdClass uniforme :
 *
 *   ->success  (bool)         true si l'opération a abouti
 *   ->code     (string)       code machine : self::OK, WORK_NOT_FOUND, ...
 *   ->message  (string)       message i18n prêt à afficher (lang ou fallback)
 *   ->id_info  (int|null)     id de la ligne `infos` créée (registerSelf/ByRef)
 *   ->id_fam   (int|null)     id_famille concerné
 *   ->work     (stdClass|null) ligne `travaux` chargée pendant l'opération
 *
 * ## Notifications
 *
 * Les notifications référent (RefNotifier) sont déclenchées automatiquement
 * pour les opérations "self" (registerSelf, unregisterSelf). Le mode gestion
 * (registerByRef, unregisterByRef) ne notifie pas — le référent étant
 * lui-même à l'origine de l'action.
 *
 * unregisterByInfoId reçoit un drapeau $is_self pour distinguer une
 * désinscription famille d'une suppression admin (cas hérité de
 * Admwork_controller::delete_registration).
 *
 * ## Usage type
 *
 *   $this->load->library('Inscriptions');
 *
 *   // -- Inscription famille classique --
 *   $r = $this->inscriptions->registerSelf($id_work, $id_fam);
 *   if ($r->success) { ... } else { $this->session->set_flashdata('error', $r->message); }
 *
 *   // -- Inscription cantine (type forcé en sécurité) --
 *   $r = $this->inscriptions->registerSelf($id_work, $id_fam, [], 'can');
 *
 *   // -- Désinscription famille --
 *   $r = $this->inscriptions->unregisterSelf($id_work, $id_fam);
 *
 *   // -- Inscription par référent (mode gestion) --
 *   $r = $this->inscriptions->registerByRef($id_work, $id_fam, 'Mr');
 *
 *   // -- Désinscription par référent (par id_info, avec garde-fou) --
 *   $r = $this->inscriptions->unregisterByRef($id_info, $id_work);
 *
 *   // -- Désinscription par id_info (cas hérité Admwork_controller) --
 *   $r = $this->inscriptions->unregisterByInfoId($id_info, $id_work, $is_self);
 *
 *   // -- Familles éligibles à une nouvelle inscription --
 *   $list = $this->inscriptions->getFamiliesAvailableForWork($work);
 *
 * @package WebApp
 */
class Inscriptions
{
    /** @var CI_Controller */
    protected $CI;

    /* ===== Codes de retour exposés ===== */
    const OK                 = 'ok';
    const WORK_NOT_FOUND     = 'work_not_found';
    const PAST_DATE          = 'past_date';
    const ALREADY_REGISTERED = 'already_registered';
    const SESSION_FULL       = 'session_full';
    const NOT_REGISTERED     = 'not_registered';
    const ALREADY_VALIDATED  = 'already_validated';
    const MISSING_FIELDS     = 'missing_fields';
    const INSERT_ERROR       = 'insert_error';
    const DELETE_ERROR       = 'delete_error';

    public function __construct()
    {
        $this->CI = &get_instance();

        // Chargement défensif des dépendances. Utilise le LoadModel maison
        // si présent (déclenche l'init des défs/règles JSON), sinon fallback
        // sur load->model().
        $models = ['Admwork_model', 'Infos_model', 'Familys_model'];
        foreach ($models as $m) {
            if (method_exists($this->CI, 'LoadModel')) {
                $this->CI->LoadModel($m);
            } else {
                $this->CI->load->model($m);
            }
        }
        $this->CI->load->library('RefNotifier');
    }

    // ===================================================================
    //  API publique — actions famille (self-service)
    // ===================================================================

    /**
     * Inscription d'une famille à une session, initiée par la famille
     * elle-même.
     *
     * Vérifications appliquées :
     *   - existence de la session (et conformité au type attendu si fourni)
     *   - session non passée (sauf URG)
     *   - famille pas déjà inscrite
     *   - capacité disponible (somme des nb_participants vs nb_inscrits_max)
     *
     * Pour les sessions cantine ('can'), $context est ignoré : mono-participant
     * 'Mr' avec horaires et nb_units issus de la session. Pour les autres
     * types, $context peut fournir :
     *   - type_participant       ('Mr'|'Mme'|'Both')
     *   - heure_debut_prevue     'HH:MM:SS'
     *   - heure_fin_prevue       'HH:MM:SS'
     *   - type_session           int
     *   - nb_unites_valides      float
     *
     * @param int         $id_work
     * @param int         $id_fam
     * @param array       $context        données complémentaires (travaux classiques)
     * @param string|null $expected_type  si non null ('can'…) contraint le type
     * @return stdClass
     */
    public function registerSelf($id_work, $id_fam, array $context = [], $expected_type = null)
    {
        $id_work = (int) $id_work;
        $id_fam  = (int) $id_fam;
        $r       = $this->_newResult();

        $work = $this->CI->Admwork_model->GetWorkById($id_work, $expected_type);
        if (!$work) return $this->_fail($r, self::WORK_NOT_FOUND);
        $r->work = $work;

        if ($work->type !== 'URG' && strtotime($work->date_travaux) < strtotime(date('Y-m-d'))) {
            return $this->_fail($r, self::PAST_DATE);
        }

        if ($this->CI->Infos_model->IsRegister($id_fam, $id_work)) {
            return $this->_fail($r, self::ALREADY_REGISTERED);
        }

        // Cantine = toujours mono-participant. Sinon, on regarde le contexte.
        $is_cantine       = ($work->type === 'can');
        $type_participant = $is_cantine
            ? 'Mr'
            : (isset($context['type_participant']) ? $context['type_participant'] : 'Mr');
        $nb_participants  = ($type_participant === 'Both') ? 2 : 1;

        if (!$this->_hasCapacity($work, $nb_participants)) {
            return $this->_fail($r, self::SESSION_FULL);
        }

        $datas   = $this->_buildInscriptionRow($id_fam, $work, $context, $type_participant, $nb_participants);
        $id_info = $this->CI->Infos_model->Register($datas);
        if (!$id_info) return $this->_fail($r, self::INSERT_ERROR);

        $r->id_info = (int) $id_info;
        $r->id_fam  = $id_fam;
        $this->_notify($work, $id_fam, 'register');

        return $this->_ok($r);
    }

    /**
     * Désinscription d'une famille, initiée par la famille elle-même.
     *
     * Refuse si une unité a déjà été validée par le référent
     * (nb_unites_valides_effectif > 0).
     *
     * @param int $id_work
     * @param int $id_fam
     * @return stdClass
     */
    public function unregisterSelf($id_work, $id_fam)
    {
        $id_work = (int) $id_work;
        $id_fam  = (int) $id_fam;
        $r       = $this->_newResult();

        $work = $this->CI->Admwork_model->GetWorkById($id_work);
        if (!$work) return $this->_fail($r, self::WORK_NOT_FOUND);
        $r->work = $work;

        $info = $this->CI->Infos_model->IsRegister($id_fam, $id_work);
        if (!$info) return $this->_fail($r, self::NOT_REGISTERED);

        if ((float) $info->nb_unites_valides_effectif > 0) {
            return $this->_fail($r, self::ALREADY_VALIDATED);
        }

        $deleted = $this->CI->Infos_model->Unregister($id_fam, $id_work, true);
        if ($deleted <= 0) return $this->_fail($r, self::DELETE_ERROR);

        $this->_notify($work, $id_fam, 'unregister');
        $r->id_fam = $id_fam;

        return $this->_ok($r);
    }

    // ===================================================================
    //  API publique — mode gestion (référent / admin)
    // ===================================================================

    /**
     * Inscrit une famille à une session en mode gestion (référent ou sys).
     *
     * Pas de notification : le référent est lui-même à l'origine.
     *
     * @param int    $id_work
     * @param int    $id_fam
     * @param string $type_participant  'Mr'|'Mme'|'Both'
     * @param int    $type_session
     * @return stdClass
     */
    public function registerByRef($id_work, $id_fam, $type_participant, $type_session = 1)
    {
        $id_work = (int) $id_work;
        $id_fam  = (int) $id_fam;
        $r       = $this->_newResult();

        if (!$id_fam || !$type_participant) return $this->_fail($r, self::MISSING_FIELDS);

        $work = $this->CI->Admwork_model->GetWorkById($id_work);
        if (!$work) return $this->_fail($r, self::WORK_NOT_FOUND);
        $r->work = $work;

        if ($this->CI->Infos_model->IsRegister($id_fam, $id_work)) {
            return $this->_fail($r, self::ALREADY_REGISTERED);
        }

        $nb_participants = ($type_participant === 'Both') ? 2 : 1;
        if (!$this->_hasCapacity($work, $nb_participants)) {
            return $this->_fail($r, self::SESSION_FULL);
        }

        $datas = [
            'id_famille'        => $id_fam,
            'id_travaux'        => $id_work,
            'type_participant'  => $type_participant,
            'nb_participants'   => $nb_participants,
            'nb_unites_valides' => 0,
            'type_session'      => (int) $type_session ?: 1,
        ];

        $id_info = $this->CI->Infos_model->Register($datas);
        if (!$id_info) return $this->_fail($r, self::INSERT_ERROR);

        $r->id_info = (int) $id_info;
        $r->id_fam  = $id_fam;
        return $this->_ok($r);
    }

    /**
     * Retire une inscription en mode gestion référent (suppression par
     * id_info, avec garde-fou : l'info doit appartenir à $id_work).
     *
     * @param int $id_info
     * @param int $id_work
     * @return stdClass
     */
    public function unregisterByRef($id_info, $id_work)
    {
        $id_info = (int) $id_info;
        $id_work = (int) $id_work;
        $r       = $this->_newResult();

        if (!$id_info) return $this->_fail($r, self::MISSING_FIELDS);

        $info = $this->CI->Infos_model->GetInfoForWork($id_info, $id_work);
        if (!$info) return $this->_fail($r, self::NOT_REGISTERED);

        $this->CI->Infos_model->_set('key_value', $id_info);
        $this->CI->Infos_model->delete();

        $r->id_fam = (int) $info->id_famille;
        return $this->_ok($r);
    }

    /**
     * Désinscription par id_info (cas hérité de Admwork_controller::
     * delete_registration). On notifie le référent uniquement si l'action
     * est réellement à l'initiative de la famille concernée.
     *
     * @param int  $id_info
     * @param int  $id_work
     * @param bool $is_self  true si c'est la famille elle-même qui annule
     * @return stdClass
     */
    public function unregisterByInfoId($id_info, $id_work, $is_self = false)
    {
        $id_info = (int) $id_info;
        $id_work = (int) $id_work;
        $r       = $this->_newResult();

        if (!$id_info) return $this->_fail($r, self::MISSING_FIELDS);

        $info = $this->CI->Infos_model->GetInfoById($id_info);
        if (!$info) return $this->_fail($r, self::NOT_REGISTERED);
        $id_fam    = (int) $info->id_famille;
        $r->id_fam = $id_fam;

        $this->CI->Infos_model->_set('key_value', $id_info);
        $this->CI->Infos_model->delete();

        if ($is_self && $id_fam) {
            $work = $this->CI->Admwork_model->GetWorkById($id_work);
            if ($work) $this->_notify($work, $id_fam, 'unregister');
        }

		// [AJOUT] Alerte e-mail désinscription, uniquement quand c'est
		// la famille elle-même qui se désinscrit (pas l'admin sys).
		if ($id_fam && $this->CI->acl->getType() === 'fam') {
			$this->CI->refnotifier->notifyWorkRegistration(
				(int) $id_work, $id_fam, 'unregister'
			);
		}

        return $this->_ok($r);
    }

    /**
     * Liste des familles éligibles à une inscription sur une session
     * (non encore inscrites + compatibles avec accespar).
     *
     * Délégué au Familys_model pour rester DRY.
     *
     * @param  stdClass $work
     * @return array
     */
    public function getFamiliesAvailableForWork($work)
    {
        return $this->CI->Familys_model->GetAvailableForWork($work);
    }

    // ===================================================================
    //  Helpers privés
    // ===================================================================

    private function _newResult()
    {
        $r = new stdClass();
        $r->success = false;
        $r->code    = '';
        $r->message = '';
        $r->id_info = null;
        $r->id_fam  = null;
        $r->work    = null;
        return $r;
    }

    private function _ok($r)
    {
        $r->success = true;
        $r->code    = self::OK;
        $r->message = $this->_lang('INSCRIPTIONS_OK', 'Opération effectuée.');
        return $r;
    }

    private function _fail($r, $code)
    {
        $r->success = false;
        $r->code    = $code;
        $r->message = $this->_lang('INSCRIPTIONS_' . strtoupper($code), $this->_defaultMessage($code));
        return $r;
    }

    /**
     * Messages de fallback (en français) utilisés quand la clé de langue
     * INSCRIPTIONS_XXX n'est pas définie.
     */
    private function _defaultMessage($code)
    {
        $defaults = [
            self::WORK_NOT_FOUND     => 'Session introuvable.',
            self::PAST_DATE          => 'Cette session est passée.',
            self::ALREADY_REGISTERED => 'Vous êtes déjà inscrit(e) à cette session.',
            self::SESSION_FULL       => 'Plus de place disponible sur cette session.',
            self::NOT_REGISTERED     => 'Aucune inscription trouvée.',
            self::ALREADY_VALIDATED  => 'Unité déjà validée — désinscription impossible.',
            self::MISSING_FIELDS     => 'Informations manquantes.',
            self::INSERT_ERROR       => 'Erreur lors de l\'enregistrement.',
            self::DELETE_ERROR       => 'Erreur lors de la suppression.',
        ];
        return isset($defaults[$code]) ? $defaults[$code] : 'Opération impossible.';
    }

    private function _lang($key, $fallback)
    {
        $val = $this->CI->lang->line($key);
        return $val ? $val : $fallback;
    }

    /**
     * Vérifie la capacité résiduelle d'une session.
     */
    private function _hasCapacity($work, $nb_participants)
    {
        $decompte = $this->CI->Infos_model->Decompte((int) $work->id);
        $current  = $decompte ? (int) $decompte->nb_participants : 0;
        $max      = (int) $work->nb_inscrits_max;
        return ($current + $nb_participants) <= $max;
    }

    /**
     * Construit la ligne `infos` à insérer pour une inscription self-service.
     *
     * Hérité du comportement historique de Cantine_controller::register_one
     * et Admwork_controller::ADD_registration : on défaut sur les horaires
     * et nb_units de la session, et type_session = 1.
     */
    private function _buildInscriptionRow($id_fam, $work, $context, $type_participant, $nb_participants)
    {
        $now = date('Y-m-d H:i:s');

        return [
            'id_famille'                 => $id_fam,
            'id_travaux'                 => (int) $work->id,
            'nb_participants'            => $nb_participants,
            'type_participant'           => $type_participant,
            'heure_debut_prevue'         => isset($context['heure_debut_prevue']) ? $context['heure_debut_prevue'] : $work->heure_deb_trav,
            'heure_fin_prevue'           => isset($context['heure_fin_prevue'])   ? $context['heure_fin_prevue']   : $work->heure_fin_trav,
            'nb_unites_valides'          => isset($context['nb_unites_valides'])  ? (float) $context['nb_unites_valides'] : (float) $work->nb_units,
            'nb_unites_valides_effectif' => 0,
            'type_session'               => isset($context['type_session'])       ? (int) $context['type_session']        : 1,
            'created'                    => $now,
            'updated'                    => $now,
        ];
    }

    /**
     * Aiguillage des notifications RefNotifier selon le type de session.
     */
    private function _notify($work, $id_fam, $action)
    {
        if (!$work) return;
        if ($work->type === 'can') {
            $this->CI->refnotifier->notifyCantineRegistration((int) $work->id, (int) $id_fam, $action);
        } else {
            $this->CI->refnotifier->notifyWorkRegistration((int) $work->id, (int) $id_fam, $action);
        }
    }
}

/* End of file Inscriptions.php */
/* Location: ./application/libraries/Inscriptions.php */
