<?php

namespace App\Libraries\Compat;

/**
 * API CI_Router : nom de classe/methode routes.
 */
class Router
{
    public function __get($name)
    {
        if ($name === 'class') {
            return $this->fetch_class();
        }
        if ($name === 'method') {
            return $this->fetch_method();
        }
        return null;
    }

    public function fetch_class()
    {
        $name = service('router')->controllerName();
        if ($name instanceof \Closure) {
            return '';
        }
        $parts = explode('\\', (string) $name);
        return end($parts);
    }

    public function fetch_method()
    {
        return service('router')->methodName();
    }
}
