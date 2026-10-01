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

    public function post($index = null, $xss = false)
    {
        $req = $this->request();
        if ($index === null) {
            return $req->getPost() ?? [];
        }
        return $req->getPost($index);
    }

    public function get($index = null, $xss = false)
    {
        $req = $this->request();
        if ($index === null) {
            return $req->getGet() ?? [];
        }
        return $req->getGet($index);
    }

    public function post_get($index)
    {
        $v = $this->post($index);
        return $v !== null ? $v : $this->get($index);
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
