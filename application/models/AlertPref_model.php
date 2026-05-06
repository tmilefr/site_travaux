<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');

/**
 * AlertPref_model
 *
 * Gère les préférences d'alerte e-mail "nouvelle session disponible"
 * pour chaque famille. Une famille peut s'abonner à un ou plusieurs
 * types de travaux (`options.cle` où filter='type').
 *
 * Pattern aligné sur Capacity_model : table de jointure simple, pilotée
 * via le rendu `checkboxdb` du formulaire Familys (Home/myaccount).
 *
 * Méthodes attendues par l'élément checkboxdb :
 *   - DeleteLink($foreignkey, $id_fam) : appelée avant ré-écriture
 *   - SetLink($foreignkey, $id_fam)    : appelée pour le mode 'add'
 *
 * Méthode métier :
 *   - GetSubscribers($type) : familles abonnées à un type donné
 *     (utilisée par Cron::send_new_session_alerts).
 */
class AlertPref_model extends Core_model
{
	const TABLE_NAME = 'famille_alerts';

	function __construct()
	{
		parent::__construct();
		$this->_set('table',     self::TABLE_NAME);
		$this->_set('key',       'id');
		$this->_set('order',     'id_fam');
		$this->_set('direction', 'asc');
		$this->_set('json',      'AlertPref.json');
	}

	// -----------------------------------------------------------------------
	// Hooks attendus par element_checkboxdb
	// -----------------------------------------------------------------------

	/**
	 * Supprime toutes les préférences d'une famille avant ré-écriture.
	 * Appelé par element_checkboxdb::PrepareForDBA.
	 *
	 * @param string $foreignkey  nom de la colonne de jointure (id_fam)
	 * @param int    $id          valeur (id de la famille)
	 * @return void
	 */
	public function DeleteLink($foreignkey, $id = null)
	{
		if (!$id) return;
		$this->db->where($foreignkey, (int) $id)->delete(self::TABLE_NAME);
		$this->_debug_array[] = $this->db->last_query();
	}

	/**
	 * Cas mode 'add' : rien de particulier à faire ici (les insertions sont
	 * faites par PrepareForDBA via post()). Méthode présente pour rester
	 * compatible avec le hook AfterExec de element_checkboxdb.
	 *
	 * @param string $foreignkey
	 * @param int    $id
	 * @return void
	 */
	public function SetLink($foreignkey, $id = null)
	{
		// no-op : les lignes sont déjà insérées via post() dans PrepareForDBA.
	}

	// -----------------------------------------------------------------------
	// Lecture métier
	// -----------------------------------------------------------------------

	/**
	 * Retourne les familles abonnées à un type de travaux donné.
	 * Filtre les familles sans e-mail (rien à envoyer) et celles dont
	 * l'école ne correspond pas (B = les deux écoles, OK partout).
	 *
	 * @param string      $id_type  ex. 'MEN', 'TRA'
	 * @param string|null $ecole    ecole de la session ('M', 'L' ou 'B')
	 * @return array              liste de stdClass {id, e_mail, nom, ...}
	 */
	public function GetSubscribers($id_type, $ecole = null)
	{
		$this->db->select('famille.id, famille.nom, famille.e_mail, famille.ecole')
			->from(self::TABLE_NAME.' AS fa')
			->join('famille', 'famille.id = fa.id_fam', 'inner')
			->where('fa.id_type', $id_type)
			->where('famille.e_mail !=', '')
			->where('famille.e_mail IS NOT NULL', null, false);

		// Filtrage écoles : la famille reçoit l'alerte si la session est
		// pour les deux écoles (B), ou si la session cible spécifiquement
		// son école, ou si la famille est sur les deux écoles.
		if ($ecole && in_array($ecole, ['M', 'L'])) {
			$this->db->group_start()
				->where('famille.ecole', $ecole)
				->or_where('famille.ecole', 'B')
				->group_end();
		}

		$query = $this->db->get();
		$this->_debug_array[] = $this->db->last_query();
		return ($query->num_rows()) ? $query->result() : [];
	}

	/**
	 * Retourne les types auxquels une famille est abonnée (debug / API).
	 *
	 * @param int $id_fam
	 * @return array  liste des id_type (cle des options)
	 */
	public function GetSubscriptions($id_fam)
	{
		$query = $this->db->select('id_type')
			->from(self::TABLE_NAME)
			->where('id_fam', (int) $id_fam)
			->get();
		$this->_debug_array[] = $this->db->last_query();
		$out = [];
		foreach ($query->result() as $row) {
			$out[] = $row->id_type;
		}
		return $out;
	}
}
