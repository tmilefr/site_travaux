<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Acl
 * Gestion de la connexion et de la sécurité du site.
 *
 * CORRECTIONS v3 :
 *  - CheckLogin() délègue à Auth::Login() : cascade acl_users → famille.
 *    Avant v3, l'interface web ne testait que acl_users, coupant l'accès
 *    à toutes les familles.
 *  - CheckLogin() synchronise 'usercheck' (utilisé par Acl) et
 *    'connected_user' (positionné par Auth via la session) pour un état
 *    cohérent entre interface web et API.
 *  - DontCheck reste à FALSE par défaut (secure by default).
 *  - Permissions en cache session par role_id, invalidées à la déconnexion.
 *  - Auth est autoloadé globalement (config/autoload.php) : on accède à
 *    $this->CI->auth sans le charger ici, ce qui évite les problèmes
 *    d'ordre d'initialisation entre le hook Loginchecker et les contrôleurs
 *    (erreurs "Undefined property: Xxx_controller::$auth").
 *
 * @package    WebApp
 * @subpackage Libraries
 * @category   Security
 */
class Acl
{
	protected $is_log        = FALSE;

	/** @var CI_Controller */
	protected $CI;

	protected $userId        = NULL;
	protected $userRoleId    = NULL;
	protected $controller    = NULL;
	protected $action        = NULL;
	protected $permissions   = [];
	protected $routes_hisory = [];

	protected $guestPages = [
		'home/logout',
		'home/login',
		'home/no_right',
		'home/index',
		'home/myaccount',
		'home/about',
		'home/maintenance',
		'home',
	];

	/**
	 * FALSE par défaut → accès refusé si non authentifié.
	 * Mettre à TRUE dans les contrôleurs publics.
	 */
	protected $DontCheck    = FALSE;

	protected $_debug       = FALSE;
	protected $_debug_array = [];

	/** @var stdClass */
	protected $usercheck    = NULL;

	// -----------------------------------------------------------------------

