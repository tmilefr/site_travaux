<?php

namespace App\Models;

use CodeIgniter\Model;
use Throwable;

/**
 * Requêtes SQL libres (outils d'administration de l'API).
 */
class GenericSql_model extends Model
{
	public function distinct($table, $id, $value)
	{
		return $this->db->table($table)->distinct()->select("$id,$value")->get()->getResult();
	}

	/**
	 * Exécute une requête SQL ; retourne le résultat, false si vide ou le message d'erreur.
	 */
	public function exec($sql)
	{
		try {
			$datas = $this->db->query($sql);

			return $datas ?: false;
		} catch (Throwable $e) {
			return 'Exception reçue : ' . $e->getMessage() . "\n";
		}
	}

	public function get($table, $order = null, $direction = null)
	{
		return $this->db->table($table)->select('*')->orderBy((string) $order, (string) $direction)->get()->getResult();
	}
}
