<?php

namespace App\Libraries\Compat;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * Adaptateur du Query Builder de CI3 vers CI4.
 *
 * CI3 : $this->db->select()->where()->get('table')
 * CI4 : $this->db->table('table')->select()->where()->get()
 *
 * Les appels du builder sont mis en file puis rejoues sur un builder CI4 des
 * que la table est connue (get/insert/update/delete/...).
 */
class Db
{
    /** @var Db|null */
    protected static $shared = null;

    /** @var BaseConnection */
    protected $conn;

    protected $queue = [];
    protected $table = '';

    public static function shared(): Db
    {
        if (static::$shared === null) {
            static::$shared = new static(Database::connect());
        }
        return static::$shared;
    }

    public function __construct(BaseConnection $conn)
    {
        $this->conn = $conn;
    }

    public function connection(): BaseConnection
    {
        return $this->conn;
    }

    // ------------------------------------------------------------------
    // Mise en file des appels du Query Builder
    // ------------------------------------------------------------------

    public function from($table)
    {
        $this->table = is_array($table) ? implode(',', $table) : $table;
        return $this;
    }

    public function __call($method, $args)
    {
        // snake_case -> camelCase (where_in -> whereIn, or_like -> orLike ...)
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $method))));

        // CI3 ignorait order_by() sans champ (null / chaine vide)
        if ($camel === 'orderBy' && ($args[0] ?? '') === '' ) {
            return $this;
        }
        if ($camel === 'orderBy' && $args[0] === null) {
            return $this;
        }

        $this->queue[] = [$camel, $args];
        return $this;
    }

    /**
     * Construit le builder CI4 pour la table et y rejoue les appels en attente.
     */
    protected function build($table = '')
    {
        $table = $table !== '' && $table !== null ? $table : $this->table;
        $builder = $this->conn->table($table);
        foreach ($this->queue as [$method, $args]) {
            $builder = $builder->{$method}(...$args);
        }
        $this->reset();
        return $builder;
    }

    protected function reset(): void
    {
        $this->queue = [];
        $this->table = '';
    }

    // ------------------------------------------------------------------
    // Operations terminales
    // ------------------------------------------------------------------

    public function get($table = '', $limit = null, $offset = null)
    {
        $builder = $this->build($table);
        if ($limit !== null) {
            $builder->limit($limit, $offset ?? 0);
        }
        return new Result($builder->get());
    }

    public function get_where($table = '', $where = null, $limit = null, $offset = null)
    {
        if ($where !== null) {
            $this->queue[] = ['where', [$where]];
        }
        return $this->get($table, $limit, $offset);
    }

    public function count_all_results($table = '', $reset = true)
    {
        return $this->build($table)->countAllResults();
    }

    public function insert($table = '', $set = null)
    {
        $builder = $this->conn->table($table !== '' ? $table : $this->table);
        foreach ($this->queue as [$method, $args]) {
            $builder = $builder->{$method}(...$args);
        }
        $this->reset();
        return $builder->insert($set);
    }

    public function insert_batch($table = '', $set = null)
    {
        return $this->conn->table($table)->insertBatch($set);
    }

    public function update($table = '', $set = null, $where = null)
    {
        $builder = $this->build($table);
        if ($where !== null) {
            $builder->where($where);
        }
        return $builder->update($set);
    }

    public function delete($table = '', $where = '')
    {
        $builder = $this->build($table);
        if ($where !== '' && $where !== null) {
            $builder->where($where);
        }
        return $builder->delete();
    }

    public function truncate($table = '')
    {
        $table = $table !== '' ? $table : $this->table;
        $this->reset();
        return $this->conn->table($table)->truncate();
    }

    public function query($sql, $binds = false)
    {
        $res = $this->conn->query($sql, $binds === false ? null : $binds);
        if (is_object($res)) {
            return new Result($res);
        }
        return $res;
    }

    // ------------------------------------------------------------------
    // Infos de connexion
    // ------------------------------------------------------------------

    public function insert_id()
    {
        return $this->conn->insertID();
    }

    public function affected_rows()
    {
        return $this->conn->affectedRows();
    }

    public function last_query()
    {
        $q = $this->conn->getLastQuery();
        return $q === null ? '' : (string) $q;
    }

    public function error()
    {
        return $this->conn->error();
    }

    public function escape($str)
    {
        return $this->conn->escape($str);
    }

    public function escape_str($str)
    {
        return $this->conn->escapeString($str);
    }

    public function trans_start()
    {
        return $this->conn->transStart();
    }

    public function trans_complete()
    {
        return $this->conn->transComplete();
    }

    public function trans_status()
    {
        return $this->conn->transStatus();
    }

    public function list_fields($table)
    {
        return $this->conn->getFieldNames($table);
    }

    public function table_exists($table)
    {
        return $this->conn->tableExists($table);
    }
}
