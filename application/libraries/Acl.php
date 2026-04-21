<?php

if (! defined('BASEPATH'))
    exit('No direct script access allowed');

/*
* GESTION DE DROIT
* la sécurité est basé sur les urls : base_url()/$controller/$method/
* Acl établi le redirect.
*
* -- Chainage --
* Hook:Loginchecker => acl:route =>  acl:usercheck => auth:connected_user
*
*
*/
class Acl
{
    protected $CI;
    protected $controller = NULL;
    protected $action = NULL;
    protected $permissions = [];
    /* BYPASS */
    protected $guestPages = [
        'home/landing',
        'home/logout',
        'home/login',
        'home/no_right',
        'home/maintenance',
		'cron/sendmail',
        'publics/index',
        'publics/files',
    ];
    /* BYPASS API */
    protected $APIguestPages = [
        'api/login',
        'api/logout'
    ];    
    /* PARAM to force TOKEN */
    protected $apiPages = [
        'api'
    ];
    protected $ApiAsk       = FALSE;
    protected $DontCheck    = FALSE;
    protected $_debug       = FALSE;
    protected $_debug_array = [];
    protected $usercheck    = NULL; //TODO : replace $usercheck 
    protected $routes_hisory = [];
	//protected $ApiMode 		= FALSE;
    
    /**
     * Constructor
     *
     * @param array $config            
     */
    public function __construct($config = array())
    {
        $this->CI = &get_instance();
        $this->CI->load->library('session');
        $this->CI->load->library('Auth');

        $this->CI->load->helper('url');
        $this->CI->LoadModel('Acl_roles_model');


        $this->controller = strtolower($this->CI->uri->rsegment(1));
        $this->action     = strtolower($this->CI->uri->rsegment(2));
        $this->routes_hisory = [];


        //création du tableau des droits pour l'utilisateur.
        if ($this->IsLog()){
            $this->permissions = $this->CI->Acl_roles_model->getRolePermissions();
        }
    }
    
   private function _IsApiAsk(){
        if (in_array( $this->controller , $this->apiPages) && !in_array($this->controller . '/' . $this->action, $this->APIguestPages) ) { 
            $this->ApiAsk = true;
        }
   }

    /**
     * Check if user is connected
     * Use session objet "connected_user" or JWT token.
     * @access public
     * @return bool
     * 
     */
    public function IsLog(){
        if ( $this->ApiAsk ) { 
            $headers = getallheaders();
            //token JWT pour les apis
            if (isset($headers['Authorization']) && preg_match('/Bearer (.*)/', $headers['Authorization'], $matches)){
                $jwt = trim($matches[1]);
                $this->CI->auth->DecodeJWT($jwt);
                $this->usercheck = $this->CI->auth->_get('connected_user');
                $this->permissions = $this->CI->Acl_roles_model->getRolePermissions(); 
                } else {
                //si un utilisateur est connecté...
                if ($this->usercheck = $this->CI->auth->_get('connected_user')){

                } else {
                    //Pas de token ! //todo : use CORE method
                    $this->CI->_renderJson(403, ["message" => "Forbidden"]);
                    die();
                }
            }    
        }  else {
            $this->usercheck = $this->CI->auth->_get('connected_user'); //best way to use ?
        }      

                    
        //gestion des comptes d'administration pour des familles, ont force l'appartenance à "sys"
        if ($this->hasAccess('admwork_controller/add')) {
            $this->usercheck->type  = 'sys';
            $this->CI->session->set_userdata('connected_user', $this->usercheck);
        }
   
        if (  $this->usercheck->autorize === true ){
            return TRUE;
        } else {
            return FALSE;
        }
    }
   
