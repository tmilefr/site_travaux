<?php

namespace App\Models;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

/**
 * Modèle de base : CRUD générique piloté par un schéma JSON (app/Models/json/*.json).
 *
 * Chaque modèle métier déclare ses propriétés ($table, $primaryKey, $order, $direction, $json)
 * puis le contrôleur charge les définitions de champs avec _init_def().
 */
#[\AllowDynamicProperties]
class Core_model extends Model
{
    protected $table = '';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    /** Valeur de la clé primaire de l'enregistrement courant */
    protected $key_value;
    /** Tri : nom de champ, ou tableau de champs (pile de tris) */
    protected $order = [];
    /** Sens du tri (ou tableau aligné sur $order) */
    protected $direction;
    protected $autorized_fields = [];
    protected $autorized_fields_search = [];
    protected $required = [];
    /** Données à écrire par put() */
    protected $datas = [];
    protected $filter = [];
    protected $group_by = [];
    protected $per_page = 20;
    protected $_debug = false;
    protected $page = 1;
    protected $nb = null;
    protected $_debug_array = [];
    protected $like = [];
    protected $global_search = null;
    protected $defs = [];
    /** Fichier de schéma JSON du modèle */
    protected $json = null;
    protected $json_path = APPPATH . 'Models/json/';
    protected $_mode = 'classic'; // classic ou join
    protected $_model_name = '';

    public function __construct()
    {
        parent::__construct();
        if (! $this->page) {
            $this->page = 1;
        }
    }

    // ------------------------------------------------------------------
    // Utilitaires
    // ------------------------------------------------------------------

    /** Nouveau builder sur la table du modèle (état propre à chaque requête). */
    protected function tb(?string $table = null): BaseBuilder
    {
        return $this->db->table($table ?? $this->table);
    }

    /** Mémorise la dernière requête (affichée en mode debug). */
    protected function log(): void
    {
        $this->_debug_array[] = (string) $this->db->getLastQuery();
    }

    /**
     * Configure la protection des champs et les horodatages natifs d'après les colonnes réelles de la table :
     * `created` est posé à l'insertion et `updated` à chaque écriture, par Model (plus par les formulaires).
     */
    protected function ensureFields(): void
    {
        if ($this->table === '' || $this->allowedFields) {
            return;
        }
        $columns             = $this->db->getFieldNames($this->table);
        $this->allowedFields = $columns;
        $this->dateFormat    = 'datetime';
        $this->createdField  = in_array('created', $columns, true) ? 'created' : '';
        $this->updatedField  = in_array('updated', $columns, true) ? 'updated' : '';
        $this->useTimestamps = $this->createdField !== '' || $this->updatedField !== '';
    }

    public function insert($row = null, bool $returnID = true)
    {
        $this->ensureFields();

        return parent::insert($row, $returnID);
    }

    public function update($id = null, $row = null): bool
    {
        $this->ensureFields();

        return parent::update($id, $row);
    }

    // ------------------------------------------------------------------
    // Requêtes génériques
    // ------------------------------------------------------------------

    /**
     * Valeurs distinctes pour alimenter un select.
     *
     * @param object $opt ->table ->id ->value [->filter_field ->filter_value]
     */
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
            $b = $this->tb($opt->table)->distinct()->select("$opt->table.$opt->id,$fields $as");
            if (isset($opt->filter_field, $opt->filter_value)) {
                $b->where($opt->filter_field, $opt->filter_value);
            }
            $datas = $b->orderBy($as, 'asc')->get()->getResult();
            $this->log();

