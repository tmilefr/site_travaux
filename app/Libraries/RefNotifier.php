<?php

namespace App\Libraries;

use App\Models\Admwork_model;
use App\Models\Familys_model;
use App\Models\Orgchart_model;
use App\Models\Sendmail_model;
use App\Models\Sendmail_statut_model;
use App\Models\Trombi_model;

/**
 * RefNotifier
 *
 * Service centralisé de notification e-mail au responsable de session
 * (référent travaux/cantine, RT de commission) lors des inscriptions et
 * désinscriptions des familles.
 *
 * Pour rester cohérent avec le reste de l'application, on ne fait pas
 * d'envoi SMTP synchrone : on insère une ligne dans `sendmail`, et le
 * cron `php index.php cron sendmail` (déjà planifié toutes les 10 min)
 * se charge de la livraison.
 *
 * Cas dégradé : si l'on ne retrouve pas l'e-mail du responsable, on
 * loggue l'événement ET on envoie une alerte à l'expéditeur configuré
 * (`mail_from_email` dans secured.php) pour que le bureau soit prévenu
 * que sa donnée référent est incomplète.
 *
 * Usage type :
 *   $notifier = service('refNotifier');
 *   $this->refnotifier->notifyWorkRegistration($id_work, $id_fam, 'register');
 *   $this->refnotifier->notifyCommissionRegistration($id_grp, $id_fam, 'register');
 *
 * @package WebApp
 */
#[\AllowDynamicProperties]
class RefNotifier
{
    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    protected $Sendmail_model;
    protected $Sendmail_statut_model;
    protected $Admwork_model;
    protected $Familys_model;
    protected $Trombi_model;
    protected $Orgchart_model;

    /** @var array  cache simple sur la durée de la requête */
    protected $_familyCache = [];

    public function __construct()
    {
        $this->db = \Config\Database::connect();

        $this->Sendmail_model        = model(Sendmail_model::class);
        $this->Sendmail_statut_model = model(Sendmail_statut_model::class);
        $this->Admwork_model         = model(Admwork_model::class);
        $this->Familys_model         = model(Familys_model::class);
        $this->Trombi_model          = model(Trombi_model::class);
        $this->Orgchart_model        = model(Orgchart_model::class);
    }

    // ===================================================================
    // API publique
    // ===================================================================

    /**
     * Notifie le référent d'une session de travaux (type != 'can').
     *
     * @param int    $id_work
     * @param int    $id_fam
     * @param string $action  'register' | 'unregister'
     * @return void
     */
    public function notifyWorkRegistration($id_work, $id_fam, $action)
    {
        $work = $this->db->table('travaux')->where('id', (int) $id_work)->get()->getRow();
        if (!$work) { return; }

        $refFamily = $this->Admwork_model->GetReferentFamily((int) $id_work);
        $refEmail  = $refFamily ? $refFamily->e_mail : null;
        $refName   = $refFamily ? $refFamily->nom    : null;

        $family = $this->_getFamily((int) $id_fam);

        $this->_dispatch(
            $refEmail, $refName,
            $this->_buildSubject('session', $work, $action),
            $this->_buildMessage('session', $work, $family, $action),
            'ref_alert_work_' . $action,
            ['id_work' => (int) $id_work, 'id_fam' => (int) $id_fam]
        );
    }

    /**
     * Notifie le référent d'une session de cantine (type = 'can').
     *
     * @param int    $id_work
     * @param int    $id_fam
     * @param string $action  'register' | 'unregister'
     * @return void
     */
    public function notifyCantineRegistration($id_work, $id_fam, $action)
    {
        $work = $this->db->table('travaux')->where('id', (int) $id_work)->where('type', 'can')->get()->getRow();
        if (!$work) { return; }

        $refFamily = $this->Admwork_model->GetReferentFamily((int) $id_work);
        $refEmail  = $refFamily ? $refFamily->e_mail : null;
        $refName   = $refFamily ? $refFamily->nom    : null;

        $family = $this->_getFamily((int) $id_fam);

        $this->_dispatch(
            $refEmail, $refName,
            $this->_buildSubject('cantine', $work, $action),
            $this->_buildMessage('cantine', $work, $family, $action),
            'ref_alert_cantine_' . $action,
            ['id_work' => (int) $id_work, 'id_fam' => (int) $id_fam]
        );
    }

