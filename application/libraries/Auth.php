<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/* Lib pour JWT */
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Auth
 *
 * Factory d'authentification pour l'API et pour l'interface web.
 *
 * CORRECTIONS v3 :
 *  - Les consommateurs verifyLogin() / verifyLoginAPI() renvoient désormais
 *    un stdClass : on lit donc $row->autorize, $row->id, $row->role_id,
 *    $row->login et non plus $row['id'], $row['role_id'], etc.
 *  - Le case DELTA hashe maintenant le mot de passe en bcrypt avant de le
 *    stocker localement (plus de crypt() + PASSWORD_SALT qui annulait la
 *    migration transparente à chaque login Delta).
 *  - Les erreurs JWT ne fuient plus le détail interne au client (message
 *    générique, erreur réelle loggée côté serveur).
 *  - Méthode Login() structurée par handlers privés pour chaque type_cnx.
 *
 * @package    WebApp
 * @subpackage Libraries
 * @category   Factory
 */
class Auth
{
	/** @var CI_Controller */
	public $CI;

	/** Connexion delta-enfance */
	protected $api = [
		'base_url'   => 'https://delta-enfance3.fr/familleabcm/ABCMRegios68200/',
		'user_agent' => 'abcmschule',
	];

	/** @var stdClass */
	protected $connected_user = NULL;

	/** @var bool */
	protected $_debug = FALSE;

	/** @var array */
	protected $msg = [];

	/** @var int  rôle par défaut quand une famille n'a pas de role_id en base */
	protected $role_famille = 2;

	/** @var string  Clé HMAC pour la signature des JWT */
	protected $secretKey = NULL;

	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct($config = [])
	{
		$this->CI = &get_instance();
		$this->CI->LoadModel('Acl_users_model');
		$this->CI->LoadModel('Familys_model');
		$this->CI->load->library('RestClient', $this->api);

		$this->CI->config->load('secured');
		$this->secretKey = defined('API_KEY') ? API_KEY : '';

		// Lecture d'un role_famille configurable
		$configured = $this->CI->config->item('role_famille');
		if ($configured !== FALSE && $configured !== NULL) {
			$this->role_famille = (int) $configured;
		}

		$this->Init();
	}

	// -----------------------------------------------------------------------

	/**
	 * Réinitialise (ou charge) l'objet connected_user depuis la session.
	 *
	 * @return void
	 */
	public function Init()
	{
		$this->connected_user = $this->CI->session->userdata('connected_user');
		if (!isset($this->connected_user->autorize)) {
			$this->connected_user = $this->_guestUser();
		}
	}

	// -----------------------------------------------------------------------
	// JWT
	// -----------------------------------------------------------------------