            return $datas;
        } catch (\Throwable $e) {
            log_message('error', 'Core_model::distinct : ' . $e->getMessage());

            return [];
        }
    }

    /** Supprime les lignes enfants rattachées à $id par la clé étrangère. */
    public function DeleteLink($foreign_key, $id = null)
    {
        if ($id) {
            $this->tb()->whereIn($foreign_key, (array) $id)->delete();
            $this->log();
        }
    }

    /** Rattache les lignes provisoires (clé étrangère 99999) à $id. */
    public function SetLink($foreign_key, $id = null)
    {
        if ($id) {
            $this->tb()->set($foreign_key, $id)->where($foreign_key, 99999)->update();
            $this->log();
        }
    }

    /** Requête SQL libre. */
    public function query($sql, $binds = null, bool $setEscapeFlags = true, $queryClass = '')
    {
        try {
            $datas = $this->db->query($sql)->getResult();
            $this->log();

            return $datas;
        } catch (\Throwable $e) {
            log_message('error', 'Core_model::query : ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Instancie les définitions de champs depuis le schéma JSON.
     */
    public function _init_def()
    {
        $this->defs                    = [];
        $this->autorized_fields        = [];
        $this->autorized_fields_search = [];
        $this->required                = [];

        $json = json_decode(file_get_contents($this->json_path . $this->json));
        foreach ($json as $field => $defs) {
            $this->autorized_fields[] = $field;
            if ($defs->search) {
                $this->autorized_fields_search[] = $field;
            }
            if ($defs->rules) {
                $this->required[] = $field;
            }

            // Élément de formulaire correspondant au type du champ
            $object_name = 'App\\Libraries\\Elements\\element_' . $defs->type;
            if (! class_exists($object_name)) {
                $object_name = 'App\\Libraries\\Elements\\element';
            }
            $obj = new $object_name();
            foreach ($defs as $key => $value) {
                $obj->_set($key, $value);
            }
            if ($obj->_get('param')) {
                $op_mg = $obj->SetParams();
                if (isset($op_mg->method) && method_exists($this, $op_mg->method)) {
                    $datas_select = [];
                    $datas        = $this->{$op_mg->method}($op_mg);
                    foreach ($datas as $data) {
                        $datas_select[$data->{$op_mg->key}] = $data->{$op_mg->data};
                    }
                    $obj->_set('values', $datas_select);
                }
            } elseif (method_exists($obj, 'SetValues')) {
                $obj->SetValues();
            }
            $obj->_set('_model_name', $this->_get('_model_name'));
            $obj->_set('name', $field);
            $this->defs[$field] = $obj;
        }
    }

    public function truncate()
    {
        $this->tb()->truncate();
    }

    /** Retourne la ligne correspondante ou false. */
    public function is_exist($field = 'id', $value = null, $fields = null)
    {
        $row = $this->where($fields ?: [$field => $value])->first();
        $this->log();

        return $row ?: false;
    }

    /** Toutes les lignes (filtre + recherche globale + tri simple), sans pagination. */
    public function get_all()
    {
        $b = $this->builder();
        foreach ((array) $this->filter as $key => $value) {
            $b->where($key, $value);
        }
        if ($this->global_search) {
            foreach ($this->autorized_fields_search as $value) {
                $b->orLike($value, $this->global_search);
            }
        }
        $b->select(implode(',', $this->autorized_fields))
            ->orderBy((string) $this->order, (string) $this->direction);
        $datas = $this->findAll();
        $this->log();

        return $datas;
    }

    public function get_distinct($field)
    {
        $datas = $this->tb()->distinct()->select($field)->get()->getResult();
        $this->log();

        return $datas;
    }

    /** La ligne dont la clé primaire vaut $key_value (objet ou null). */
    public function get_one()
    {
        $row = $this->find($this->key_value);
        $this->log();

        return $row;
    }

    /** Insère une ligne (tableau ou objet) et retourne son identifiant. */
    public function post($datas)
    {
        $id = $this->insert($datas);
        $this->log();

        return $id;
    }

    // ------------------------------------------------------------------
    // Filtres, recherche, tri (appliqués au builder $b)
    // ------------------------------------------------------------------

    protected function applyFilter(BaseBuilder $b): void
    {
        if (is_array($this->filter) && count($this->filter)) {
            $b->groupStart();
            foreach ($this->filter as $key => $value) {
                $b->where($key, $value);
            }
            $b->groupEnd();
        }
    }

    protected function applyOrder(BaseBuilder $b): void
    {
        // Pile de tris : $order = ['a','b'] aligné sur $direction = ['asc','desc']
        if (is_array($this->order) && count($this->order) > 0) {
            $dirs = is_array($this->direction) ? $this->direction : [];
            foreach ($this->order as $i => $field) {
                $b->orderBy($field, $dirs[$i] ?? 'asc');
            }
        } elseif (is_string($this->order) && $this->order !== '') {
            $b->orderBy($this->order, (string) $this->direction);
        }
    }

    /** Nom de colonne qualifié ; ajoute la jointure quand le champ référence une autre table. */
    protected function qualifiedField(BaseBuilder $b, string $field): string
    {
        $def = $this->defs[$field] ?? null;
        if ($def !== null && isset($def->table[$field])) {
            $this->_mode = 'join';
            $b->join($def->table[$field], $this->table . '.' . $field . '=' . $def->table[$field] . '.' . $def->foreignKey[$field], 'left');

            return $def->table[$field] . '.' . $def->foreignField[$field];
        }

        return $this->_mode === 'join' ? $this->table . '.' . $field : $field;
    }

    protected function applySearch(BaseBuilder $b): void
    {
        if ($this->global_search) {
            $b->groupStart();
            foreach ($this->autorized_fields_search as $key => $value) {
                $col = $this->qualifiedField($b, $value);
                if (! $key && is_array($this->filter) && count($this->filter)) {
                    $b->like($col, $this->global_search);
                } else {
                    $b->orLike($col, $this->global_search);
                }
            }
            $b->groupEnd();
        }
    }

    protected function listFields(): string
    {
        if (! $this->autorized_fields) {
            return '*';
        }
        $cols = [];
        foreach ($this->autorized_fields as $field) {
            $cols[] = $this->_mode === 'join' ? $this->table . '.' . $field : $field;
        }

        return implode(',', $cols);
    }

    /** Nombre total de lignes de la liste (renseigné par get() ; calculé à la demande sinon). */
    public function get_pagination()
    {
        if ($this->nb === null) {
            $b = $this->builder();
            $this->applyFilter($b);
            $this->applySearch($b);
            $this->nb = $b->countAllResults();
        }
        $this->_debug_array[] = 'get_pagination : ' . $this->nb;

        return $this->nb;
    }

    /**
     * Page courante de la liste (filtre, recherche, tri) par Model::paginate().
     * Le pager partagé de CI4 mémorise le total ; le numéro de page est le 4e segment de l'URL.
     */
    public function get()
    {
        $b = $this->builder();
        $this->applyFilter($b);
        $this->applySearch($b);
        $b->select($this->listFields());
        $this->applyOrder($b);
        if ($this->per_page) {
            $datas     = $this->paginate((int) $this->per_page, 'default', (int) $this->page, 4);
            $this->nb  = $this->pager->getTotal('default');
        } else {
            $datas = $this->findAll();
        }
        $this->log();

        return $datas;
    }

    /** Comme get() mais sans LIMIT (export CSV). */
    public function get_all_filtered()
    {
        $b = $this->builder();
        $this->applyFilter($b);
        $this->applySearch($b);
        $b->select($this->listFields());
        $this->applyOrder($b);
        $datas = $this->findAll();
        $this->log();

        return $datas;
    }

    /** Supprime plusieurs lignes ; retourne le nombre de lignes supprimées. */
    public function delete_bulk(array $ids)
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (empty($ids)) {
            return 0;
        }
        parent::delete($ids);
        $this->log();

        return $this->db->affectedRows();
    }

    /** Met à jour la ligne courante avec $this->datas (limitée aux champs autorisés). */
    public function put($id = null)
    {
        if ($id) {
            $this->key_value = $id;
        }
        $datas = $this->datas;
        if ($this->autorized_fields) {
            $datas = array_intersect_key((array) $datas, array_flip($this->autorized_fields));
        }
        if (! $this->key_value || ! $datas) {
            return;
        }
        $this->update($this->key_value, $datas);
        $this->log();
    }

    /** Supprime la ligne $id (ou la ligne courante). */
    public function delete($id = null, bool $purge = false)
    {
        $id ??= $this->key_value;
        if (! $id) {
            return false;
        }
        $res = parent::delete($id, $purge);
        $this->log();

        return $res;
    }

    // ------------------------------------------------------------------
    // Accesseurs génériques
    // ------------------------------------------------------------------

    public function _set($field, $value)
    {
        $this->$field = $value;
        if ($field === 'key_value') {
            foreach ($this->defs as $obj) {
                $obj->_set('parent_id', $value);
            }
        }
    }

    public function _get($field)
    {
        return $this->$field;
    }

    public function __destruct()
    {
        if ($this->_debug) {
            d($this->_debug_array);
        }
    }
}
