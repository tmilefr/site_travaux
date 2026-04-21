<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

/**
 * Familys_model
 *
 * CORRECTIONS v3 :
 *  - verifyLogin()    : bcrypt via PasswordAuthenticator, migration
 *                       transparente des anciens hashes crypt() et MD5.
 *  - verifyLogin()    : signature de retour en stdClass (autorize, name, id,
 *                       role_id, type), cohérente avec Acl_users_model et
 *                       consommée par Auth::Login.
 *  - verifyLoginAPI() : même signature stdClass. Utilisée pour l'auth Delta
 *                       (confiance dans le SSO externe, pas de mot de passe
 *                       local à vérifier).
 *  - Le fallback par défaut sur le role_id de famille (role_famille = 2)
 *    est désormais tiré de la config plutôt que hardcodé.
 */
class Familys_model extends Core_model
{
	/** @var string */
	const TABLE_NAME = 'famille';

	/** @var int  role_id attribué par défaut à une famille sans role_id en base */
	protected $default_family_role_id = 2;

	function __construct()
	{
		parent::__construct();

		$this->_set('table',     self::TABLE_NAME);
		$this->_set('key',       'id');
		$this->_set('order',     'login');
		$this->_set('direction', 'desc');
		$this->_set('json',      'Familys.json');

		// Lecture du role famille par défaut depuis la config, avec fallback
		$configured = $this->config->item('role_famille');
		if ($configured !== FALSE && $configured !== NULL) {
			$this->default_family_role_id = (int) $configured;
		}
	}

	// -----------------------------------------------------------------------
	// Recherches simples
	// -----------------------------------------------------------------------

	/**
	 * Récupère une famille par son identifiant.
	 *
	 * @param  int $id_fam
	 * @return stdClass|false
	 */
	function GetFamily($id_fam)
	{
		$query = $this->db->select('*')
			->from($this->table)
			->where('id', $id_fam)
			->get();
		$this->_debug_array[] = $this->db->last_query();

		if ($query->num_rows() > 0) {
			return $query->row();
		}
		return FALSE;
	}

	/**
	 * Récupère une famille par son e-mail (case-insensitive).
	 *
	 * @param  string $email
	 * @return stdClass|false
	 */
	function GetFamilyByLogin($email)
	{
		$email = strtolower(str_replace("\r\n", '', $email));
		$query = $this->db->select('*')
			->from($this->table)
			->where('LOWER(e_mail)', $email)
			->get();
		$this->_debug_array[] = $this->db->last_query();

		if ($query->num_rows() > 0) {
			return $query->row();
		}
		return FALSE;
	}

	/**
	 * Met à jour la civil_year d'une famille.
	 *
	 * @param  int    $id
	 * @param  string $civil_year
	 * @return void
	 */
	function SetCivilYears($id, $civil_year)
	{
		$this->db->set('civil_year', $civil_year);
		$this->db->where('id', $id);
		$this->db->update($this->table);
	}

	// -----------------------------------------------------------------------
	// Authentification
	// -----------------------------------------------------------------------

	/**
	 * Vérifie les identifiants d'une famille (connexion locale classique).
	 *
	 * @param  string $login
	 * @param  string $password
	 * @return stdClass  Objet avec ->autorize (bool), ->name, ->id, ->role_id, ->type, ->login
	 */
	function verifyLogin($login, $password)
	{
		$usercheck = $this->_buildGuestUsercheck();

		// Délégation à la couche partagée. allowMd5 = TRUE pour gérer les
		// très anciens comptes famille encore stockés en MD5.
		$this->load->library('PasswordAuthenticator', [], 'passauth');
		$row = $this->passauth->verify(self::TABLE_NAME, 'login', $login, $password, TRUE);

		foreach ($this->passauth->getDebug() as $msg) {
			$this->_debug_array[] = $msg;
		}

		if ($row === FALSE) {
			return $usercheck;
		}

		return $this->_populateUsercheck($row);
	}

	/**
	 * Vérifie l'existence d'une famille par idfamille (auth déléguée à Delta).
	 * Aucune vérification de mot de passe local : Delta a déjà authentifié
	 * l'utilisateur en amont, on se contente de retrouver la ligne locale.
	 *
	 * @param  mixed $idfamille
	 * @return stdClass  Objet avec ->autorize (bool), ->name, ->id, ->role_id, ->type, ->login
	 */
	function verifyLoginAPI($idfamille)
	{
		$usercheck = $this->_buildGuestUsercheck();

		if (empty($idfamille)) {
			return $usercheck;
		}

		$query = $this->db->select('*')
			->from($this->table)
			->where('idfamille', $idfamille)
			->get();
		$this->_debug_array[] = $this->db->last_query();

		if ($query->num_rows() === 0) {
			return $usercheck;
		}

		return $this->_populateUsercheck($query->row_array());
	}

	// -----------------------------------------------------------------------
	// Méthodes privées
	// -----------------------------------------------------------------------

	/**
	 * Construit un objet usercheck par défaut (non connecté).
	 *
	 * @return stdClass
	 */
	private function _buildGuestUsercheck()
	{
		$u           = new stdClass();
		$u->autorize = FALSE;
		$u->name     = 'nobody';
		$u->login    = '';
		$u->type     = 'none';
		$u->id       = 0;
		$u->role_id  = 0;
		return $u;
	}

	/**
	 * Construit un usercheck "connecté" à partir d'une ligne de la table famille.
	 *
	 * @param  array $row
	 * @return stdClass
	 */
	private function _populateUsercheck(array $row)
	{
		$u           = new stdClass();
		$u->autorize = TRUE;
		$u->login    = isset($row['login']) ? $row['login'] : '';
		// Le "name" affiché est le nom de famille, avec fallback sur le login.
		$u->name     = !empty($row['nom']) ? $row['nom'] : $u->login;
		$u->id       = (int) $row['id'];
		$u->role_id  = !empty($row['role_id']) ? (int) $row['role_id'] : $this->default_family_role_id;
		$u->type     = 'fam';
		return $u;
	}
}

/* End of file Familys_model.php */
/* Location: ./application/models/Familys_model.php */
