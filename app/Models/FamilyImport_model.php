<?php

namespace App\Models;

/**
 * FamilyImport_model
 *
 * Log des imports CSV ABCM. Une ligne par import effectivement appliqué
 * (étape 2 du flow). Voir Familys_controller::import_apply().
 */
class FamilyImport_model extends Core_model
{
	function __construct()
	{
		parent::__construct();

		$this->_set('table',     'family_import');
		$this->_set('key',       'id');
		$this->_set('order',     'created');
		$this->_set('direction', 'desc');
		$this->_set('json',      'FamilyImport.json');
	}

	/**
	 * Liste des derniers imports, plus récent en premier.
	 *
	 * @param  int $limit
	 * @return array
	 */
	function GetLastImports($limit = 20)
	{
		$query = $this->db->select('*')
			->from($this->table)
			->order_by('created', 'DESC')
			->limit($limit)
			->get();
		$this->_debug_array[] = $this->db->last_query();

		return ($query->num_rows() > 0) ? $query->result() : [];
	}
}
