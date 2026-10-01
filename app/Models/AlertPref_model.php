<?php

namespace App\Models;

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

	protected $table = self::TABLE_NAME;
	protected $primaryKey = 'id';
	protected $order = 'id_fam';
	protected $direction = 'asc';
	protected $json = 'AlertPref.json';



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
		$this->db->table(self::TABLE_NAME)->where($foreignkey, (int) $id)->delete();
		$this->log();
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
		$b = $this->db->table(self::TABLE_NAME.' AS fa')->select('famille.id, famille.nom, famille.e_mail, famille.ecole')->join('famille', 'famille.id = fa.id_fam', 'inner')->where('fa.id_type', $id_type)->where('famille.e_mail !=', '')->where('famille.e_mail IS NOT NULL', null, false);

		// Filtrage écoles : la famille reçoit l'alerte si la session est
		// pour les deux écoles (B), ou si la session cible spécifiquement
		// son école, ou si la famille est sur les deux écoles.
		if ($ecole && in_array($ecole, ['M', 'L'])) {
			$b->groupStart()
				->where('famille.ecole', $ecole)
				->orWhere('famille.ecole', 'B')
				->groupEnd();
		}

		$query = $b->get();
		$this->log();
		return ($query->getNumRows()) ? $query->getResult() : [];
	}

	/**
	 * Retourne les types auxquels une famille est abonnée (debug / API).
	 *
	 * @param int $id_fam
	 * @return array  liste des id_type (cle des options)
	 */
	public function GetSubscriptions($id_fam)
	{
		$query = $this->db->table(self::TABLE_NAME)->select('id_type')->where('id_fam', (int) $id_fam)->get();
		$this->log();
		$out = [];
		foreach ($query->getResult() as $row) {
			$out[] = $row->id_type;
		}
		return $out;
	}
}
