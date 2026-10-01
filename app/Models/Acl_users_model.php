<?php

namespace App\Models;

/**
 * Acl_users_model
 *
 * CORRECTIONS v3 :
 *  - verifyLogin() : la vérification du mot de passe contrôle réellement l'accès
 *    (bug critique originel corrigé : $usercheck->autorize = true était en
 *     dehors du if (hash_equals()) → tout login existant passait).
 *  - verifyLogin() : délégation à PasswordAuthenticator pour le hash bcrypt
 *    et la migration transparente des anciens hashes crypt().
 *  - verifyLogin() : signature de retour en stdClass (autorize, name, id,
 *    role_id, type) — consommée par Auth::Login et Acl::CheckLogin.
 *  - hashPassword()  : méthode déléguée au service partagé.
 *  - echo de debug en production supprimé.
 */
class Acl_users_model extends Core_model
{
	/** @var string */
	const TABLE_NAME = 'acl_users';

	protected $table = self::TABLE_NAME;
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Acl_users.json';

	function __construct()
	{
		parent::__construct();
		$this->_init_def();
	}

	// -----------------------------------------------------------------------

	/**
	 * Retourne le role_id d'un utilisateur.
	 *
	 * @param  int $userId
	 * @return int
	 */
	public function getUserRoleId($userId = 0)
	{
		$query = $this->db->table(self::TABLE_NAME . ' u')->select('role_id')->where('id', $userId)->get();
		$this->log();

		if ($query->getNumRows() > 0) {
			$row = $query->getRowArray();
			return (int) $row['role_id'];
		}
		return 0;
	}

	// -----------------------------------------------------------------------

	/**
	 * Vérifie les identifiants d'un compte administrateur.
	 *
	 * @param  string $login
	 * @param  string $password
	 * @return stdClass  Objet avec ->autorize (bool), ->name, ->id, ->role_id, ->type, ->login
	 */
	public function verifyLogin($login, $password)
	{
		// Objet de retour par défaut : accès refusé
		$usercheck            = new \stdClass();
		$usercheck->autorize  = FALSE;
		$usercheck->name      = 'nobody';
		$usercheck->login     = '';
		$usercheck->type      = 'none';
		$usercheck->id        = 0;
		$usercheck->role_id   = 0;

		// Délégation à la couche de sécurité partagée
		$passauth = service('passwordAuthenticator');
		$row = $passauth->verify(self::TABLE_NAME, 'login', $login, $password, FALSE);

		// Remontée des logs SQL pour les consommateurs qui lisent _debug_array
		foreach ($passauth->getDebug() as $msg) {
			$this->_debug_array[] = $msg;
		}

		if ($row === FALSE) {
			return $usercheck;
		}

		// Connexion acceptée
		$usercheck->autorize = TRUE;
		$usercheck->name     = isset($row['name'])  ? $row['name']  : $row['login'];
		$usercheck->login    = $row['login'];
		$usercheck->id       = (int) $row['id'];
		$usercheck->role_id  = (int) $row['role_id'];
		$usercheck->type     = 'sys';

		return $usercheck;
	}

	// -----------------------------------------------------------------------

	/**
	 * Hashe un mot de passe en bcrypt. Wrapper autour du service partagé.
	 *
	 * @param  string $plainPassword
	 * @return string
	 */
	public function hashPassword($plainPassword)
	{
		$passauth = service('passwordAuthenticator');
		return $passauth->hash($plainPassword);
	}
}

/* End of file Acl_users_model.php */
/* Location: ./application/models/Acl_users_model.php */
