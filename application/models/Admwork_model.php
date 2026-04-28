<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once(dirname(__FILE__).'/Core_model.php');
class Admwork_model extends Core_model{
	
	function __construct(){
		parent::__construct();
		
		$this->_set('table'	, 'travaux');
		$this->_set('key'	, 'id');
		$this->_set('order'	, 'name');
		$this->_set('direction'	, 'desc');
		$this->_set('json'	, 'Travaux.json');
	}


	function DraftPublication($civil_year){
		$this->db->set('statut', 1);
		$this->db->where('statut', 0);
		$this->db->where("civil_year", $civil_year );
		$this->db->update($this->table);
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
	function GetFiltered($civil_year, $schools, $exclude_types = ['can']){
		$this->db->order_by('date_travaux','DESC');
		$this->db->select('*')
			->from($this->table)
			->where("civil_year IN ('".$civil_year."','2025-2026')")
			->where("statut", 1 )
			->where_in('accespar', $schools);
		if (!empty($exclude_types)){
			$this->db->where_not_in('type', $exclude_types);
		}
		$query = $this->db->get();
		$this->_debug_array[] = $this->db->last_query();
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		return false;
	}

	function GetMax($id_travaux){
		//nb_inscrits_max
		$data=$this->db->select('nb_inscrits_max')
				->from($this->table)
				->where('id',$id_travaux)
				->get();
		$this->_debug_array[] = $this->db->last_query();
		return (($data->num_rows()) ? $data->result()[0]:FALSE);				
	}

	function stats(){
		$datas = $this->db->select("count(*) AS nb,type")
		->group_by("type")
		->get($this->table)
		->result();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}

	/**
	 * Chaîne de liaison pour retrouver la famille du référent :
	 *   travaux.referent_travaux (INT)      = trombi.id
	 *   trombi.ref               (VARCHAR)  = groupes_member.id
	 *   groupes_member.id_fam    (VARCHAR)  = famille.id (INT)
	 *
	 * MySQL gère la conversion implicite VARCHAR↔INT pour les égalités, donc les
	 * jointures s'écrivent directement sans CAST. Si un jour une valeur non
	 * numérique est stockée par erreur dans id_fam, la jointure ne remontera
	 * simplement aucune ligne (comportement souhaitable).
	 *
	 * @param int $id_travaux
	 * @return stdClass|null  famille complète (id, nom, e_mail, ...)
	 */
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
		$row = $this->db->select('famille.*, groupes_member.name AS gm_name, groupes_member.surname AS gm_surname, groupes_member.email AS gm_email')
			->from('travaux')
			->join('trombi',         'trombi.id = travaux.referent_travaux', 'inner')
			->join('groupes_member', 'groupes_member.id = trombi.ref',       'inner')
			->join('famille',        'famille.id = groupes_member.id_fam',   'inner')
			->where('travaux.id', (int) $id_travaux)
			->get()
			->row();
		$this->_debug_array[] = $this->db->last_query();
		echo '<p>'.$this->db->last_query().'</p>';
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
		$data = $this->db->select('travaux.*')
			->from('travaux')
			->join('trombi',         'trombi.id = travaux.referent_travaux', 'inner')
			->join('groupes_member', 'groupes_member.id = trombi.ref',       'inner')
			->where('groupes_member.id_fam', (int) $id_fam)
			->where('travaux.archived !=', 1)
			->order_by('travaux.date_travaux', 'DESC')
			->get();
		$this->_debug_array[] = $this->db->last_query();

		return ($data->num_rows()) ? $data->result() : [];
	}

	/**
	 * Sessions passées dont le mail de validation au référent n'a pas encore
	 * été envoyé. Appelé par le cron.
	 *
	 * @param int $days_since  nb jours mini écoulés depuis la session
	 * @return array
	 */
	public function GetWorksNeedingRefMail($days_since = 0)
	{
		$cutoff = date('Y-m-d', strtotime('-' . (int) $days_since . ' days'));

		$data = $this->db->select('travaux.*')
			->from('travaux')
			->where('travaux.archived !=', 1)
			->where('travaux.ref_mail_sent_at IS NULL', null, false)
			->where('travaux.date_travaux <=', $cutoff)
			->where('travaux.referent_travaux !=', 0)
			->where('travaux.referent_travaux IS NOT NULL', null, false)
			->order_by('travaux.date_travaux', 'ASC')
			->get();
		$this->_debug_array[] = $this->db->last_query();

		return ($data->num_rows()) ? $data->result() : [];
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
		$this->db->set('archived', 1)
			->set('updated', date('Y-m-d H:i:s'))
			->where('archived !=', 1)
			->where('type !=', 'URG')
			->where('date_travaux <', $cutoff)
			->update($this->table);
		$this->_debug_array[] = $this->db->last_query();
		return $this->db->affected_rows();
	}

	/**
	 * Marque la session comme "mail au référent envoyé".
	 *
	 * @param int $id_travaux
	 * @return void
	 */
	public function MarkRefMailSent($id_travaux)
	{
		$this->db->where('id', (int) $id_travaux)
			->update('travaux', ['ref_mail_sent_at' => date('Y-m-d H:i:s')]);
		$this->_debug_array[] = $this->db->last_query();
	}

}
?>