<?php

namespace App\Libraries;

use App\Models\AlertPref_model;
use App\Models\Admwork_model;
use App\Models\Sendmail_model;
use App\Models\Sendmail_statut_model;
use App\Models\ValidationToken_model;
use Closure;

/**
 * Tâches planifiées (appelées par les commandes spark de app/Commands) :
 * envoi de la file d'e-mails, mail aux référents, alertes de nouvelles sessions.
 *
 * Chaque tâche écrit ses messages par la fonction de sortie passée à setOutput().
 */
class CronJobs
{
    private Closure $out;
    private Admwork_model $works;
    private Sendmail_model $queue;
    private Sendmail_statut_model $statuts;
    private ValidationToken_model $tokens;
    private AlertPref_model $prefs;

    public function __construct()
    {
        $this->out     = static function (string $line): void {};
        $this->works   = model(Admwork_model::class);
        $this->queue   = model(Sendmail_model::class);
        $this->statuts = model(Sendmail_statut_model::class);
        $this->tokens  = model(ValidationToken_model::class);
        $this->prefs   = model(AlertPref_model::class);
    }

    public function setOutput(callable $out): self
    {
        $this->out = Closure::fromCallable($out);

        return $this;
    }

    private function say(string $line): void
    {
        ($this->out)(rtrim($line, "\n"));
    }

    /**
     * Exécute $task en exclusion mutuelle (flock sur writable/cron.lock) : deux cron ne se chevauchent pas,
     * et le verrou disparaît seul si le processus est tué.
     */
    public function locked(callable $task): bool
    {
        $fh = fopen(WRITEPATH . 'cron.lock', 'c');
        if ($fh === false || ! flock($fh, LOCK_EX | LOCK_NB)) {
            $this->say("Un autre processus est déjà en cours d'exécution. Veuillez réessayer plus tard.");

            return false;
        }
        try {
            $task($this);
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }

        return true;
    }

    /** Envoie jusqu'à $size e-mails de la file `sendmail` et trace le résultat dans `sendmail_statut`. */
    public function sendmail(int $size = 10): void
    {
        $size = max(1, $size); // limit(0) = sans limite dans le Query Builder
        $config    = config('Travaux');
        $fromEmail = $config->mailFromEmail;
        if (empty($fromEmail)) {
            log_message('error', 'Cron sendmail : travaux.mailFromEmail non configuré dans .env, abandon.');
            $this->say('Erreur : travaux.mailFromEmail non configuré (voir .env)');

            return;
        }

        $email = service('email'); // Config\Email, surchargée par les variables email.* du .env
        foreach ($this->queue->get4send($size) as $key => $mail) {
            $email->clear(true);
            $email->setFrom($fromEmail, $config->mailFromName ?: $fromEmail);
            if (! empty($config->mailReplyTo)) {
                $email->setReplyTo($config->mailReplyTo);
            }
            $email->setTo($mail->email);
            $email->setSubject($mail->object);
            $email->setMessage($mail->message);

            $statut = $email->send(false) ? 1 : 2;
            $this->queue->update($mail->id, ['statut' => $statut]);
            $this->statuts->insert([
                'id_sen'     => $mail->id,
                'date'       => date('Y-m-d H:i:s'),
                'sendstatut' => $statut,
                'error'      => $email->printDebugger(),
            ]);
            $this->say('e-mail ' . $key . ' : ' . $statut);
        }
    }

