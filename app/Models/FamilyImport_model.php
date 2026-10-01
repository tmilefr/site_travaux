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
	protected $table = 'family_import';
	protected $primaryKey = 'id';
	protected $order = 'created';
	protected $direction = 'desc';
	protected $json = 'FamilyImport.json';



	/**
	 * Liste des derniers imports, plus récent en premier.
	 *
	 * @param  int $limit
	 * @return array
	 */
	function GetLastImports($limit = 20)
	{
		$query = $this->db->table($this->table)->select('*')->orderBy('created', 'DESC')->limit($limit)->get();
		$this->log();

		return ($query->getNumRows() > 0) ? $query->getResult() : [];
	}
}
