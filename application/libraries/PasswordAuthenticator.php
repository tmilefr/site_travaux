<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * PasswordAuthenticator
 *
 * Service mutualisé de vérification et de migration des mots de passe.
 * Utilisé par Acl_users_model et Familys_model pour factoriser la logique
 * d'authentification, le hashage bcrypt et la migration transparente des
 * anciens hashes (crypt + PASSWORD_SALT, MD5 pour les familles).
 *
 * Chaque modèle appelle verify() en lui passant le nom de la table, la colonne
 * identifiant l'utilisateur (login, e-mail…) et la valeur saisie. Le service
 * renvoie la ligne de la base sous forme d'array si l'authentification
 * réussit, FALSE sinon. En cas de hash legacy valide, il met immédiatement à
 * jour la base avec un hash bcrypt.
 *
 * Cette classe ne modifie jamais l'objet $usercheck / $connected_user : c'est
 * à l'appelant (modèle) de construire son objet de retour à partir de la ligne
 * retournée, ce qui préserve la séparation des responsabilités.
 *
 * @package     WebApp
 * @subpackage  Libraries
 * @category    Security
 */
class PasswordAuthenticator
{
	/** @var CI_Controller */
	protected $CI;

	/** @var bool */
	protected $_debug = FALSE;

	/** @var array */
	protected $_debug_array = [];

	/**
	 * Constructor
	 *
	 * @param array $config  ignoré, présent pour la compatibilité CI
	 */
	public function __construct($config = [])
	{
		$this->CI = &get_instance();
		$this->CI->load->database();
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie un couple (login, password) dans une table arbitraire.
	 *
	 * La méthode :
	 *   1. Recherche la ligne correspondant à la valeur d'identifiant donnée.
	 *   2. Teste le mot de passe en bcrypt, puis (si legacy) en crypt(), puis
	 *      (si $allowMd5 === TRUE) en MD5.
	 *   3. Si un hash legacy valide est détecté, met à jour la base avec un
	 *      hash bcrypt frais.
	 *   4. Si le hash bcrypt doit être ré-hashé (coût augmenté), effectue la
	 *      mise à jour.
	 *
	 * En cas d'identifiant introuvable, on exécute quand même un password_hash
	 * sur la valeur saisie pour conserver un temps de réponse constant
	 * (protection contre les attaques par timing d'énumération d'utilisateurs).
	 *
	 * @param  string $table       Nom de la table (ex. 'acl_users')
	 * @param  string $loginField  Nom de la colonne d'identifiant (ex. 'login')
	 * @param  string $loginValue  Valeur saisie par l'utilisateur
	 * @param  string $password    Mot de passe en clair saisi
	 * @param  bool   $allowMd5    TRUE pour accepter un ancien hash MD5 (familles)
	 * @return array|false         Ligne de la table si succès, FALSE sinon
	 */
	public function verify($table, $loginField, $loginValue, $password, $allowMd5 = FALSE)
	{
		if (empty($loginValue) || empty($password)) {
			// Timing constant même sur paramètres vides
			password_hash((string) $password, PASSWORD_BCRYPT);
			return FALSE;
		}

		$query = $this->CI->db->select('*')
			->from($table)
			->where($loginField, $loginValue)
			->limit(1)
			->get();

		$this->_debug_array[] = $this->CI->db->last_query();

		if ($query->num_rows() === 0) {
			// Protection contre les attaques par timing d'énumération
			password_hash($password, PASSWORD_BCRYPT);
			return FALSE;
		}

		$row    = $query->row_array();
		$stored = isset($row['password']) ? (string) $row['password'] : '';

		if ($stored === '') {
			return FALSE;
		}

		$passwordOk = FALSE;

		if ($this->_isBcryptHash($stored)) {
			// Hash bcrypt moderne
			$passwordOk = password_verify($password, $stored);

			if ($passwordOk && password_needs_rehash($stored, PASSWORD_BCRYPT)) {
				$this->_updatePasswordHash($table, $row['id'], $password);
				$row['password'] = $this->hash($password);
			}
		} else {
			// Legacy crypt() avec sel fixe
			if (defined('PASSWORD_SALT') && hash_equals($stored, crypt($password, PASSWORD_SALT))) {
				$passwordOk = TRUE;
				$this->_updatePasswordHash($table, $row['id'], $password);
				$row['password'] = $this->hash($password);
			}
			// Legacy MD5 (uniquement pour la table famille historiquement)
			elseif ($allowMd5 && md5($password) === $stored) {
				$passwordOk = TRUE;
				$this->_updatePasswordHash($table, $row['id'], $password);
				$row['password'] = $this->hash($password);
			}
		}

		if (!$passwordOk) {
			return FALSE;
		}

		return $row;
	}

	// -----------------------------------------------------------------------

	/**
	 * Hashe un mot de passe en clair en bcrypt.
	 * Centralisé ici pour que tout le code applicatif (element_password,
	 * seeds, scripts d'administration…) appelle le même point d'entrée.
	 *
	 * @param  string $plainPassword
	 * @return string  Hash bcrypt (60 caractères)
	 */
	public function hash($plainPassword)
	{
		return password_hash((string) $plainPassword, PASSWORD_BCRYPT);
	}

	// -----------------------------------------------------------------------

	/**
	 * Indique si un hash donné nécessite une réécriture (algo obsolète ou
	 * coût bcrypt augmenté).
	 *
	 * @param  string $storedHash
	 * @return bool
	 */
	public function needsRehash($storedHash)
	{
		if (!$this->_isBcryptHash($storedHash)) {
			return TRUE;
		}
		return password_needs_rehash($storedHash, PASSWORD_BCRYPT);
	}

	// -----------------------------------------------------------------------

	/**
	 * Retourne les messages de debug accumulés (requêtes SQL…).
	 *
	 * @return array
	 */
	public function getDebug()
	{
		return $this->_debug_array;
	}

	// -----------------------------------------------------------------------
	// Méthodes privées
	// -----------------------------------------------------------------------

	/**
	 * Un hash bcrypt commence toujours par $2y$ (PHP standard) ou $2a$
	 * (ancien préfixe). Tout le reste est considéré comme legacy.
	 *
	 * @param  string $hash
	 * @return bool
	 */
	private function _isBcryptHash($hash)
	{
		return is_string($hash)
			&& (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0);
	}

	/**
	 * Écrit le nouveau hash bcrypt dans la table.
	 *
	 * @param  string $table
	 * @param  int    $userId
	 * @param  string $plainPassword
	 * @return void
	 */
	private function _updatePasswordHash($table, $userId, $plainPassword)
	{
		$newHash = $this->hash($plainPassword);
		$this->CI->db->where('id', $userId)
			->update($table, ['password' => $newHash]);
		$this->_debug_array[] = $this->CI->db->last_query();
	}

	// -----------------------------------------------------------------------

	public function _set($field, $value) { $this->$field = $value; }
	public function _get($field)         { return $this->$field;   }

	// -----------------------------------------------------------------------

	public function __destruct()
	{
		if ($this->_debug) {
			foreach ($this->_debug_array as $msg) {
				log_message('debug', 'PasswordAuthenticator: ' . $msg);
			}
		}
	}
}

/* End of file PasswordAuthenticator.php */
/* Location: ./application/libraries/PasswordAuthenticator.php */
