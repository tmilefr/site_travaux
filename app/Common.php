<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

use App\Libraries\Compat\Ci3;
use CodeIgniter\HTTP\Exceptions\RedirectException;

/*
 * ------------------------------------------------------------------
 * Fonctions de compatibilite avec les helpers CodeIgniter 3 utilises
 * par le code metier (controleurs, bibliotheques, vues).
 * ------------------------------------------------------------------
 */

if (! function_exists('get_instance')) {
    /**
     * Controleur courant (equivalent de get_instance() de CI3).
     */
    function &get_instance()
    {
        $ci = Ci3::get();
        return $ci;
    }
}

if (! function_exists('ci_redirect')) {
    /**
     * redirect() de CI3 : redirige et interrompt l'execution du controleur.
     */
    function ci_redirect($uri = '', $method = 'auto', $code = null)
    {
        $uri = (string) $uri;
        if (! preg_match('#^(\w+:)?//#i', $uri)) {
            $uri = ltrim($uri, '/');
        }
        throw new RedirectException(redirect()->to($uri, $code ?: 302));
    }
}

if (! function_exists('ci_lang')) {
    /**
     * lang() de CI3 : traduction d'une cle des fichiers de langue historiques.
     */
    function ci_lang($line, $for = '', $attributes = [])
    {
        $ci   = Ci3::get();
        $line = $ci !== null ? $ci->lang->line($line) : $line;

        if ($for !== '') {
            $line = '<label for="' . $for . '">' . $line . '</label>';
        }
        return $line;
    }
}

if (! function_exists('config_item')) {
    function config_item($item)
    {
        $ci = Ci3::get();
        return $ci !== null ? $ci->config->item($item) : null;
    }
}

if (! function_exists('html_escape')) {
    function html_escape($var, $double_encode = true)
    {
        if (empty($var)) {
            return $var;
        }
        if (is_array($var)) {
            return array_map('html_escape', $var, array_fill(0, count($var), $double_encode));
        }
        return htmlspecialchars((string) $var, ENT_QUOTES, 'UTF-8', $double_encode);
    }
}

if (! function_exists('form_error')) {
    function form_error($field = '', $prefix = '', $suffix = '')
    {
        $ci = Ci3::get();
        return ($ci !== null && isset($ci->form_validation)) ? $ci->form_validation->error($field, $prefix, $suffix) : '';
    }
}

if (! function_exists('validation_errors')) {
    function validation_errors($prefix = '', $suffix = '')
    {
        $ci = Ci3::get();
        return ($ci !== null && isset($ci->form_validation)) ? $ci->form_validation->error_string($prefix, $suffix) : '';
    }
}

if (! function_exists('set_status_header')) {
    function set_status_header($code = 200, $text = '')
    {
        service('response')->setStatusCode((int) $code);
        if (! headers_sent()) {
            http_response_code((int) $code);
        }
    }
}

if (! function_exists('show_error')) {
    /**
     * show_error() de CI3. En mode API (controleur Api) l'erreur est levee
     * sous forme d'exception CiError ; sinon la page d'erreur CI4 s'affiche.
     */
    function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
    {
        $message = implode(' / ', is_array($message) ? $message : [$message]);
        $ci      = Ci3::get();

        if ($status_code === 404) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($message);
        }
        if ($ci !== null && method_exists($ci, '_get') && $ci->_get('_api') == true) {
            set_status_header($status_code);
            throw new \App\Libraries\Compat\CiError($message, $status_code);
        }
        throw new \RuntimeException($message, $status_code >= 400 && $status_code < 600 ? $status_code : 500);
    }
}

if (! function_exists('show_404')) {
    function show_404($page = '', $log_error = true)
    {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($page);
    }
}

if (! function_exists('form_hidden')) {
    /**
     * form_hidden() de CI3 : accepte n'importe quel type de valeur
     * (la version CI4 est typee strictement).
     */
    function form_hidden($name, $value = '', $recursing = false)
    {
        static $form;

        if ($recursing === false) {
            $form = "\n";
        }

        if (is_array($name)) {
            foreach ($name as $key => $val) {
                form_hidden($key, $val, true);
            }
            return $form;
        }

        if (! is_array($value)) {
            $form .= '<input type="hidden" name="' . $name . '" value="' . html_escape($value) . "\" />\n";
        } else {
            foreach ($value as $key => $val) {
                $key = is_int($key) ? '' : $key;
                form_hidden($name . '[' . $key . ']', $val, true);
            }
        }

        return $form;
    }
}
