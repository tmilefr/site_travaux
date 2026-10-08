<?php

namespace App\Libraries;

use App\Models\Acl_roles_model;
use CodeIgniter\HTTP\RedirectResponse;

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
#[\AllowDynamicProperties]
class Acl
{
	protected $is_log        = FALSE;

	/** @var \CodeIgniter\Session\Session */
	protected $session;

	/** @var Acl_roles_model */
	protected $roles;

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
		'admwork_controller/validate_by_token',
		'cron/send_ref_validation_mails',  // ← accès par lien email
		'cron/send_new_session_alerts',
		'translations_controller/switch_lang'
	];

	/**
	 * FALSE par défaut → accès refusé si non authentifié.
	 * Mettre à TRUE dans les contrôleurs publics.
	 */
	protected $DontCheck    = FALSE;

	protected $_debug       = FALSE;
	protected $_debug_array = [];

	/** @var \stdClass */
	protected $usercheck    = NULL;

	// -----------------------------------------------------------------------

	/**
	 * Le contrôleur et l'action courants sont lus dans le routeur CI4.
	 */
	public function __construct()
	{
		$this->session = session();
		$this->roles   = model(Acl_roles_model::class);

		$router = service('router');
		$class  = $router->controllerName();
		if (is_string($class) && $class !== '') {
			$this->controller = strtolower(substr(strrchr('\\' . $class, '\\'), 1));
		}
		// Action = 2e segment de l'URL (Controleur/action/...), « index » par défaut
		$segments     = array_values(array_filter(explode('/', trim(service('request')->getPath(), '/')), 'strlen'));
		$this->action = strtolower($segments[1] ?? 'index');

		// Récupère l'objet utilisateur depuis la session
		$this->usercheck = $this->session->get('usercheck');

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
	public function Route(): ?RedirectResponse
	{
		if ($this->DontCheck) {
			return NULL;
		}
		$currentPage = $this->controller . '/' . $this->action;
		if ($this->IsLog()) {
			if (!$this->hasAccess()) {
				if ($currentPage !== '/home/no_right'
					&& !in_array($currentPage, $this->getGuestPages())
				) {
					$this->routes_hisory[] = $currentPage;
					$this->session->set('routes', $this->routes_hisory);
					return redirect()->to(site_url('Home/no_right'));
				}
			} else {
				if (config('Travaux')->maintenance == TRUE
					&& $currentPage !== 'home/maintenance'
				) {
					if ($this->getType() !== 'sys') {
						return redirect()->to(site_url('Home/maintenance'));
					}
				}
				$this->_debug_array[] = $currentPage . ' GRANTED';
			}
		} else {
			if (in_array($currentPage, $this->getGuestPages()) && !in_array($currentPage, ['home/login', 'home/index'])) { //sauf login
				return NULL;
			}
			if (is_cli()) {
				echo "no access for $currentPage\n";
			} elseif ($currentPage !== 'home/login') {
				return redirect()->to(site_url('Home/login'));
			}
		}

		return NULL;
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
		$this->session->remove('acl_perms_' . $previousRoleId);

		$connectedUser = service('auth')->Login($data);

		if (empty($connectedUser->autorize) || $connectedUser->autorize !== TRUE) {
			return tr('WRONG_ACCES');
		}

		// Construction de l'objet usercheck à partir du connected_user
		$usercheck           = new \stdClass();
		$usercheck->autorize = TRUE;
		$usercheck->type     = $connectedUser->type;
		$usercheck->name     = $connectedUser->name;
		$usercheck->id       = (int) $connectedUser->id;
		$usercheck->role_id  = (int) $connectedUser->role_id;

		$this->session->set('usercheck', $usercheck);
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
			$this->session->remove('acl_perms_' . $this->usercheck->role_id);
		}
		$this->session->destroy();
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
		$this->usercheck           = new \stdClass();
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
		$cached   = $this->session->get($cacheKey);

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
		$permissions = $this->roles->getRolePermissions($roleId);
		$cacheKey    = 'acl_perms_' . $roleId;
		$this->session->set($cacheKey, $permissions);
		$this->_debug_array[] = 'ACL permissions: chargées depuis BDD et mises en cache (role_id=' . $roleId . ')';
		return $permissions;
	}

	// -----------------------------------------------------------------------

	public function __destruct()
	{
		if ($this->_debug) {
			d($this);
		}
	}
}

