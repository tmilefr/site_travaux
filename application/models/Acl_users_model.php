<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

/**
 * Acl_users_model
 * 
 * CORRECTIONS v2 :
 *  - verifyLogin() : bug critique corrigé (auth sans mdp valide)
 *  - verifyLogin() : crypt() remplacé par password_verify()
 *  - verifyLogin() : echo de debug supprimé
 *  - hashPassword() : nouvelle méthode centralisée pour hasher les mots de passe
 *  - needsRehash()  : détecte les anciens hash crypt() pour migration transparente
 */
class Acl_users_model extends Core_model {

	function __construct() {
		parent::__construct();

		$this->_set('table',     'acl_users');
		$this->_set('key',       'id');
		$this->_set('order',     'name');
		$this->_set('direction', 'desc');
		$this->_set('json',      'Acl_users.json');
		$this->_init_def();
	}

	// -----------------------------------------------------------------------

	/**
	 * Retourne le role_id d'un utilisateur
	 *
	 * @param  int $userId
	 * @return int
	 */
	public function getUserRoleId($userId = 0)
	{
		$query = $this->db->select('role_id')
			->from('acl_users u')
			->where('id', $userId)
			->get();
		$this->_debug_array[] = $this->db->last_query();

		if ($query->num_rows() > 0) {
			$row = $query->row_array();
			return $row['role_id'];
		}
		return 0;
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie les identifiants de connexion.
	 *
	 * CORRECTIONS :
	 *  1. La vérification du mot de passe contrôle maintenant réellement l'accès.
	 *  2. password_verify() remplace crypt() + sel fixe (bcrypt adaptatif).
	 *  3. Migration transparente : si l'ancien hash crypt() est détecté,
	 *     il est mis à jour silencieusement en bcrypt lors de la connexion.
	 *  4. Suppression du echo "Mot de passe correct !" (debug en prod).
	 *
	 * @param  string $login
	 * @param  string $password
	 * @return stdClass  ->autorize (bool), ->name, ->id, ->role_id, ->type
	 */
	public function verifyLogin($login, $password)
	{
		// Objet par défaut : accès refusé
		$usercheck            = new stdClass();
		$usercheck->autorize  = false;
		$usercheck->name      = 'nobody';
		$usercheck->type      = 'none';
		$usercheck->id        = 0;
		$usercheck->role_id   = 0;

		if (empty($login) || empty($password)) {
			return $usercheck;
		}

		$query = $this->db->select('*')
			->from('acl_users u')
			->where('login', $login)
			->limit(1)
			->get();
		$this->_debug_array[] = $this->db->last_query();

		if ($query->num_rows() === 0) {
			// Timing constant : on hash quand même pour éviter les timing attacks
			password_hash($password, PASSWORD_BCRYPT);
			return $usercheck;
		}

		$row = $query->row_array();

		// --- Vérification du mot de passe ---
		$passwordOk = false;

		if ($this->_isLegacyHash($row['password'])) {
			// Ancien hash crypt() : comparaison legacy puis migration
			if (hash_equals($row['password'], crypt($password, PASSWORD_SALT))) {
				$passwordOk = true;
				// Migration silencieuse vers bcrypt
				$this->_migratePassword($row['id'], $password);
			}
		} else {
			// Hash bcrypt moderne
			$passwordOk = password_verify($password, $row['password']);

			// Rehash si le coût bcrypt a été augmenté depuis
			if ($passwordOk && password_needs_rehash($row['password'], PASSWORD_BCRYPT)) {
				$this->_migratePassword($row['id'], $password);
			}
		}

		if (!$passwordOk) {
			return $usercheck;
		}

		// --- Connexion acceptée ---
		$usercheck->autorize = true;
		$usercheck->name     = $row['name'];
		$usercheck->id       = $row['id'];
		$usercheck->role_id  = $row['role_id'];
		$usercheck->type     = 'sys';

		return $usercheck;
	}

	// -----------------------------------------------------------------------

	/**
	 * Hashe un mot de passe en bcrypt (utilisé par element_password et les seeds).
	 *
	 * @param  string $plainPassword
	 * @return string
	 */
	public function hashPassword($plainPassword)
	{
		return password_hash($plainPassword, PASSWORD_BCRYPT);
	}

	// -----------------------------------------------------------------------
	// Méthodes privées
	// -----------------------------------------------------------------------

	/**
	 * Détecte si le hash stocké est un ancien hash crypt() (format DES/MD5/SHA).
	 * Les hash bcrypt commencent toujours par $2y$ ou $2a$.
	 *
	 * @param  string $hash
	 * @return bool
	 */
	private function _isLegacyHash($hash)
	{
		return strpos($hash, '$2y$') !== 0 && strpos($hash, '$2a$') !== 0;
	}

	/**
	 * Met à jour le hash en base vers bcrypt.
	 *
	 * @param  int    $userId
	 * @param  string $plainPassword
	 */
	private function _migratePassword($userId, $plainPassword)
	{
		$newHash = password_hash($plainPassword, PASSWORD_BCRYPT);
		$this->db->where('id', $userId)
		         ->update('acl_users', ['password' => $newHash]);
		$this->_debug_array[] = $this->db->last_query();
	}
}
