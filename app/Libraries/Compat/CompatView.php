<?php

namespace App\Libraries\Compat;

use CodeIgniter\View\View;

/**
 * Vue CI4 qui expose, comme CI3, les composants du controleur courant
 * a l'interieur des gabarits ($this->render_object, $this->lang, $this->acl ...).
 */
class CompatView extends View
{
    public function __get($name)
    {
        $ci = Ci3::get();
        // $this->config est deja la configuration du moteur de vues CI4 :
        // les gabarits utilisent $this->conf pour la config applicative CI3.
        if ($name === 'conf') {
            return $ci !== null ? $ci->config : null;
        }
        if ($ci !== null && (isset($ci->$name) || property_exists($ci, $name))) {
            return $ci->$name;
        }
        return null;
    }

    public function __isset($name)
    {
        $ci = Ci3::get();
        if ($name === 'conf') {
            return $ci !== null;
        }
        return $ci !== null && isset($ci->$name);
    }

    public function __call($name, $args)
    {
        $ci = Ci3::get();
        if ($ci !== null && method_exists($ci, $name)) {
            return $ci->$name(...$args);
        }
        throw new \BadMethodCallException('Call to undefined method ' . static::class . '::' . $name . '()');
    }
}
