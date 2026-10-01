<?php

namespace App\Libraries\Compat;

use CodeIgniter\Database\BaseResult;

/**
 * Resultat de requete avec l'API CI3 (result(), row(), num_rows()...).
 */
class Result
{
    /** @var BaseResult */
    protected $result;

    public function __construct($result)
    {
        $this->result = $result;
    }

    public function result($type = 'object')
    {
        return $type === 'array' ? $this->result->getResultArray() : $this->result->getResultObject();
    }

    public function result_array()
    {
        return $this->result->getResultArray();
    }

    public function row($n = 0, $type = 'object')
    {
        return $type === 'array' ? $this->result->getRowArray($n) : $this->result->getRowObject($n);
    }

    public function row_array($n = 0)
    {
        return $this->result->getRowArray($n);
    }

    public function num_rows()
    {
        return $this->result->getNumRows();
    }

    public function num_fields()
    {
        return $this->result->getFieldCount();
    }

    public function list_fields()
    {
        return $this->result->getFieldNames();
    }

    public function free_result()
    {
        $this->result->freeResult();
    }

    public function __call($name, $args)
    {
        return $this->result->{$name}(...$args);
    }
}
