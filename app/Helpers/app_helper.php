<?php

/**
 * Fonctions d'aide de l'application (chargées par Config\Autoload::$helpers).
 */

if (! function_exists('tr')) {
    /**
     * Traduit une clé de l'application.
     *
     * Les libellés sont répartis dans app/Language/<langue>/ :
     *  - <Controleur>.php : libellés propres au contrôleur courant (prioritaire)
     *  - Menu.php         : libellés des menus (toutes les pages)
     *  - Traduction.php   : libellés communs
     * Une clé introuvable est renvoyée entre <i></i> pour être repérée à l'écran.
     */
    function tr($key, array $args = []): string
    {
        $key = (string) $key;

        static $files = null;

        if ($files === null) {
            $files = [];
            $name  = service('router')->controllerName();
            if (is_string($name) && $name !== '') {
                $short   = substr(strrchr('\\' . $name, '\\'), 1);
                $files[] = ucfirst(strtolower($short));
            }
            // Contrôleurs rattachés à d'autres fichiers de libellés
            array_push($files, 'Cantine', 'Inscriptions', 'Menu', 'Traduction');
        }

        foreach ($files as $file) {
            $line = lang($file . '.' . $key, $args);
            if ($line !== $file . '.' . $key) {
                return $line;
            }
        }

        return '<i>' . $key . '</i>';
    }
}

if (! function_exists('open_form')) {
    /**
     * form_open() de CodeIgniter avec des champs cachés de type quelconque
     * (identifiants entiers, null...) convertis en chaînes.
     */
    function open_form(string $action = '', $attributes = [], array $hidden = []): string
    {
        return form_open($action, $attributes, array_map(static fn ($v) => is_array($v) ? $v : (string) $v, $hidden));
    }
}

if (! function_exists('open_form_multipart')) {
    function open_form_multipart(string $action = '', $attributes = [], array $hidden = []): string
    {
        return form_open_multipart($action, $attributes, array_map(static fn ($v) => is_array($v) ? $v : (string) $v, $hidden));
    }
}
