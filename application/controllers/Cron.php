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
}