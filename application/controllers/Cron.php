<?php


class Cron extends MY_Controller {
    
    protected $lockFile = '';  
    public $Sendmail_model = NULL;
    public $Sendmail_statut_model = NULL;
    public $render_object = NULL;
    public $email = NULL;

    /**
     * Method __construct
     *
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        $this->lockFile = str_replace('application','',APPPATH).'/process.loc';
    }
   


    /**
     * Cron SendMail ( bypass right use ACL exception ACL::$guestPages TODO : use cron-key ? )
     * @param int $size 
     * @return void 
     */
    function sendmail($size = 10){
		$this->_setLock();

		//TODO : load on demand
		$this->LoadModel('Sendmail_model');
        $this->LoadModel('Sendmail_statut_model');

        $this->load->library('email');

        $listemails = $this->Sendmail_model->get4send($size);
        foreach($listemails as $key=>$listemail){
           
            $this->email->clear(TRUE);
            $this->email->from('noreply@mulhouse-travaux.abcmzwei.eu', 'Regio MLH ABCM');
            $this->email->to($listemail->email);
            $this->email->subject($listemail->object);
            $this->email->message($listemail->message);

            $listemail->statut = (($this->email->send()) ? 1:2);
            $listemail->updated = date('Y-m-d h:i:s');
            
            /* MAJ send mail */
            $sendmail = [];
            foreach( $listemail AS $field=>$value){
                $sendmail[$field] = $value;
            }
            $this->Sendmail_model->_set('key_value', $listemail->id);	
            $this->Sendmail_model->_set('datas', $sendmail);
            $this->Sendmail_model->put();

            /* MAJ log SENDMAIL */
            $statut = [];
            $statut['id_sen'] = $listemail->id;
            $statut['date'] = date('Y-m-d H:i:s');
            $statut['sendstatut'] = $listemail->statut; //nouveau
            $statut['created'] = date('Y-m-d h:i:s');
            $statut['error'] = $this->email->print_debugger();
            $statut['sendstatut'] = $listemail->statut;
            $this->Sendmail_statut_model->post($statut);
            echo 'e-mail '.$key.' : '.$listemail->statut."\n";
        }
	}

    function __destruct() 
    {
        if (file_exists($this->lockFile)) {
            unlink($this->lockFile);
        }
    }

    private function _setLock(){
		if (file_exists($this->lockFile)) {
			echo 'Un autre processus est déjà en cours d\'exécution. Veuillez réessayer plus tard.';
			die();
		}		
		// Créer le fichier de verrouillage
		if (!touch($this->lockFile)) {
			echo 'Impossible de créer le fichier de verrouillage.';
			exit;
		}
    }


    
    /**
     * Envoie aux référents un lien vers leur session à venir.
     *
     * @param int $days_before  nb jours avant la session pour l'envoi (défaut 7)
     * @return void
     */
    public function send_ref_validation_mails($days_before = 7)
    {
        $this->_setLock();

        $this->LoadModel('Admwork_model');
        $this->LoadModel('ValidationToken_model');
        $this->LoadModel('Sendmail_model');

        $works = $this->Admwork_model->GetWorksNeedingRefMail((int) $days_before);
        if (empty($works)) {
            echo "Aucune session à notifier dans les {$days_before} prochains jours.\n";
            return;
        }

        $base_url = config_item('base_url') ?: base_url();

        foreach ($works as $work) {
            // 1) Retrouver la famille référente (via groupes_member.id_fam)
            $refFamily = $this->Admwork_model->GetReferentFamily($work->id);
            if (!$refFamily || empty($refFamily->e_mail)) {
                echo "Session {$work->id} ({$work->titre}) : référent introuvable ou sans email, ignorée.\n";
                // On ne marque PAS la session comme envoyée : on retentera demain
                // au cas où le problème vient d'un id_fam momentanément vide.
                continue;
            }

            // 2) Générer un token pour ce (session, référent)
            $token = $this->ValidationToken_model->create($work->id, $refFamily->id);
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
            $this->Sendmail_model->post([
                'reference' => 'ref_validation',
                'email'     => $refFamily->e_mail,
                'object'    => $subject,
                'message'   => $message,
                'created'   => date('Y-m-d H:i:s'),
            ]);

            // 5) Marquer la session comme notifiée
            $this->Admwork_model->MarkRefMailSent($work->id);

            echo "Mail programmé pour {$refFamily->e_mail} "
               . "(session #{$work->id} '{$work->titre}' du {$date_fr}).\n";
        }
    }
}