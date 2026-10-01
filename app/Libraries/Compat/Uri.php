<?php

namespace App\Libraries\Compat;

/**
 * API CI_URI (segments) sur le chemin de la requete CI4.
 */
class Uri
{
    public function segment_array()
    {
        $path = trim(service('request')->getPath(), '/');
        if ($path === '') {
            return [];
        }
        return array_combine(range(1, count($segs = explode('/', $path))), $segs);
    }

    public function segment($n, $no_result = null)
    {
        $segs = $this->segment_array();
        return $segs[$n] ?? $no_result;
    }

    /**
     * Segment "route" : 1 = controleur, 2 = methode (valeurs par defaut incluses
     * quand l'URL est vide), au-dela = parametres.
     */
    public function rsegment($n, $no_result = null)
    {
        $router = service('router');
        if ($n === 1) {
            $class = $router->controllerName();
            if ($class instanceof \Closure) {
                return $no_result;
            }
            $parts = explode('\\', (string) $class);
            return end($parts);
        }
        if ($n === 2) {
            return $router->methodName();
        }
        $params = $router->params();
        return $params[$n - 3] ?? $no_result;
    }

    public function total_segments()
    {
        return count($this->segment_array());
    }

    public function uri_string()
    {
        return trim(service('request')->getPath(), '/');
    }

    /**
     * Transforme les segments en tableau associatif cle/valeur a partir du segment $n.
     */
    public function uri_to_assoc($n = 3, $default = [])
    {
        $segs = array_values($this->segment_array());
        $segs = array_slice($segs, $n - 1);
        $assoc = [];
        for ($i = 0, $c = count($segs); $i < $c; $i += 2) {
            $assoc[$segs[$i]] = $segs[$i + 1] ?? null;
        }
        return $assoc;
    }
}