	/**
	 * Encode un JWT représentant l'utilisateur connecté.
	 *
	 * @return void
	 */
	public function EncodeJWT()
	{
		try {
			$issuer_claim    = __CLASS__;
			$audience_claim  = 'API access';
			$issuedat_claim  = time();
			$notbefore_claim = $issuedat_claim;
			$expire_claim    = $issuedat_claim + 6000; // TODO : rendre configurable

			// On n'inclut pas un token antérieur dans le nouveau token
			$this->connected_user->token = '';

			$token = [
				'iss'  => $issuer_claim,
				'aud'  => $audience_claim,
				'iat'  => $issuedat_claim,
				'nbf'  => $notbefore_claim,
				'exp'  => $expire_claim,
				'data' => $this->connected_user,
			];

			$jwt = JWT::encode($token, $this->secretKey, 'HS256');

			$this->connected_user->token        = $jwt;
			$this->connected_user->expireAt     = $expire_claim;
		} catch (Exception $e) {
			log_message('error', 'Auth::EncodeJWT failed: ' . $e->getMessage());
			echo json_encode(['message' => 'Authentication token generation failed']);
			http_response_code(401);
			die;
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Décode un JWT et restaure l'utilisateur connecté dans la session.
	 *
	 * @param  string $token
	 * @return void
	 */
	public function DecodeJWT($token)
	{
		try {
			$decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

			if (isset($decoded->data)) {
				$this->connected_user->autorize = $decoded->data->autorize;
				$this->connected_user->type     = $decoded->data->type;
				$this->connected_user->name     = $decoded->data->name;
				$this->connected_user->id       = $decoded->data->id;
				$this->connected_user->role_id  = $decoded->data->role_id;
				$this->connected_user->msg      = $this->CI->lang->line('JWT_ACCESS');
			} else {
				$this->connected_user->msg = $this->CI->lang->line('NO_JWT_ACCESS');
			}
			$this->CI->session->set_userdata('connected_user', $this->connected_user);
		} catch (Exception $e) {
			log_message('error', 'Auth::DecodeJWT failed: ' . $e->getMessage());
			echo json_encode(['message' => 'Invalid or expired token']);
			http_response_code(401);
			die;
		}
	}

	// -----------------------------------------------------------------------
	// Login
	// -----------------------------------------------------------------------

	/**
	 * Point d'entrée d'authentification. Dispatche selon le type_cnx.
	 *
	 * @param  array $data  ['login', 'password', 'type_cnx' (NORM|DELTA)]
	 * @return stdClass     $this->connected_user
	 */
	public function Login($data)
	{
		if (!isset($data['type_cnx'])) {
			$data['type_cnx'] = 'NORM'; // API par défaut
		}

		switch ($data['type_cnx']) {
			case 'NORM':
				$this->_loginNormal($data);
				break;

			case 'DELTA':
				$this->_loginDelta($data);
				break;

			default:
				$this->connected_user->msg = Lang('ERROR_CNX_USER');
		}

		if (!empty($this->connected_user->autorize) && $this->connected_user->autorize === TRUE) {
			$this->EncodeJWT();
			$this->CI->session->set_userdata('connected_user', $this->connected_user);
		}

		return $this->connected_user;
	}

	// -----------------------------------------------------------------------
	// Handlers d'authentification par type
	// -----------------------------------------------------------------------

	/**
	 * Connexion locale : cascade acl_users -> familys.
	 *
	 * @param  array $data
	 * @return void
	 */
	private function _loginNormal(array $data)
	{
		// 1) Compte admin (acl_users)
		$usercheck = $this->CI->Acl_users_model->verifyLogin($data['login'], $data['password']);
		$this->msg[] = $this->CI->Acl_users_model->_get('_debug_array');

		if (!empty($usercheck->autorize) && $usercheck->autorize === TRUE) {
			$this->_applyUsercheck($usercheck);
			return;
		}

		// 2) Famille (famille)
		$usercheck = $this->CI->Familys_model->verifyLogin($data['login'], $data['password']);
		$this->msg[] = $this->CI->Familys_model->_get('_debug_array');

		if (!empty($usercheck->autorize) && $usercheck->autorize === TRUE) {
			$this->_applyUsercheck($usercheck);
			return;
		}

		// 3) Échec des deux
		$this->connected_user->msg = Lang('ERROR_CNX_USER');
	}

	/**
	 * Connexion Delta : SSO externe, création/synchro de la famille locale.
	 *
	 * @param  array $data
	 * @return void
	 */
	private function _loginDelta(array $data)
	{
		$result = $this->CI->restclient->get($data['login'] . '/' . urlencode($data['password']));

		if ($result->error) {
			$this->connected_user->msg = $result->error;
			return;
		}

		$res = json_decode($result->response);

		// Exemple de réponse attendue :
		// { "auth":200, "family":"LARESSER BURGELIN", "adresse":"...", "cp":"...",
		//   "city":"...", "email":"...", "idfamille":168, "ecole1":"Ecole : Mulhouse,Nombre : 1" }

		if (!isset($res->auth) || $res->auth !== 200) {
			$this->connected_user->msg = Lang('ERROR_CNX_USER');
			return;
		}

		if (empty($res->idfamille)) {
			$this->connected_user->msg = Lang('ERROR_CNX_USER');
			return;
		}

		$usercheck = $this->CI->Familys_model->verifyLoginAPI($res->idfamille);

		if (!empty($usercheck->autorize) && $usercheck->autorize === TRUE) {
			// Famille connue localement → mise à jour des champs depuis Delta
			$this->_applyUsercheck($usercheck);
			$this->_syncFamilyFromDelta($usercheck->id, $res, $data['password'], /*isNew*/ FALSE);
			$this->connected_user->msg = Lang('OK_UPDATE_ACCES_API');
			return;
		}

		// Famille inconnue : on crée localement (on fait confiance à Delta)
		$newId = $this->_createFamilyFromDelta($res, $data['password']);
		if ($newId) {
			$created           = new stdClass();
			$created->autorize = TRUE;
			$created->type     = 'fam';
			$created->login    = $res->family;
			$created->name     = $res->family;
			$created->id       = (int) $newId;
			$created->role_id  = $this->role_famille;

			$this->_applyUsercheck($created);
			$this->connected_user->msg = Lang('OK_CREATE_ACCES_API');
		} else {
			$this->connected_user->msg = Lang('ERROR_CNX_USER');
			log_message('error', 'Auth::_loginDelta : échec création famille pour idfamille=' . $res->idfamille);
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Copie les champs utiles de $usercheck (stdClass renvoyé par les modèles)
	 * dans $this->connected_user.
	 *
	 * @param  stdClass $usercheck
	 * @return void
	 */
	private function _applyUsercheck($usercheck)
	{
		$this->connected_user->autorize = TRUE;
		$this->connected_user->type     = $usercheck->type;
		$this->connected_user->role_id  = !empty($usercheck->role_id) ? (int) $usercheck->role_id : $this->role_famille;
		// Pour le nom affiché on privilégie le login côté "sys" (historique) et
		// le nom côté "fam". L'ancien code exposait $row['login'] dans les
		// deux cas — on conserve ce comportement via $usercheck->login.
		$this->connected_user->name     = !empty($usercheck->login) ? $usercheck->login : $usercheck->name;
		$this->connected_user->id       = (int) $usercheck->id;
		$this->connected_user->msg      = '';
	}

	// -----------------------------------------------------------------------

	/**
	 * Met à jour une famille existante avec les données fraîches de Delta.
	 *
	 * @param  int       $id
	 * @param  stdClass  $res
	 * @param  string    $plainPassword
	 * @param  bool      $isNew
	 * @return void
	 */
	private function _syncFamilyFromDelta($id, $res, $plainPassword, $isNew)
	{
		$this->CI->load->library('PasswordAuthenticator', [], 'passauth');

		$row              = [];
		$row['name']      = $res->family;
		$row['adresse']   = $res->adresse;
		$row['cp']        = $res->cp;
		$row['ville']     = $res->city;
		$row['e_mail']    = $res->email;
		$row['password']  = $this->CI->passauth->hash($plainPassword);
		$row['updated']   = date('Y-m-d H:i:s');
		$row['ecole']     = (strpos(strtolower($res->ecole1), 'mulhouse') !== FALSE) ? 'M' : 'L';

		$this->CI->Familys_model->_set('key_value', $id);
		$this->CI->Familys_model->_set('datas', $row);
		$this->CI->Familys_model->put();
	}

	/**
	 * Crée une nouvelle famille à partir des données Delta.
	 *
	 * @param  stdClass $res
	 * @param  string   $plainPassword
	 * @return int|false  ID créé ou FALSE en cas d'échec
	 */
	private function _createFamilyFromDelta($res, $plainPassword)
	{
		$this->CI->load->library('PasswordAuthenticator', [], 'passauth');

		$row               = [];
		$row['login']      = $res->family;
		$row['nom']        = $res->family;
		$row['adresse']    = $res->adresse;
		$row['cp']         = $res->cp;
		$row['ville']      = $res->city;
		$row['e_mail']     = $res->email;
		$row['password']   = $this->CI->passauth->hash($plainPassword);
		$row['updated']    = date('Y-m-d H:i:s');
		$row['created']    = $row['updated'];
		$row['idfamille']  = $res->idfamille;
		$row['ecole']      = (strpos(strtolower($res->ecole1), 'mulhouse') !== FALSE) ? 'M' : 'L';

		$id = $this->CI->Familys_model->post($row);
		return $id ? (int) $id : FALSE;
	}

	// -----------------------------------------------------------------------

	/**
	 * Construit un objet connected_user "invité".
	 *
	 * @return stdClass
	 */
	private function _guestUser()
	{
		$u           = new stdClass();
		$u->autorize = FALSE;
		$u->type     = 'none';
		$u->name     = 'nobody';
		$u->id       = 0;
		$u->role_id  = 0;
		$u->msg      = '';
		return $u;
	}

	// -----------------------------------------------------------------------

	public function _set($field, $value) { $this->$field = $value; }
	public function _get($field)         { return $this->$field;   }

	// -----------------------------------------------------------------------

	function __destruct()
	{
		if ($this->_debug) {
			unset($this->CI);
			echo debug($this, __FILE__);
		}
	}
}

/* End of file Auth.php */
/* Location: ./application/libraries/Auth.php */
