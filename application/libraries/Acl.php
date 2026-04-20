<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Acl
 * Gestion de la connexion et de la sécurité du site.
 *
 * CORRECTIONS v2 :
 *  - DontCheck passe à FALSE par défaut → "secure by default"
 *    (mettre à TRUE explicitement dans les contrôleurs publics si besoin)
 *  - Cache des permissions en session par role_id pour éviter une requête SQL
 *    à chaque page (clé de cache invalidée automatiquement à la déconnexion)
 *  - CheckLogin() : correction du bug de lecture usercheck (on lisait $this->usercheck
 *    avant que la session soit rechargée)
 *  - Ajout de logout() pour nettoyer le cache des permissions
 */
class Acl
{
	protected $is_log       = false;
	protected $CI;
	protected $userId       = NULL;
	protected $userRoleId   = NULL;
	protected $controller   = NULL;
	protected $action       = NULL;
	protected $permissions  = [];
	protected $guestPages   = [
		'home/logout',
		'home/login',
		'home/no_right',
		'home/index',
		'home/Myaccount',
		'home/about',
		'home/maintenance',
		'home',
	];

	/**
	 * CORRECTION : FALSE par défaut → accès refusé si non authentifié.
	 * Mettre à TRUE dans les contrôleurs publics (page d'accueil, landing page…).
	 */
	protected $DontCheck    = FALSE;

	protected $_debug       = FALSE;
	protected $_debug_array = [];
	protected $usercheck    = NULL;

	// -----------------------------------------------------------------------

	/**
	 * Constructeur
	 *
	 * CORRECTIONS :
	 *  - Permissions chargées depuis la session si disponibles (cache par role_id).
	 *
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		$this->CI = &get_instance();
		$this->CI->load->library('session');
		$this->CI->load->helper('url');
		$this->CI->load->model('Acl_roles_model');
		$this->CI->load->model('Acl_users_model');

		$this->controller   = strtolower($this->CI->uri->rsegment(1));
		$this->action       = strtolower($this->CI->uri->rsegment(2));
		$this->routes_hisory = [];

		// Récupère l'objet utilisateur depuis la session
		$this->usercheck = $this->CI->session->userdata('usercheck');

		if (!isset($this->usercheck->autorize)) {
			$this->_initGuestUsercheck();
		}

		// Chargement des permissions avec cache session
		if ($this->IsLog()) {
			$this->permissions = $this->_getPermissionsFromCache();
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie si l'utilisateur est connecté.
	 *
	 * @return bool
	 */
	public function IsLog()
	{
		return isset($this->usercheck->autorize) && $this->usercheck->autorize === true;
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie si le contrôleur/action courant est accessible selon le rôle.
	 *
	 * @param  string|null $currentPermission
	 * @return bool
	 */
	public function hasAccess($currentPermission = null)
	{
		if ($this->DontCheck)
			return TRUE;

		if ($this->IsLog()) {
			if (!$currentPermission)
				$currentPermission = $this->controller . '/' . $this->action;

			$roleId = $this->getUserRoleId();
			if (isset($this->permissions[$roleId]) && count($this->permissions[$roleId]) > 0) {
				if (in_array(strtolower($currentPermission), $this->permissions[$roleId])) {
					return TRUE;
				} else {
					$this->_debug_array[] = $currentPermission . ' NOT GRANTED';
				}
			}
		}
		return FALSE;
	}

	// -----------------------------------------------------------------------

	/**
	 * Route la requête : redirige si non autorisé, gère la maintenance.
	 *
	 * @return void|redirect
	 */
	public function Route()
	{
		if ($this->DontCheck)
			return TRUE;

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
				if ($this->CI->config->item('maintenance') == true
					&& $this->controller . '/' . $this->action !== 'home/maintenance'
				) {
					if ($this->getType() !== 'sys')
						return redirect('/Home/maintenance');
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
	 * Vérifie les identifiants et ouvre la session.
	 *
	 * CORRECTION : on relit $usercheck depuis la variable locale (pas $this->usercheck
	 * qui n'est pas encore rechargé depuis la session au moment de l'appel).
	 *
	 * @param  array $data  ['login' => ..., 'password' => ...]
	 * @return string|null  Message d'erreur ou null si OK
	 */
	public function CheckLogin($data)
	{
		$usercheck = $this->CI->Acl_users_model->verifyLogin($data['login'], $data['password']);
		$this->CI->session->set_userdata('usercheck', $usercheck);

		// Invalider le cache des permissions de l'ancien rôle éventuel
		$this->CI->session->unset_userdata('acl_perms_' . ($this->usercheck->role_id ?? 0));

		// Mettre à jour l'objet courant
		$this->usercheck = $usercheck;

		if (!$usercheck->autorize) {
			return $this->CI->lang->line('WRONG_ACCES');
		}

		// Pré-charger les permissions en session dès la connexion
		$this->permissions = $this->_loadAndCachePermissions($usercheck->role_id);

		return null;
	}

	// -----------------------------------------------------------------------

	/**
	 * Déconnecte l'utilisateur et nettoie le cache des permissions.
	 */
	public function Logout()
	{
		if ($this->IsLog()) {
			$this->CI->session->unset_userdata('acl_perms_' . $this->usercheck->role_id);
		}
		$this->CI->session->sess_destroy();
	}

	// -----------------------------------------------------------------------

	/**
	 * @return string|false
	 */
	public function getType()
	{
		return $this->IsLog() ? $this->usercheck->type : FALSE;
	}

	/**
	 * @return string|false
	 */
	public function GetUserName()
	{
		return $this->IsLog() ? $this->usercheck->name : FALSE;
	}

	/**
	 * @return int|false
	 */
	public function getUserId()
	{
		return $this->IsLog() ? $this->usercheck->id : FALSE;
	}

	/**
	 * @return int|false
	 */
	public function getUserRoleId()
	{
		return $this->IsLog() ? $this->usercheck->role_id : FALSE;
	}

	/**
	 * @return array
	 */
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
	 */
	private function _initGuestUsercheck()
	{
		$this->usercheck           = new StdClass();
		$this->usercheck->autorize = false;
		$this->usercheck->type     = 'none';
		$this->usercheck->name     = 'nobody';
		$this->usercheck->id       = 0;
		$this->usercheck->role_id  = 0;
	}

	/**
	 * Retourne les permissions depuis le cache session, ou les charge depuis la BDD.
	 *
	 * CORRECTION : évite une requête SQL à chaque requête HTTP pour l'utilisateur connecté.
	 *
	 * @return array
	 */
	private function _getPermissionsFromCache()
	{
		$cacheKey = 'acl_perms_' . $this->usercheck->role_id;
		$cached   = $this->CI->session->userdata($cacheKey);

		if ($cached !== null && is_array($cached)) {
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