    /** Envoie aux référents un lien vers leur session à venir ($days_before jours avant). */
    public function sendRefValidationMails(int $days_before = 7): void
    {
        $works = $this->works->GetWorksNeedingRefMail($days_before);
        if (empty($works)) {
            $this->say("Aucune session à notifier dans les {$days_before} prochains jours.\n");
            return;
        }

        $base_url = base_url();

        foreach ($works as $work) {
            // 1) Retrouver la famille référente (via groupes_member.id_fam)
            $refFamily = $this->works->GetReferentFamily($work->id);
            if (!$refFamily || empty($refFamily->e_mail)) {
                $this->say("Session {$work->id} ({$work->titre}) : référent introuvable ou sans email, ignorée.\n");
                // On ne marque PAS la session comme envoyée : on retentera demain
                // au cas où le problème vient d'un id_fam momentanément vide.
                continue;
            }

            // 2) Générer un token pour ce (session, référent)
            $token = $this->tokens->create($work->id, $refFamily->id);
            $link  = rtrim($base_url, '/') . '/Admwork_controller/validate_by_token/' . $token;

            // 3) Construire le mail
            $type_label = ($work->type === 'MEN') ? 'ménage' : 'travaux';
            $date_fr    = date('d/m/Y', strtotime($work->date_travaux));
            $days_to_go = max(0, floor((strtotime($work->date_travaux) - strtotime('today')) / 86400));

            $subject = 'Votre session ' . $type_label . ' du ' . $date_fr;

            $heures = '';
            if ($work->type_session == 1 && $work->heure_deb_trav) {
                $heures = ' de ' . substr($work->heure_deb_trav, 0, 5)
                        . ' à '  . substr($work->heure_fin_trav, 0, 5);
            }

            $ecole_label = '';
            switch ($work->ecole) {
                case 'M': $ecole_label = 'Mulhouse';   break;
                case 'L': $ecole_label = 'Lutterbach'; break;
                case 'B': $ecole_label = 'Mulhouse et Lutterbach'; break;
            }

            $message = "Bonjour,\n\n"
                . "Vous êtes référent de la session de " . $type_label
                . " \"" . $work->titre . "\"\n"
                . "prévue le " . $date_fr . $heures
                . ($ecole_label ? " à " . $ecole_label : "") . ",\n"
                . "soit dans " . $days_to_go . " jour" . ($days_to_go > 1 ? 's' : '') . ".\n\n"
                . "Vous pouvez dès à présent consulter la liste des parents inscrits "
                . "en suivant ce lien :\n"
                . $link . "\n\n"
                . "Le jour de la session, ce même lien vous permettra de valider "
                . "les présences, ajuster le nombre d'unités réalisées, et signaler "
                . "d'éventuels no-shows.\n\n"
                . "Ce lien est personnel. Il reste valide "
                . ValidationToken_model::EXPIRY_DAYS . " jours.\n\n"
                . "Merci pour votre engagement !\n"
                . "L'association ABCM Mulhouse-Lutterbach";

            // 4) Pousser dans la file d'envoi (cron sendmail s'en chargera)
            $this->queue->post([
                'reference' => 'ref_validation',
                'email'     => $refFamily->e_mail,
                'object'    => $subject,
                'message'   => $message,
            ]);

            // 5) Marquer la session comme notifiée
            $this->works->MarkRefMailSent($work->id);

            $this->say("Mail programmé pour {$refFamily->e_mail} "
               . "(session #{$work->id} '{$work->titre}' du {$date_fr}).\n");
        }
    }

