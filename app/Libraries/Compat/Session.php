<?php

namespace App\Libraries\Compat;

/**
 * API CI_Session (userdata, flashdata...) sur la session CI4.
 */
class Session
{
    protected function s()
    {
        return session();
    }

    public function userdata($key = null)
    {
        $s = $this->s();
        if ($key === null) {
            return $s->get();
        }
        return $s->get($key);
    }

    public function has_userdata($key)
    {
        return $this->s()->has($key);
    }

    public function set_userdata($data, $value = null)
    {
        if (is_array($data)) {
            $this->s()->set($data);
        } else {
            $this->s()->set($data, $value);
        }
    }

    public function unset_userdata($key)
    {
        $this->s()->remove($key);
    }

    public function set_flashdata($data, $value = null)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $this->s()->setFlashdata($k, $v);
            }
        } else {
            $this->s()->setFlashdata($data, $value);
        }
    }

    public function flashdata($key = null)
    {
        return $this->s()->getFlashdata($key);
    }

    public function keep_flashdata($key)
    {
        $this->s()->keepFlashdata($key);
    }

    public function sess_destroy()
    {
        $this->s()->destroy();
    }

    public function sess_regenerate($destroy = false)
    {
        $this->s()->regenerate($destroy);
    }

    public function session_id()
    {
        return session_id();
    }

    public function __get($name)
    {
        return $this->s()->get($name);
    }
}
