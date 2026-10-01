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

if (! function_exists('field_error')) {
    /**
     * Message d'erreur de validation d'un champ, encadré par $open / $close.
     */
    function field_error(string $field, string $open = '', string $close = ''): string
    {
        $error = service('validation')->getError($field);

        return $error === '' ? '' : $open . esc($error) . $close;
    }
}

if (! function_exists('pagination_links')) {
    /**
     * Liens de pagination des listes (URL de la forme <base_url>/<page>).
     *
     * @param string $baseUrl   ex. base_url('Familys_controller/list/page')
     * @param int    $total     nombre total de lignes
     * @param int    $perPage   lignes par page
     * @param int    $current   page courante (à partir de 1)
     * @param int    $around    nombre de pages affichées de chaque côté
     */
    function pagination_links(string $baseUrl, int $total, int $perPage, int $current, int $around = 2): string
    {
        $perPage = max(1, $perPage);
        if ($total <= 0 || $total <= $perPage) {
            return '';
        }

        $pages   = (int) ceil($total / $perPage);
        $current = min(max(1, $current), $pages);
        $label   = static fn (string $k, string $default): string => lang('Pagination.pagination_' . $k . '_link') !== 'Pagination.pagination_' . $k . '_link'
            ? lang('Pagination.pagination_' . $k . '_link') : $default;
        $item = static function (int $page, string $text, bool $active = false) use ($baseUrl): string {
            if ($active) {
                return '<li class="page-item active"><span class="page-link">' . $text . '<span class="sr-only">(current)</span></span></li>';
            }

            return '<li class="page-item"><a class="page-link" href="' . rtrim($baseUrl, '/') . '/' . $page . '">' . $text . '</a></li>';
        };

        $html = '<div class="pagging text-center"><nav><ul class="pagination">';
        if ($current - $around > 1) {
            $html .= $item(1, $label('first', '&laquo;'));
        }
        if ($current > 1) {
            $html .= $item($current - 1, $label('prev', '&lt;'));
        }
        for ($i = max(1, $current - $around); $i <= min($pages, $current + $around); $i++) {
            $html .= $item($i, (string) $i, $i === $current);
        }
        if ($current < $pages) {
            $html .= $item($current + 1, $label('next', '&gt;'));
        }
        if ($current + $around < $pages) {
            $html .= $item($pages, $label('last', '&raquo;'));
        }

        return $html . '</ul></nav></div>';
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
