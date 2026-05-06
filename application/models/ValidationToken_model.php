<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__) . '/Core_model.php');

/**
 * ValidationToken_model
 *
 * Gère les jetons à usage unique envoyés par email au référent
 * pour qu'il puisse valider les présences SANS se connecter.
 *
 * Workflow :
 *   1. Cron détecte une session passée + pas de mail envoyé
 *   2. create() génère un token aléatoire de 64 chars, expire à +30j
 *   3. Un email est poussé dans Sendmail_model avec le lien contenant le token
 *   4. Quand le référent clique, Admwork_controller::validate_by_token() :
 *        - vérifie le token via findValid()
 *        - affiche/traite la vue
 *        - à la soumission, marque le token comme used_at = NOW()
 *   5. Le référent peut revenir tant que le token n'est pas expiré
 *      (on ne "consomme" le token qu'à la soumission finale)
 */
class ValidationToken_model extends Core_model
{
	const TABLE_NAME     = 'validation_tokens';
	const EXPIRY_DAYS    = 30;
	const TOKEN_LENGTH   = 32; // 32 bytes -> 64 hex chars

	function __construct()
	{
		parent::__construct();
		$this->_set('table',     self::TABLE_NAME);
		$this->_set('key',       'id');
		$this->_set('order',     'created');
		$this->_set('direction', 'desc');
		$this->_set('json'	, 'Unites.json');
	}

	/**
	 * Génère et insère un nouveau token pour (session, référent).
	 * Invalide les tokens précédents éventuels pour ce couple.
	 *
	 * @param int $id_travaux
	 * @param int $id_fam_ref
	 * @return string Le token généré
	 */
	public function create($id_travaux, $id_fam_ref)
	{
		$token = bin2hex(random_bytes(self::TOKEN_LENGTH));

		$now     = date('Y-m-d H:i:s');
		$expires = date('Y-m-d H:i:s', strtotime('+' . self::EXPIRY_DAYS . ' days'));

		$this->db->insert(self::TABLE_NAME, [
			'id_travaux' => (int) $id_travaux,
			'id_fam_ref' => (int) $id_fam_ref,
			'token'      => $token,
			'expires_at' => $expires,
			'created'    => $now,
		]);
		$this->_debug_array[] = $this->db->last_query();

		return $token;
	}

	/**
	 * Retrouve un token valide (non expiré, non utilisé).
	 * Utilisation garde-fou avant d'afficher la vue ou de traiter le POST.
	 *
	 * @param string $token
	 * @return stdClass|null  Objet token enrichi avec ->travaux, ou null
	 */
	public function findValid($token)
	{
		if (!$token || !preg_match('/^[a-f0-9]{' . (self::TOKEN_LENGTH * 2) . '}$/', $token)) {
			return null;
		}

		$row = $this->db->select('*')
			->from(self::TABLE_NAME)
			->where('token', $token)
			->where('expires_at >=', date('Y-m-d H:i:s'))
			->get()
			->row();
		$this->_debug_array[] = $this->db->last_query();

		return $row ?: null;
	}

	/**
	 * Marque le token comme utilisé (à la soumission du formulaire).
	 *
	 * @param int $id
	 * @return void
	 */
	public function markUsed($id)
	{
		$this->db->where('id', (int) $id)
			->update(self::TABLE_NAME, ['used_at' => date('Y-m-d H:i:s')]);
		$this->_debug_array[] = $this->db->last_query();
	}
}
