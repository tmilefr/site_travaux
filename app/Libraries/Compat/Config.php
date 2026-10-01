<?php

namespace App\Libraries\Compat;

/**
 * API CI_Config : fichiers historiques ($config['cle'] = ...) places dans
 * app/Config/legacy/<nom>.php
 */
class Config
{
    public $config = [];
    public $is_loaded = [];

    public function __construct()
    {
        $this->config['language'] = 'french';
        $this->config['charset']  = 'UTF-8';
    }

    public function load($file = '', $use_sections = false, $fail_gracefully = false)
    {
        $file = str_replace('.php', '', $file ?: 'config');
        $path = APPPATH . 'Config/legacy/' . $file . '.php';

        if (!is_file($path)) {
            if ($fail_gracefully) {
                return false;
            }
            return false; // fichier optionnel (ex: secured.php non versionne)
        }
        if (in_array($path, $this->is_loaded, true)) {
            return true;
        }

        $config = [];
        include $path;

        if ($use_sections) {
            $this->config[$file] = array_merge($this->config[$file] ?? [], $config);
        } else {
            $this->config = array_merge($this->config, $config);
        }
        $this->is_loaded[] = $path;
        return true;
    }

    public function item($item, $index = '')
    {
        if ($item === 'base_url') {
            return base_url();
        }
        if ($index === '') {
            return $this->config[$item] ?? null;
        }
        return $this->config[$index][$item] ?? null;
    }

    public function set_item($item, $value)
    {
        $this->config[$item] = $value;
    }

    public function base_url($uri = '')
    {
        return base_url($uri);
    }

    public function site_url($uri = '')
    {
        return site_url($uri);
    }
}