    /**
     * manage url access
     *
     * @access public
     * @return bool
     * 
     */
    public function hasAccess($currentPermission = null)
    {
        if ($this->DontCheck)
            return TRUE;
        //Opération de maintenance
        if ($this->CI->config->item('maintenance') == true &&  $this->controller . '/' . $this->action != 'home/maintenance'){
            if ($this->getType() != 'sys') //les utilisateurs SYS ne sont pas concerné.
                return redirect('/Home/maintenance');
        }
        //UI Guest Page 
        if (in_array($this->controller . '/' . $this->action, $this->getGuestPages()) AND !$currentPermission) { //hors page en cours
            return TRUE;  
        }
        if (in_array($this->controller . '/' . $this->action, $this->APIguestPages) AND !$currentPermission) { //hors page en cours
            return TRUE;
        }
        //Check right
        if (isset($this->usercheck->role_id)){  
            if (!$currentPermission) //on recupère la page en cours par défaut
                $currentPermission =  $this->controller . '/' . $this->action;
            //on regarde dans le tableau des droits ratatchés à l'utilisateur.
            if (isset($this->permissions[$this->getUserRoleId()]) && count($this->permissions[$this->getUserRoleId()]) > 0) {
                if (in_array( strtolower($currentPermission) , $this->permissions[$this->getUserRoleId()])) {
                    return TRUE;
                } else {
					//echo $currentPermission.' NOT GRANTED'."<br/>";
                    $this->_debug_array[] = $currentPermission.' NOT GRANTED';
                }
            }
        }        
        //NOT by default
        return FALSE;
    }
    
    /**
     * Check if current controller/method has access and redirect if it's need
     * TODO : clean redirect in case
     * used by hook 
     * @access public
     * @return bool
     * 
     */
    public function Route(){
        $this->_IsApiAsk();
        
        if ($this->DontCheck)
            return TRUE;      
        if ( $this->IsLog() ) {
            // Check for access
            if (!$this->hasAccess()) {
                $this->routes_hisory[] = $this->controller . '/' . $this->action;
                $this->CI->session->set_userdata('routes',  $this->routes_hisory); 
                if ($this->CI->input->is_cli_request()){
                    echo 'you can\'t access to this method';
                    die();
                } else {
                    if ($this->ApiAsk){
                        $this->CI->_renderJson(403, ["message" => "Forbidden"]);
                        die();
                    }
                    return redirect('/Home/no_right');
                }
            } else {                  
                $this->_debug_array[] = $this->controller . '/' . $this->action.' GRANTED';
            }
        } else {
            if (!$this->hasAccess()) { //check gest page
                if ($this->CI->input->is_cli_request()){
                    echo 'you can\'t access to this method';
                    die();
                } else {
                    if ($this->ApiAsk){
                        $this->CI->_renderJson(403, ["message" => "Forbidden"]);
                        die();
                    }
                    return redirect('Home/login');
                }
                
            }
        }
    }



    /**
     * Check login for user
     *
     * @access public
     * @return bool
     * 
     */
    public function CheckLogin($data){
        //TODO : reprise de session ?
        //si c'est un appel API
        if (isset($data['api-key'] ) && $data['api-key'] !=  $this->CI->auth->_get('secretKey')){
            return false;
        }
        $this->usercheck = $this->CI->auth->login($data); 
        //MAKE JWT in case of use in front
        if (isset($this->usercheck->autorize) AND  $this->usercheck->autorize == TRUE ){
            return  $this->usercheck;
        } else {
            return $this->CI->lang->line('WRONG_ACCES').'<br/><span class="">'.$this->usercheck->msg.'</span>';
        }       
    }

    /**
     * Get Type
     *
     * @access public
     * @return bool
     * 
     */
    public function getType(){
        if (isset($this->usercheck->type)){
            return $this->usercheck->type;
        } else {
            return FALSE;
        }  
    }

    // --------------------------------------------------------------------
    
     /**
     * Get Name
     *
     * @access public
     * @return bool
     * 
     */
    public function GetUserName(){
        if (isset($this->usercheck->name)){
            return $this->usercheck->name;
        } else {
            return FALSE;
        }  
    }

    /**
     * Return the value of user id from the session.
     * Returns 0 if not logged in
     *
     * @access private
     * @return int
     */
    public function getUserId()
    {
        if (isset($this->usercheck->id))
            return $this->usercheck->id;
        return false;
    }

    /**
     * Return user role
     *
     * @return int
     */
    public function getUserRoleId()
    {
        if (isset($this->usercheck->role_id))
            return $this->usercheck->role_id;
        return false;
    }
    
    //liste des pages ne nécessitant pas de login
    public function getGuestPages()
    {
        return $this->guestPages;
    }

    public function _set($field,$value){
		$this->$field = $value;
	}

	public function _get($field){
		return $this->$field;
	}	

	function __destruct(){
		if ($this->_debug){
			unset($this->CI); //pour casser la recursivité d'affichage
			echo debug($this, __file__);
		}
	}
    
}
