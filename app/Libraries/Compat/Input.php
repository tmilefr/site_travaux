<?php

namespace App\Libraries\Compat;

/**
 * API CI_Input sur la requete CI4.
 */
class Input
{
    protected function request()
    {
        return service('request');
    }

    /**
     * Lecture dans un tableau (superglobale) avec la syntaxe CI3 :
     * "champ" ou "champ[cle]". On lit les superglobales directement (comme CI3)
     * car le code metier les modifie parfois ($_POST['x'] = ...).
     */
    protected function fetch(array $source, $index = null)
    {
        if ($index === null) {
            return $source;
        }
        if (is_array($index)) {
            $out = [];
            foreach ($index as $key) {
                $out[$key] = $this->fetch($source, $key);
            }
            return $out;
        }
        if (isset($source[$index])) {
            return $source[$index];
        }
        if (($pos = strpos($index, '[')) !== false && preg_match_all('/\[(.*?)\]/', $index, $m)) {
            $value = $source[substr($index, 0, $pos)] ?? null;
            foreach ($m[1] as $key) {
                if (!is_array($value)) {
                    return null;
                }
                if ($key === '') {
                    return $value;
                }
                $value = $value[$key] ?? null;
            }
            return $value;
        }
        return null;
    }

    public function post($index = null, $xss = false)
    {
        return $this->fetch($_POST, $index);
    }

    public function get($index = null, $xss = false)
    {
        return $this->fetch($_GET, $index);
    }

    public function post_get($index)
    {
        $v = $this->post($index);
        return $v !== null ? $v : $this->get($index);
    }

    public function get_post($index)
    {
        $v = $this->get($index);
        return $v !== null ? $v : $this->post($index);
    }

    public function server($index = null)
    {
        if ($index === null) {
            return $_SERVER;
        }
        return $_SERVER[$index] ?? null;
    }

    public function cookie($index = null)
    {
        return $this->request()->getCookie($index);
    }

    public function ip_address()
    {
        return $this->request()->getIPAddress();
    }

    public function is_ajax_request()
    {
        $req = $this->request();
        return method_exists($req, 'isAJAX') ? $req->isAJAX() : false;
    }

    public function method($upper = false)
    {
        $m = $this->request()->getMethod();
        return $upper ? strtoupper($m) : strtolower($m);
    }

    public function get_request_header($name)
    {
        $h = $this->request()->getHeaderLine($name);
        return $h === '' ? null : $h;
    }

    public function raw_input_stream()
    {
        return $this->request()->getBody();
    }
}
