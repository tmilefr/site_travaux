<?php

namespace App\Libraries\Compat;

/**
 * API CI_Lang : fichiers de langue au format historique
 * ($lang['CLE'] = 'texte';) dans app/Language/<langue>/<fichier>_lang.php
 */
class Lang
{
    public $language = [];
    public $is_loaded = [];
    protected $idiom = 'french';

    public function idiom(): string
    {
        return $this->idiom;
    }

    public function load($langfile, $idiom = '', $return = false, $add_suffix = true, $alt_path = '')
    {
        $langfile = str_replace('.php', '', $langfile);
        if ($add_suffix) {
            $langfile = preg_replace('/_lang$/', '', $langfile) . '_lang';
        }
        $langfile .= '.php';

        $idiom = $idiom ?: $this->idiom;

        if (!$return && isset($this->is_loaded[$langfile]) && $this->is_loaded[$langfile] === $idiom) {
            return true;
        }

        $path = APPPATH . 'Language/' . $idiom . '/' . $langfile;
        if (!is_file($path)) {
            throw new \RuntimeException('Unable to load the requested language file: language/' . $idiom . '/' . $langfile);
        }

        $lang = [];
        include $path;

        if ($return) {
            return $lang;
        }

        $this->is_loaded[$langfile] = $idiom;
        $this->language = array_merge($this->language, $lang);
        log_message('debug', 'Language file loaded: language/' . $idiom . '/' . $langfile);
        return true;
    }

    /**
     * Retourne la traduction, ou <i>CLE</i> si elle est absente.
     */
    public function line($line, $log_errors = true)
    {
        return isset($this->language[$line]) ? $this->language[$line] : '<i>' . $line . '</i>';
    }
}
