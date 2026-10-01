<?php

namespace App\Libraries\Compat;

/**
 * Pagination au rendu identique a CI_Pagination (liens Bootstrap).
 * Options utilisees : base_url, total_rows, per_page, cur_page, use_page_numbers.
 */
class Pagination
{
    protected $config = [
        'base_url'         => '',
        'total_rows'       => 0,
        'per_page'         => 15,
        'cur_page'         => 1,
        'use_page_numbers' => true,
        'num_links'        => 2,
        'first_link'       => '&lsaquo; First',
        'next_link'        => '&gt;',
        'prev_link'        => '&lt;',
        'last_link'        => 'Last &rsaquo;',
    ];

    public function initialize($params = [])
    {
        $this->config = array_merge($this->config, (array) $params);

        // Libelles traduits si disponibles (fichier pagination_lang.php)
        $ci = Ci3::get();
        if ($ci !== null && isset($ci->lang)) {
            try {
                $ci->lang->load('pagination');
            } catch (\Throwable $e) {
                // fichier optionnel
            }
            foreach (['first', 'next', 'prev', 'last'] as $k) {
                $v = $ci->lang->language['pagination_' . $k . '_link'] ?? null;
                if ($v !== null) {
                    $this->config[$k . '_link'] = $v;
                }
            }
        }
        return $this;
    }

    protected function item($page, $label, $current = false)
    {
        $url = rtrim($this->config['base_url'], '/') . '/' . $page;
        if ($current) {
            return '<li class="page-item active"><span class="page-link">' . $label . '<span class="sr-only">(current)</span></span></li>';
        }
        return '<li class="page-item"><a class="page-link" href="' . $url . '">' . $label . '</a></li>';
    }

    public function create_links()
    {
        $per  = max(1, (int) $this->config['per_page']);
        $total = (int) $this->config['total_rows'];
        if ($total <= 0 || $total <= $per) {
            return '';
        }
        $pages = (int) ceil($total / $per);
        $cur   = min(max(1, (int) $this->config['cur_page']), $pages);
        $n     = (int) $this->config['num_links'];

        $out = '<div class="pagging text-center"><nav><ul class="pagination">';
        if ($cur - $n > 1) {
            $out .= $this->item(1, $this->config['first_link']);
        }
        if ($cur > 1) {
            $out .= $this->item($cur - 1, $this->config['prev_link']);
        }
        for ($i = max(1, $cur - $n); $i <= min($pages, $cur + $n); $i++) {
            $out .= $this->item($i, $i, $i === $cur);
        }
        if ($cur < $pages) {
            $out .= $this->item($cur + 1, $this->config['next_link']);
        }
        if ($cur + $n < $pages) {
            $out .= $this->item($pages, $this->config['last_link']);
        }
        return $out . '</ul></nav></div>';
    }
}