    /**
     * Notifie le RT (responsable, classif='RT') d'une commission lors
     * d'une candidature ou d'une annulation de candidature.
     *
     * @param int    $id_grp  id de la commission (orgchart.id)
     * @param int    $id_fam
     * @param string $action  'register' | 'unregister'
     * @return void
     */
    public function notifyCommissionCandidature($id_grp, $id_fam, $action)
    {
        $commission = $this->db->table('trombi')->where('id', (int) $id_grp)->get()->getRow();
        if (!$commission) { return; }

        // RT de la commission : trombi.classif = 'RT'
        $rt        = $this->Trombi_model->GetMemberFromClassif((int) $id_grp, 'RT');
        $refEmail  = ($rt && !empty($rt->email)) ? $rt->email : null;
        $refName   = $rt ? trim(($rt->name ?? '') . ' ' . ($rt->surname ?? '')) : null;

        $family = $this->_getFamily((int) $id_fam);

        $this->_dispatch(
            $refEmail, $refName,
            $this->_buildSubject('commission', $commission, $action),
            $this->_buildMessage('commission', $commission, $family, $action),
            'ref_alert_commission_' . $action,
            ['id_grp' => (int) $id_grp, 'id_fam' => (int) $id_fam]
        );
    }

    // ===================================================================
    // Construction des contenus
    // ===================================================================

    private function _buildSubject($kind, $obj, $action)
    {
        $verb = ($action === 'register') ? 'Inscription' : 'Désinscription';
        switch ($kind) {
            case 'session':
                $date = isset($obj->date_travaux)
                    ? date('d/m/Y', strtotime($obj->date_travaux)) : '';
                return $verb . ' à votre session "' . $obj->titre . '" du ' . $date;
            case 'cantine':
                $date = isset($obj->date_travaux)
                    ? date('d/m/Y', strtotime($obj->date_travaux)) : '';
                return $verb . ' à la cantine du ' . $date;
            case 'commission':
                $verbCom = ($action === 'register') ? 'Candidature' : 'Annulation de candidature';
                return $verbCom . ' pour la commission "' . $obj->title . '"';
        }
        return $verb;
    }

    private function _buildMessage($kind, $obj, $family, $action)
    {
        $verb_inscription = ($action === 'register') ? "s'est inscrite" : "s'est désinscrite";
        $verb_candidature = ($action === 'register') ? "vient de poser sa candidature" : "vient d'annuler sa candidature";

        $famille_nom    = $family ? ($family->nom ?? $family->login ?? 'inconnue') : 'inconnue';
        $famille_email  = $family ? ($family->e_mail ?? '') : '';

        $base_url = base_url();

        switch ($kind) {
            case 'session':
                $date = isset($obj->date_travaux)
                    ? date('d/m/Y', strtotime($obj->date_travaux)) : '';
                $heures = '';
                if (!empty($obj->heure_deb_trav) && !empty($obj->heure_fin_trav)) {
                    $heures = "Horaires : "
                        . substr($obj->heure_deb_trav, 0, 5) . ' - '
                        . substr($obj->heure_fin_trav, 0, 5) . "\n";
                }
                $lien = $base_url . 'Admwork_controller/my_sessions';
                return "Bonjour,\n\n"
                    . "La famille " . $famille_nom . " (" . $famille_email . ") "
                    . $verb_inscription . " à votre session :\n\n"
                    . "Titre  : " . $obj->titre . "\n"
                    . "Date   : " . $date . "\n"
                    . $heures
                    . "\nVous pouvez consulter la liste à jour des inscrits ici :\n"
                    . $lien . "\n\n"
                    . "L'association ABCM Mulhouse-Lutterbach";

            case 'cantine':
                $date = isset($obj->date_travaux)
                    ? date('d/m/Y', strtotime($obj->date_travaux)) : '';
                $heures = '';
                if (!empty($obj->heure_deb_trav) && !empty($obj->heure_fin_trav)) {
                    $heures = "Horaires : "
                        . substr($obj->heure_deb_trav, 0, 5) . ' - '
                        . substr($obj->heure_fin_trav, 0, 5) . "\n";
                }
                $lien = $base_url . 'Cantine_controller/register';
                return "Bonjour,\n\n"
                    . "La famille " . $famille_nom . " (" . $famille_email . ") "
                    . $verb_inscription . " à la session de cantine :\n\n"
                    . "Date   : " . $date . "\n"
                    . $heures
                    . "\nVous pouvez consulter la liste à jour des inscrits ici :\n"
                    . $lien . "\n\n"
                    . "L'association ABCM Mulhouse-Lutterbach";

            case 'commission':
                $lien = $base_url . 'Orgchart_controller/view_one/' . (int) $obj->id;
                return "Bonjour,\n\n"
                    . "La famille " . $famille_nom . " (" . $famille_email . ") "
                    . $verb_candidature . " pour la commission :\n\n"
                    . "Commission : " . $obj->title . "\n"
                    . "\nVous pouvez consulter la fiche de la commission ici :\n"
                    . $lien . "\n\n"
                    . "L'association ABCM Mulhouse-Lutterbach";
        }
        return '';
    }

