<?php

namespace App\Libraries\Compat;

/**
 * Registre de l'instance "controleur courant".
 *
 * Remplace get_instance() de CodeIgniter 3 : les bibliotheques, modeles et vues
 * historiques accedent aux services (db, session, lang, config...) via le
 * controleur courant.
 */
class Ci3
{
    /** @var object|null */
    protected static $instance = null;

    public static function set($controller): void
    {
        if (static::$instance === null) {
            static::$instance = $controller;
        }
    }

    /** Force le remplacement (tests, CLI) */
    public static function reset($controller = null): void
    {
        static::$instance = $controller;
    }

    public static function get()
    {
        return static::$instance;
    }
}
