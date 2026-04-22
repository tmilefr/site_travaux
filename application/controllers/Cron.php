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
     * Envoie aux référents un lien pour valider les présences de leurs
     * sessions passées.
     *
     * @param int $days_since  délai de sécurité (jours depuis la session)
     * @return void
     */
    public function send_ref_validation_mails($days_since = 0)
    {
        $this->_setLock();

        $this->LoadModel('Admwork_model');
        $this->LoadModel('ValidationToken_model');
        $this->LoadModel('Sendmail_model');

        $works = $this->Admwork_model->GetWorksNeedingRefMail((int) $days_since);
        if (empty($works)) {
            echo "Aucune session à notifier.\n";
            return;
        }

        $base_url = config_item('base_url') ?: base_url();

        foreach ($works as $work) {
            // 1) Retrouver la famille référente
            $refFamily = $this->Admwork_model->GetReferentFamily($work->id);
            if (!$refFamily || empty($refFamily->e_mail)) {
                echo "Session {$work->id} : référent sans email, ignorée.\n";
                // Marquer quand même pour ne pas retenter indéfiniment ?
                // $this->Admwork_model->MarkRefMailSent($work->id);
                continue;
            }

            // 2) Générer un token pour ce (session, référent)
            $token = $this->ValidationToken_model->create($work->id, $refFamily->id);
            $link  = rtrim($base_url, '/') . '/Admwork_controller/validate_by_token/' . $token;

            // 3) Construire le mail (texte simple — votre template peut l'enrichir)
            $subject = 'Validation des présences : ' . $work->titre
                . ' (' . date('d/m/Y', strtotime($work->date_travaux)) . ')';

            $message = "Bonjour,\n\n"
                . "Vous étiez référent de la session \"" . $work->titre . "\" "
                . "du " . date('d/m/Y', strtotime($work->date_travaux)) . ".\n\n"
                . "Merci de valider la présence des parents inscrits en suivant ce lien :\n"
                . $link . "\n\n"
                . "Ce lien est personnel et expire dans "
                . ValidationToken_model::EXPIRY_DAYS . " jours.\n\n"
                . "Merci !\n"
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

            echo "Mail programmé pour {$refFamily->e_mail} (session {$work->id}).\n";
        }
    }
}