    // ===================================================================
    // Pose dans la file d'envoi + gestion du cas dégradé
    // ===================================================================

    /**
     * Dépose le mail dans la file `sendmail`. Crée aussi la ligne
     * `sendmail_statut` initiale (statut = 0), à l'identique du reste
     * de l'application (cf. Api::mails et Cron::send_ref_validation_mails).
     *
     * @param string|null $email
     * @param string|null $name
     * @param string $subject
     * @param string $message
     * @param string $reference  trace logique (ref_alert_work_register, ...)
     * @param array  $context    pour les logs en cas dégradé
     * @return void
     */
    private function _dispatch($email, $name, $subject, $message, $reference, $context)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->_handleMissingEmail($reference, $subject, $message, $context);
            return;
        }
        $this->_pushSendmail($email, $subject, $message, $reference);
    }

    /**
     * Insertion d'un e-mail dans la file `sendmail` + statut initial.
     *
     * @return int|null  id de la ligne sendmail créée
     */
    private function _pushSendmail($email, $subject, $message, $reference)
    {
        $row = [
            'reference' => $reference,
            'email'     => $email,
            'object'    => $subject,
            'message'   => $message,
            'created'   => date('Y-m-d H:i:s'),
        ];
        $id = $this->Sendmail_model->post($row);

        if ($id) {
            $this->Sendmail_statut_model->post([
                'id_sen'     => $id,
                'date'       => date('Y-m-d H:i:s'),
                'sendstatut' => 0,
                'created'    => date('Y-m-d H:i:s'),
            ]);
        }
        return $id ?: null;
    }

    /**
     * Cas dégradé : référent introuvable ou sans e-mail.
     *  - log dans le journal applicatif
     *  - envoie une alerte à l'admin (mail_from_email)
     *
     * @return void
     */
    private function _handleMissingEmail($reference, $subject, $message, $context)
    {
        $msg = '[RefNotifier] référent sans e-mail valide. '
             . 'reference=' . $reference . ' '
             . 'context=' . json_encode($context);
        log_message('error', $msg);

        $adminEmail = config('Travaux')->mailFromEmail;
        if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            // On ne peut rien faire de plus : pas d'admin configuré.
            log_message('error', '[RefNotifier] mail_from_email absent : alerte admin non envoyée.');
            return;
        }

        $alertSubject = '[ALERTE] Notification référent impossible — ' . $subject;
        $alertMessage = "Bonjour,\n\n"
            . "Une notification automatique au responsable de session n'a pas pu être\n"
            . "envoyée parce que son adresse e-mail est introuvable ou invalide.\n\n"
            . "Référence : " . $reference . "\n"
            . "Contexte  : " . json_encode($context) . "\n\n"
            . "Merci de vérifier la fiche du référent dans la table trombi/famille.\n\n"
            . "--- Contenu du message qui aurait dû partir ---\n"
            . "Sujet   : " . $subject . "\n\n"
            . $message;

        $this->_pushSendmail($adminEmail, $alertSubject, $alertMessage,
                             $reference . '_admin_alert');
    }

    // ===================================================================
    // Helpers
    // ===================================================================

    private function _getFamily($id_fam)
    {
        if (!$id_fam) { return null; }
        if (isset($this->_familyCache[$id_fam])) {
            return $this->_familyCache[$id_fam];
        }
        $fam = $this->Familys_model->GetFamily($id_fam);
        $this->_familyCache[$id_fam] = $fam ?: null;
        return $this->_familyCache[$id_fam];
    }
}



