<?php

namespace App\Libraries\Compat;

/**
 * Equivalent de CI_Model : donne a chaque modele l'acces aux composants du
 * controleur courant ($this->session, $this->lang, $this->Autre_model ...).
 */
class Model
{
    public $db;

    public function __construct()
    {
        $this->db = Db::shared();
    }

    public function __get($name)
    {
        $ci = Ci3::get();
        if ($ci !== null && (isset($ci->$name) || property_exists($ci, $name))) {
            return $ci->$name;
        }
        return null;
    }

    public function __isset($name)
    {
        $ci = Ci3::get();
        return $ci !== null && isset($ci->$name);
    }
}