	/**
	 * Constructeur
	 *
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		$this->CI = &get_instance();
		$this->CI->load->library('session');
		$this->CI->load->library('auth');
		$this->CI->load->helper('url');
		$this->CI->load->model('Acl_roles_model');
		$this->CI->load->model('Acl_users_model');

		$this->controller = strtolower($this->CI->uri->rsegment(1));
		$this->action     = strtolower($this->CI->uri->rsegment(2));

		// Récupère l'objet utilisateur depuis la session
		$this->usercheck = $this->CI->session->userdata('usercheck');

		if (!isset($this->usercheck->autorize)) {
			$this->_initGuestUsercheck();
		}

		if ($this->IsLog()) {
			$this->permissions = $this->_getPermissionsFromCache();
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Indique si l'utilisateur est connecté.
	 *
	 * @return bool
	 */
	public function IsLog()
	{
		return isset($this->usercheck->autorize) && $this->usercheck->autorize === TRUE;
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie si le contrôleur/action courant est autorisé pour le rôle.
	 *
	 * @param  string|null $currentPermission
	 * @return bool
	 */
	public function hasAccess($currentPermission = NULL)
	{
		if ($this->DontCheck) {
			return TRUE;
		}

		if ($this->IsLog()) {
			if (!$currentPermission) {
				$currentPermission = $this->controller . '/' . $this->action;
			}

			$roleId = $this->getUserRoleId();
			if (isset($this->permissions[$roleId]) && count($this->permissions[$roleId]) > 0) {
				if (in_array(strtolower($currentPermission), $this->permissions[$roleId])) {
					return TRUE;
				}
				$this->_debug_array[] = $currentPermission . ' NOT GRANTED';
			}
		}
		return FALSE;
	}

	// -----------------------------------------------------------------------

	/**
	 * Route la requête : redirige si non autorisé, gère le mode maintenance.
	 *
	 * @return void|mixed
	 */
	public function Route()
	{
		if ($this->DontCheck) {
			return TRUE;
		}

		if ($this->IsLog()) {
			if (!$this->CI->acl->hasAccess()) {
				$currentPage = $this->controller . '/' . $this->action;
				if ($currentPage !== '/home/no_right'
					&& !in_array($currentPage, $this->CI->acl->getGuestPages())
				) {
					$this->routes_hisory[] = $currentPage;
					$this->CI->session->set_userdata('routes', $this->routes_hisory);
					return redirect('/Home/no_right');
				}
			} else {
				if ($this->CI->config->item('maintenance') == TRUE
					&& $this->controller . '/' . $this->action !== 'home/maintenance'
				) {
					if ($this->getType() !== 'sys') {
						return redirect('/Home/maintenance');
					}
				}
				$this->_debug_array[] = $this->controller . '/' . $this->action . ' GRANTED';
			}
		} else {
			if ($this->controller . '/' . $this->action !== 'home/login') {
				return redirect('/Home/login');
			}
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie les identifiants saisis via le formulaire de connexion web.
	 *
	 * Délègue à Auth::Login() pour bénéficier de la cascade acl_users →
	 * famille et (si type_cnx fourni) du SSO Delta.
	 *
	 * @param  array $data  ['login', 'password', 'type_cnx' (facultatif)]
	 * @return string|null  Message d'erreur à afficher, ou null si succès
	 */
	public function CheckLogin($data)
	{
		// Invalide d'abord le cache de l'ancien rôle éventuel
		$previousRoleId = isset($this->usercheck->role_id) ? $this->usercheck->role_id : 0;
		$this->CI->session->unset_userdata('acl_perms_' . $previousRoleId);

		// Garde-fou : Auth est normalement autoloadée via config/autoload.php.
		// On recharge quand même au cas où, l'appel est idempotent côté CI.
		if (!isset($this->CI->auth) || !is_object($this->CI->auth)) {
			$this->CI->load->library('Auth');
		}

		$connectedUser = $this->CI->auth->Login($data);

		if (empty($connectedUser->autorize) || $connectedUser->autorize !== TRUE) {
			return $this->CI->lang->line('WRONG_ACCES');
		}

		// Construction de l'objet usercheck à partir du connected_user
		$usercheck           = new stdClass();
		$usercheck->autorize = TRUE;
		$usercheck->type     = $connectedUser->type;
		$usercheck->name     = $connectedUser->name;
		$usercheck->id       = (int) $connectedUser->id;
		$usercheck->role_id  = (int) $connectedUser->role_id;

		$this->CI->session->set_userdata('usercheck', $usercheck);
		$this->usercheck = $usercheck;

		// Pré-charger les permissions en session dès la connexion
		$this->permissions = $this->_loadAndCachePermissions($usercheck->role_id);

		return NULL;
	}

	// -----------------------------------------------------------------------

	/**
	 * Déconnecte l'utilisateur et purge le cache des permissions.
	 *
	 * @return void
	 */
	public function Logout()
	{
		if ($this->IsLog()) {
			$this->CI->session->unset_userdata('acl_perms_' . $this->usercheck->role_id);
		}
		$this->CI->session->sess_destroy();
	}

	// -----------------------------------------------------------------------
	// Accesseurs
	// -----------------------------------------------------------------------

	/** @return string|false */
	public function getType()
	{
		return $this->IsLog() ? $this->usercheck->type : FALSE;
	}

	/** @return string|false */
	public function GetUserName()
	{
		return $this->IsLog() ? $this->usercheck->name : FALSE;
	}

	/** @return int|false */
	public function getUserId()
	{
		return $this->IsLog() ? $this->usercheck->id : FALSE;
	}

	/** @return int|false */
	public function getUserRoleId()
	{
		return $this->IsLog() ? $this->usercheck->role_id : FALSE;
	}

	/** @return array */
	public function getGuestPages()
	{
		return $this->guestPages;
	}

	// -----------------------------------------------------------------------

	public function _set($field, $value) { $this->$field = $value; }
	public function _get($field)         { return $this->$field;   }

	// -----------------------------------------------------------------------
	// Méthodes privées
	// -----------------------------------------------------------------------

	/**
	 * Initialise un objet usercheck "invité" (non connecté).
	 *
	 * @return void
	 */
	private function _initGuestUsercheck()
	{
		$this->usercheck           = new stdClass();
		$this->usercheck->autorize = FALSE;
		$this->usercheck->type     = 'none';
		$this->usercheck->name     = 'nobody';
		$this->usercheck->id       = 0;
		$this->usercheck->role_id  = 0;
	}

	/**
	 * Retourne les permissions depuis le cache session, ou les recharge.
	 *
	 * @return array
	 */
	private function _getPermissionsFromCache()
	{
		$cacheKey = 'acl_perms_' . $this->usercheck->role_id;
		$cached   = $this->CI->session->userdata($cacheKey);

		if ($cached !== NULL && is_array($cached)) {
			$this->_debug_array[] = 'ACL permissions: cache hit (role_id=' . $this->usercheck->role_id . ')';
			return $cached;
		}

		return $this->_loadAndCachePermissions($this->usercheck->role_id);
	}

	/**
	 * Charge les permissions depuis la BDD et les met en cache session.
	 *
	 * @param  int $roleId
	 * @return array
	 */
	private function _loadAndCachePermissions($roleId)
	{
		$permissions = $this->CI->Acl_roles_model->getRolePermissions($roleId);
		$cacheKey    = 'acl_perms_' . $roleId;
		$this->CI->session->set_userdata($cacheKey, $permissions);
		$this->_debug_array[] = 'ACL permissions: chargées depuis BDD et mises en cache (role_id=' . $roleId . ')';
		return $permissions;
	}

	// -----------------------------------------------------------------------

	public function __destruct()
	{
		if ($this->_debug) {
			unset($this->CI);
			echo debug($this, __FILE__);
		}
	}
}

/* End of file Acl.php */
/* Location: ./application/libraries/Acl.php */
