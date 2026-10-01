<?php

namespace App\Libraries\Compat;

use Config\Database;

/**
 * Adaptateur de dbforge CI3 -> Forge CI4.
 */
class Forge
{
    protected $forge;

    public function __construct()
    {
        $this->forge = Database::forge();
    }

    public function add_field($fields)
    {
        $this->forge->addField($fields);
        return $this;
    }

    public function add_key($key, $primary = false)
    {
        $this->forge->addKey($key, $primary);
        return $this;
    }

    public function create_table($table, $ifNotExists = false, array $attributes = [])
    {
        return $this->forge->createTable($table, $ifNotExists, $attributes);
    }

    public function modify_column($table, $fields)
    {
        return $this->forge->modifyColumn($table, $fields);
    }

    public function __call($name, $args)
    {
        $camel = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        return $this->forge->{$camel}(...$args);
    }
}
