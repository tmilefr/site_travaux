<?php
defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Core_model
 * Modèle de base générique pour tous les modèles de l'application.
 *
 * CORRECTIONS v2 :
 *  - _set_list_fields() : bug de doublon du dernier caractère corrigé (substr → rtrim)
 *  - get_all()          : ajout d'une limite de sécurité (défaut 1000 lignes)
 *  - get_all()          : la recherche globale utilise _setField() comme get()
 *                         pour être cohérente avec le mode 'join'
 */
class Core_model extends CI_Model {

	protected $table;
	protected $key;
	protected $key_value;
	protected $order        = [];
	protected $direction;
	protected $autorized_fields        = [];
	protected $autorized_fields_search = [];
	protected $required     = [];
	protected $datas        = [];
	protected $filter       = [];
	protected $group_by     = [];
	protected $per_page     = 20;
	protected $_debug       = FALSE;
	protected $page         = 1;
	protected $nb           = null;
	protected $_debug_array = [];
	protected $like         = [];
	protected $global_search = null;
	protected $defs         = [];
	protected $json         = null;
	protected $json_path    = APPPATH.'models/json/';
	protected $_mode        = 'classic'; // classic | join

	// -----------------------------------------------------------------------

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		if (!$this->page) {
			$this->page = 1;
		}
	}


	/**
	 * Log une erreur DB et lève une exception si $throw = true.
	 * Utiliser dans tous les catch des méthodes critiques.
	 */
	protected function _handleDbError(string $context, Exception $e, bool $throw = true): void {
		$dbError = $this->db->error();
		$msg = sprintf(
			'[%s] Exception: %s | DB error: %s | Last query: %s',
			$context,
			$e->getMessage(),
			json_encode($dbError),
			$this->db->last_query()
		);
		log_message('error', $msg);

		if ($throw) {
			throw new RuntimeException($msg, (int)($dbError['code'] ?? 0), $e);
		}
	}

	// -----------------------------------------------------------------------
	// LECTURE
	// -----------------------------------------------------------------------

	/**
	 * Récupère tous les enregistrements correspondant aux filtres.
	 *
	 * CORRECTIONS :
	 *  1. Limite de sécurité : $limit (défaut 1000) pour éviter de charger
	 *     l'intégralité d'une table volumineuse en mémoire.
	 *     Passer 0 pour désactiver la limite (à utiliser avec précaution).
	 *  2. global_search : utilise _setField() pour la cohérence avec le mode 'join'.
	 *
	 * @param  int $limit  Nombre max de lignes (0 = illimité)
	 * @return array
	 */
	public function get_all($limit = 1000)
	{
		if (is_array($this->filter) && count($this->filter)) {
			foreach ($this->filter as $key => $value) {
				$this->db->where($key, $value);
			}
		}

		if ($this->global_search) {
			$this->db->group_start();
			foreach ($this->autorized_fields_search as $idx => $field) {
				$resolvedField = $this->_setField($field);
				if ($idx === 0) {
					$this->db->like($resolvedField, $this->global_search);
				} else {
					$this->db->or_like($resolvedField, $this->global_search);
				}
			}
			$this->db->group_end();
		}

		$q = $this->db->select(implode(',', $this->autorized_fields))
		              ->order_by($this->order, $this->direction);

		if ($limit > 0) {
			$q->limit($limit);
		}

		$datas = $q->get($this->table)->result();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}

	// -----------------------------------------------------------------------

	/**
	 * Récupère une liste paginée (utilisée par les vues liste).
	 *
	 * @return array
	 */
	public function get()
	{
		$this->_set_filter();
		$this->_set_search();

		if ($this->per_page) {
			if (!$this->page) {
				$this->page = 1;
			}
			$this->db->limit(intval($this->per_page), ($this->page - 1) * $this->per_page);
		}

		$datas = $this->db->select($this->_set_list_fields())
		                  ->order_by($this->order, $this->direction)
		                  ->get($this->table);
		$this->_debug_array[] = $this->db->last_query();
		return $datas->result();
	}

	// -----------------------------------------------------------------------

	/**
	 * Retourne le nombre total de lignes pour la pagination.
	 *
	 * @return int
	 */
	public function get_pagination()
	{
		if (!$this->nb) {
			$this->_set_filter();
			$this->_set_search();
			$this->nb = $this->db->select($this->table . '.' . $this->key)
			                     ->get($this->table)
			                     ->num_rows();
		}
		$this->_debug_array[] = 'get_pagination : ' . $this->nb;
		return $this->nb;
	}

	// -----------------------------------------------------------------------

	/**
	 * Récupère un enregistrement unique par clé primaire.
	 *
	 * @return object|false
	 */
	public function get_one()
	{
		try {
			$this->db->where($this->key, $this->key_value);
			$result = $this->db->select($this->_set_list_fields())
							   ->get($this->table)
							   ->row();
			$this->_debug_array[] = $this->db->last_query();
			return $result ?: null; // null explicite plutôt que false/empty
		} catch (Exception $e) {
			$this->_handleDbError('Core_model::get_one', $e);
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Récupère un enregistrement unique par un champ quelconque.
	 *
	 * @param  array $fields  ['champ' => 'valeur']
	 * @return object|false
	 */
	public function get_by($fields)
	{
		$query = $this->db->select('*')
		                  ->from($this->table)
		                  ->where($fields)
		                  ->limit(1)
		                  ->get();
		$this->_debug_array[] = $this->db->last_query();

		if ($query->num_rows()) {
			return $query->row();
		}
		return false;
	}

	// -----------------------------------------------------------------------

	public function get_distinct($field)
	{
		$this->db->distinct();
		$datas = $this->db->select($field)->get($this->table)->result();
		$this->_debug_array[] = $this->db->last_query();
		return $datas;
	}

	// -----------------------------------------------------------------------

	public function distinct($opt)
	{
		try {
			if (strpos($opt->value, '@')) {
				$fields = 'CONCAT_WS(" ",' . str_replace('@', ',', $opt->value) . ') AS ';
				$as     = ' ' . str_replace('@', '_', $opt->value);
			} else {
				$fields = $opt->value;
				$as     = $opt->value;
			}
			$this->db->distinct();
			if (isset($opt->filter_field) && isset($opt->filter_value)) {
				$datas = $this->db->select("$opt->table.$opt->id,$fields $as")
				                  ->where($opt->filter_field, $opt->filter_value)
				                  ->order_by("$as", 'asc')
				                  ->get($opt->table)->result();
			} else {
				$datas = $this->db->select("$opt->table.$opt->id,$fields $as")
				                  ->order_by("$as", 'asc')
				                  ->get($opt->table)->result();
			}
			$this->_debug_array[] = $this->db->last_query();
			return $datas;
		} catch (Exception $e) {
			log_message('error', 'Core_model::distinct() — ' . $e->getMessage());
			return [];
		}
	}

	// -----------------------------------------------------------------------

	public function query($sql)
	{
		try {
			$datas = $this->db->query($sql)->result();
			$this->_debug_array[] = $this->db->last_query();
			return $datas;
		} catch (Exception $e) {
			log_message('error', 'Core_model::query() — ' . $e->getMessage());
			return [];
		}
	}

	// -----------------------------------------------------------------------
	// ÉCRITURE
	// -----------------------------------------------------------------------

	/**
	 * Insère un enregistrement.
	 *
	 * @param  array $datas
	 * @return int   Insert ID
	 */
	public function post($datas)
	{
		$this->db->insert($this->table, $datas);
		$this->_debug_array[] = $this->db->last_query();
		return $this->db->insert_id();
	}

	// -----------------------------------------------------------------------

	/**
	 * Met à jour l'enregistrement courant ($this->key_value).
	 *
	 * @param  int|null $id  Optionnel — remplace $this->key_value
	 */
	public function put($id = null)
	{
		foreach ($this->datas as $field => $data) {
			if (!in_array($field, $this->autorized_fields)) {
				unset($this->datas[$field]);
			}
		}
		if ($id) {
			$this->key_value = $id;
		}
		$this->db->where($this->key, $this->key_value);
		$this->db->update($this->table, $this->datas);
		$this->_debug_array[] = $this->db->last_query();
	}

	// -----------------------------------------------------------------------

	public function delete()
	{
		$this->db->where_in($this->key, $this->key_value)
		         ->delete($this->table);
	}

	// -----------------------------------------------------------------------
	// MÉTHODES INTERNES
	// -----------------------------------------------------------------------

	/**
	 * Construit la liste des champs SELECT.
	 *
	 * CORRECTION : substr($string_field, -1) ajoutait le dernier caractère
	 * au lieu d'une virgule → remplacé par rtrim(..., ',') pour supprimer
	 * proprement la virgule finale.
	 *
	 * @return string
	 */
	function _set_list_fields()
	{
		if (empty($this->autorized_fields)) {
			return ($this->_mode === 'join') ? $this->table . '.*' : '*';
		}

		$parts = [];
		foreach ($this->autorized_fields as $field) {
			$parts[] = ($this->_mode === 'join') ? $this->table . '.' . $field : $field;
		}
		return implode(',', $parts);
	}

	// -----------------------------------------------------------------------

	function _set_filter()
	{
		if (is_array($this->filter) && count($this->filter)) {
			$this->db->group_start();
			foreach ($this->filter as $key => $value) {
				$this->db->where($key, $value);
			}
			$this->db->group_end();
		}
	}

	// -----------------------------------------------------------------------

	function _set_search()
	{
		if ($this->global_search) {
			$this->db->group_start();
			foreach ($this->autorized_fields_search as $key => $value) {
				if (!$key && is_array($this->filter) && count($this->filter)) {
					$this->db->like($this->_setField($value), $this->global_search);
				} else {
					$this->db->or_like($this->_setField($value), $this->global_search);
				}
			}
			$this->db->group_end();
		}
	}

	// -----------------------------------------------------------------------

	function _set_group_by()
	{
		if (is_array($this->group_by) && count($this->group_by)) {
			foreach ($this->group_by as $value) {
				$this->db->group_by($value);
			}
		}
	}

	// -----------------------------------------------------------------------

	function _set_order_by()
	{
		if (is_array($this->order) && count($this->order)) {
			foreach ($this->order as $key => $value) {
				$this->db->order_by($key, $value);
			}
		}
	}

	// -----------------------------------------------------------------------

	function _setField($field)
	{
		$defs = $this->_get('defs');
		if (!isset($defs[$field])) {
			return $field;
		}
		$def = $defs[$field];

		if (isset($def->table[$field])) {
			$this->_mode = 'join';
			$this->db->join(
				$def->table[$field],
				$this->table . '.' . $field . '=' . $def->table[$field] . '.' . $def->foreignKey[$field],
				'left'
			);
			return $def->table[$field] . '.' . $def->foreignField[$field];
		} else {
			return ($this->_mode === 'join') ? $this->table . '.' . $field : $field;
		}
	}

	// -----------------------------------------------------------------------

	/**
	 * Initialise les définitions de champs depuis le fichier JSON associé.
	 */
	public function _init_def()
	{
		if (!$this->json) return;

		$path = $this->json_path . $this->json;
		if (!file_exists($path)) {
			log_message('error', 'Core_model::_init_def() — JSON introuvable : ' . $path);
			return;
		}

		$json = json_decode(file_get_contents($path), false);
		if (!$json) {
			log_message('error', 'Core_model::_init_def() — JSON invalide : ' . $path);
			return;
		}

		foreach ($json as $field => $def) {
			// Chargement de la classe d'élément
			$elementClass = 'element_' . $def->type;
			$elementFile  = APPPATH . 'libraries/elements/' . $elementClass . '.php';

			if (file_exists($elementFile)) {
				require_once($elementFile);
				$this->defs[$field] = new $elementClass();
			} else {
				require_once(APPPATH . 'libraries/elements/element.php');
				$this->defs[$field] = new element();
			}

			$this->defs[$field]->_set('name', $field);

			foreach ($def as $key => $value) {
				$this->defs[$field]->_set($key, $value);
			}

			if (isset($def->list) && $def->list === true) {
				$this->autorized_fields[] = $field;
			}
			if (isset($def->search) && $def->search === true) {
				$this->autorized_fields_search[] = $field;
			}
			if (isset($def->rules) && $def->rules) {
				$this->required[$field] = $def->rules;
			}
		}
	}

	// -----------------------------------------------------------------------

	public function _set($field, $value) { $this->$field = $value; }
	public function _get($field)         { return $this->$field;   }

	// -----------------------------------------------------------------------

	public function __destruct()
	{
		if ($this->_debug) {
			echo debug($this->_debug_array, __FILE__);
			foreach ($this->_debug_array as $msg) {
				log_message('debug', debug($msg, get_class($this)));
			}
		}
	}
}

/* End of file Core_model.php */
/* Location: ./application/models/Core_model.php */