    /**
     * Alerte les familles abonnées de chaque nouvelle session publiée (marquée par travaux.alert_sent_at
     * pour ne pas répéter l'alerte).
     *
     * @param int $lookahead_days ne notifie que les sessions des N prochains jours (0 = sans borne)
     */
    public function sendNewSessionAlerts(int $lookahead_days = 0): void
    {
        $works = $this->sessionsNeedingAlert($lookahead_days);
        if (empty($works)) {
            $this->say("Aucune nouvelle session à signaler.\n");
            return;
        }

        $base_url = base_url();
        $register_url = rtrim($base_url, '/') . '/Admwork_controller/register';

        $total_mails  = 0;
        $total_works  = 0;

        foreach ($works as $work) {

            // 1) Récupère les familles abonnées à ce type, filtrées par école
            $subs = $this->prefs->GetSubscribers($work->type, $work->ecole);

            if (empty($subs)) {
                // Aucun abonné : on marque quand même comme traitée pour ne
                // pas re-scanner à l'infini cette session.
                $this->works->MarkAlertSent($work->id);
                $this->say("Session #{$work->id} : aucun abonné pour le type {$work->type}.\n");
                continue;
            }

            // 2) Préparation du libellé du type (depuis options)
            $type_label = $this->typeLabel($work->type);

            // 3) Construit le mail (commun à tous les abonnés, expéditeur unique)
            $date_fr = date('d/m/Y', strtotime($work->date_travaux));
            $heures = '';
            if ($work->type_session == 1 && $work->heure_deb_trav) {
                $heures = ' de ' . substr($work->heure_deb_trav, 0, 5)
                        . ' à '  . substr($work->heure_fin_trav, 0, 5);
            }

            $ecole_label = '';
            switch ($work->ecole) {
                case 'M': $ecole_label = 'Mulhouse';   break;
                case 'L': $ecole_label = 'Lutterbach'; break;
                case 'B': $ecole_label = 'Mulhouse et Lutterbach'; break;
            }

            $subject = 'Nouvelle session ' . $type_label . ' du ' . $date_fr;

            $message_template =
                "Bonjour,\n\n"
                . "Une nouvelle session correspondant à vos préférences vient d'être publiée :\n\n"
                . "  - Titre : " . $work->titre . "\n"
                . "  - Type  : " . $type_label . "\n"
                . "  - Date  : " . $date_fr . $heures . "\n"
                . ($ecole_label ? "  - École : " . $ecole_label . "\n" : "")
                . "  - Places : " . (int) $work->nb_inscrits_max . "\n\n"
                . "Pour vous inscrire, rendez-vous sur :\n"
                . $register_url . "\n\n"
                . "Vous recevez cet e-mail parce que vous avez activé l'alerte pour ce "
                . "type de session dans votre compte. Pour modifier vos préférences :\n"
                . rtrim($base_url, '/') . "/Home/myaccount\n\n"
                . "L'association ABCM Mulhouse-Lutterbach";

            // 4) Empilement d'un e-mail par abonné
            foreach ($subs as $fam) {
                $this->queue->post([
                    'reference' => 'new_session_alert',
                    'email'     => $fam->e_mail,
                    'object'    => $subject,
                    'message'   => $message_template,
                ]);
                $total_mails++;
            }

            // 5) Marque la session comme notifiée (idempotence)
            $this->works->MarkAlertSent($work->id);
            $total_works++;

            $this->say("Session #{$work->id} ({$work->titre}) : "
                . count($subs) . " famille(s) notifiée(s).\n");
        }

        $this->say("Total : {$total_mails} e-mail(s) programmé(s) sur {$total_works} session(s).\n");
    }

    /** Sessions qui doivent déclencher une alerte e-mail. */
    private function sessionsNeedingAlert(int $lookahead_days = 0): array
    {
        $b = db_connect()->table('travaux')->select('travaux.*')
            ->where('travaux.archived !=', 1)
            ->where('travaux.alert_sent_at IS NULL', null, false)
            ->where('travaux.statut', '1') // publié uniquement
            ->where('travaux.date_travaux >=', date('Y-m-d'))
            ->where('travaux.type IS NOT NULL', null, false)
            ->where('travaux.type !=', '');

        if ($lookahead_days > 0) {
            $b->where('travaux.date_travaux <=', date('Y-m-d', strtotime('+' . $lookahead_days . ' days')));
        }

        return $b->orderBy('travaux.date_travaux', 'ASC')->get()->getResult();
    }

    /** Libellé humain d'un type de travail (table options), à défaut la clé elle-même. */
    private function typeLabel(string $cle): string
    {
        $row = db_connect()->table('options')->select('value')->where('cle', $cle)->where('filter', 'type')->limit(1)->get()->getRow();

        return $row ? $row->value : $cle;
    }
}
