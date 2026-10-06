<?php

namespace App\Models;
class Admwork_model extends Core_model{
	
	protected $table = 'travaux';
	protected $primaryKey = 'id';
	protected $order = 'name';
	protected $direction = 'desc';
	protected $json = 'Travaux.json';




	function DraftPublication($civil_year){
		$this->tb()->set('statut', 1)->where('statut', 0)->where('civil_year', $civil_year)->update();
	}

/**
	 * Liste des travaux filtrés par année et école(s).
	 *
	 * @param string  $civil_year
	 * @param array   $schools         codes école acceptés (B/M/L)
	 * @param array   $exclude_types   types à exclure ; par défaut on retire 'can'
	 *                                 (les sessions cantine ont leur propre vue
	 *                                 Cantine_controller/register).
	 * @return array|false
	 */
	function GetFiltered($civil_year, $schools, $exclude_types = []){ //'can'
		$b = $this->tb()->select('*')
			->where("civil_year IN ('".$civil_year."','2025-2026')")
			->where('statut', 1)
			->whereIn('accespar', $schools)
			->orderBy('date_travaux', 'DESC');
		if (!empty($exclude_types)){
			$b->whereNotIn('type', $exclude_types);
		}
		$query = $b->get();
		$this->log();
		if ($query->getNumRows() > 0)
		{
			return $query->getResult();
		}
		return false;
	}

	function GetMax($id_travaux){
		//nb_inscrits_max
		$data=$this->db->table($this->table)->select('nb_inscrits_max')->where('id',$id_travaux)->get();
		$this->log();
		return (($data->getNumRows()) ? $data->getResult()[0]:FALSE);				
	}

	function stats(){
		$datas = $this->db->table($this->table)->select("count(*) AS nb,type")->groupBy("type")->get()->getResult();
		$this->log();
		return $datas;
	}

	/**
	 * Retourne la famille référente d'une session.
	 *
	 * Chaîne de jointure :
	 *   travaux.referent_travaux (INT) = trombi.id
	 *   trombi.ref               (VARCHAR contenant un id) = groupes_member.id
	 *   groupes_member.id_fam    (VARCHAR contenant un id) = famille.id (INT)
	 *
	 * MySQL convertit implicitement VARCHAR ↔ INT pour les comparaisons
	 * d'égalité, donc pas besoin de CAST explicite.
	 *
	 * @param int $id_travaux
	 * @return stdClass|null  famille complète (id, nom, e_mail, ...)
	 */
	public function GetReferentFamily($id_travaux)
	{
		$row = $this->db->table('travaux')->select('famille.*, groupes_member.name AS gm_name, groupes_member.surname AS gm_surname, groupes_member.email AS gm_email')->join('trombi',         'trombi.id = travaux.referent_travaux', 'inner')->join('groupes_member', 'groupes_member.id = trombi.ref',       'inner')->join('famille',        'famille.id = groupes_member.id_fam',   'inner')->where('travaux.id', (int) $id_travaux)->get()->getRow();
		$this->log();

		return $row ?: null;
	}

	/**
	 * Retourne les sessions où la famille connectée est référent (menu).
	 *
	 * @param int $id_fam
	 * @return array
	 */
	public function GetWorksAsReferent($id_fam)
	{
		$data = $this->db->table('travaux')->select('travaux.*')->join('trombi',         'trombi.id = travaux.referent_travaux', 'inner')->join('groupes_member', 'groupes_member.id = trombi.ref',       'inner')->where('groupes_member.id_fam', (int) $id_fam)->where('travaux.archived !=', 1)->orderBy('travaux.date_travaux', 'DESC')->get();
		$this->log();

		return ($data->getNumRows()) ? $data->getResult() : [];
	}

	/**
	 * Archive automatiquement les travaux dont la date est passée depuis
	 * $grace_days jours. On conserve un délai de grâce pour permettre la
	 * validation des unités a posteriori par le référent.
	 *
	 * - Ne touche pas aux travaux URG (pas de date significative).
	 * - Ne ré-archive pas les travaux déjà archivés (idempotent).
	 *
	 * @param int $grace_days  jours après la date où l'on archive (défaut 30)
	 * @return int  nombre de lignes archivées
	 */
	function ArchiveOldWorks($grace_days = 30){
		$cutoff = date('Y-m-d', strtotime('-'.(int)$grace_days.' days'));
		$this->db->table($this->table)->set('archived', 1)->set('updated', date('Y-m-d H:i:s'))->where('archived !=', 1)->where('type !=', 'URG')->where('date_travaux <', $cutoff)->update();
		$this->log();
		return $this->db->affectedRows();
	}

	/**
	 * Sessions dont le mail d'information au référent doit partir.
	 * Logique : le mail part 7 jours AVANT la session, et uniquement pour les
	 * types "ménage" (MEN) et "travaux" (TRA).
	 *
	 * @param int $days_before  nb jours avant la session où envoyer le mail (défaut 7)
	 * @return array
	 */
	public function GetWorksNeedingRefMail($days_before = 7)
	{
		// Fenêtre : sessions dont la date est entre aujourd'hui et aujourd'hui+N jours
		$target_date = date('Y-m-d', strtotime('+' . (int) $days_before . ' days'));
		$today       = date('Y-m-d');

		$data = $this->db->table('travaux')->select('travaux.*')->where('travaux.archived !=', 1)->where('travaux.ref_mail_sent_at IS NULL', null, false)->where('travaux.date_travaux >=', $today)->where('travaux.date_travaux <=', $target_date)->whereIn('travaux.type', ['MEN', 'TRA'])->where('travaux.referent_travaux !=', 0)->where('travaux.referent_travaux IS NOT NULL', null, false)->orderBy('travaux.date_travaux', 'ASC')->get();
		$this->log();

		return ($data->getNumRows()) ? $data->getResult() : [];
	}

	/**
	 * Marque la session comme "mail au référent envoyé".
	 *
	 * @param int $id_travaux
	 * @return void
	 */
	public function MarkRefMailSent($id_travaux)
	{
		$this->db->table('travaux')->where('id', (int) $id_travaux)->update(['ref_mail_sent_at' => date('Y-m-d H:i:s')]);
		$this->log();
	}

	/**
	 * Marque la session comme "alerte e-mail famille envoyée".
	 * Utilisé par Cron::send_new_session_alerts pour garantir l'idempotence.
	 *
	 * @param int $id_travaux
	 * @return void
	 */
	public function MarkAlertSent($id_travaux)
	{
		$this->db->table('travaux')->where('id', (int) $id_travaux)->update(['alert_sent_at' => date('Y-m-d H:i:s')]);
		$this->log();
	}


	/**
     * Récupère une session par son id, avec un filtre optionnel sur le type.
     * Utilisé par la librairie Inscriptions pour valider l'existence et
     * la conformité de la session ciblée.
     *
     * @param  int         $id_work
     * @param  string|null $expected_type  si non null ('can'…), la session
     *                                     doit être de ce type
     * @return stdClass|null
     */
    public function GetWorkById($id_work, $expected_type = null){
        $b = $this->tb()->where($this->primaryKey, (int) $id_work);
        if ($expected_type !== null){
            $b->where('type', $expected_type);
        }
        $row = $b->get()->getRow();
        $this->log();
        return $row ?: null;
    }
}
?>