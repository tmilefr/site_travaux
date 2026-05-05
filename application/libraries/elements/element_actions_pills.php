<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * element_actions_pills.php
 * -----------------------------------------------------------------------------
 * Override du type "table" pour le rendu en LISTE des actions ACL.
 *
 *  - Hérite de element_table : conserve le formulaire d'édition, le PrepareForDBA,
 *    le AfterExec, le RenderFormElement (lignes éditables, addRow / removeRow…)
 *    => zéro impact sur les écrans d'édition.
 *  - Override uniquement la méthode Render() utilisée dans la VUE DE LISTE,
 *    pour passer du tableau vertical (1 action par ligne) à un rendu compact
 *    sous forme de "pills" : Standard (CRUD) regroupé, Métier mis en avant.
 *
 * Activation : dans le JSON du modèle, passer le champ concerné de
 *   "type": "table"  ->  "type": "actions_pills"
 *
 * Le moteur d'instanciation (Core_model::_init_def) charge automatiquement
 * application/libraries/elements/element_{type}.php → aucune modif du core.
 */

require_once(APPPATH.'libraries/elements/element_table.php');

class element_actions_pills extends element_table
{
    /**
     * Mapping des actions CRUD reconnues -> icône + classe de badge.
     * Toute action absente de cette map est considérée "métier".
     */
    protected static $crud_map = array(
        'list'      => array('icon' => 'oi-list',   'class' => 'badge-secondary'),
        'view'      => array('icon' => 'oi-eye',    'class' => 'badge-secondary'),
        'add'       => array('icon' => 'oi-plus',   'class' => 'badge-primary'),
        'edit'      => array('icon' => 'oi-pencil', 'class' => 'badge-primary'),
        'delete'    => array('icon' => 'oi-trash',  'class' => 'badge-danger'),
        'set_rules' => array('icon' => 'oi-key',    'class' => 'badge-warning'),
    );

    protected static $default_pill = array('icon' => 'oi-cog', 'class' => 'badge-success');

    /**
     * Rendu compact des actions en pills.
     * Conserve la signature de element_table::Render($format = false) pour
     * rester compatible avec les autres modes (json, raw) si jamais ils sont
     * utilisés ailleurs : on ne réimplémente que le mode HTML par défaut.
     *
     * @param  string|bool $format 'json' | 'raw' | false (html)
     * @return string|array
     */
    public function Render($format = false)
    {
        // Pour les formats non-HTML, on délègue au parent (comportement legacy)
        if ($format === 'json' || $format === 'raw') {
            return parent::Render($format);
        }

        // ---------------------------------------------------------------
        // Récupération des actions liées (logique identique au parent)
        // ---------------------------------------------------------------
        if (!$this->parent_id) {
            return $this->value;
        }
        $this->_load_table_model();
        if (!isset($this->CI->{$this->model})) {
            return $this->model . ' not instantiate';
        }

        $this->CI->{$this->model}->_set('filter', array($this->foreignkey => $this->parent_id));
        $this->CI->{$this->model}->_set('order', $this->ref ?: 'action');
        $this->CI->{$this->model}->_set('direction', 'asc');
        $datas = $this->CI->{$this->model}->get_all();

        $nb_actions = is_array($datas) ? count($datas) : 0;

        // ---------------------------------------------------------------
        // Tri Standard / Métier
        // ---------------------------------------------------------------
        $std    = array();
        $custom = array();
        if ($nb_actions > 0) {
            // Le champ "nom de l'action" est défini par $ref dans le JSON
            // (ex. "ref":"action" pour Acl_controllers).
            $name_field = $this->ref ?: 'action';
            foreach ($datas as $row) {
                $name = isset($row->{$name_field}) ? (string) $row->{$name_field} : '';
                if ($name === '') { continue; }
                $key = strtolower($name);
                if (isset(self::$crud_map[$key])) {
                    $std[] = array('name' => $name, 'cfg' => self::$crud_map[$key]);
                } else {
                    $custom[] = array('name' => $name, 'cfg' => self::$default_pill);
                }
            }
        }

        // ---------------------------------------------------------------
        // Libellés (avec fallback si la clé de langue n'est pas définie)
        // ---------------------------------------------------------------
        $lbl_standard = $this->CI->lang->line('ACL_LBL_STANDARD');
        if (!$lbl_standard) { $lbl_standard = 'Standard'; }
        $lbl_business = $this->CI->lang->line('ACL_LBL_BUSINESS');
        if (!$lbl_business) { $lbl_business = 'Métier'; }
        $lbl_no_action = $this->CI->lang->line('NO_ACTION_DEFINED');
        if (!$lbl_no_action) { $lbl_no_action = 'Aucune action définie.'; }
        $lbl_no_custom = $this->CI->lang->line('ACL_LBL_NO_CUSTOM');
        if (!$lbl_no_custom) { $lbl_no_custom = 'CRUD uniquement.'; }

        // ---------------------------------------------------------------
        // Construction du HTML
        // ---------------------------------------------------------------
        $out = '<div class="acl-actions-pills" data-nb-actions="' . $nb_actions . '">';

        if ($nb_actions === 0) {
            $out .= '<span class="acl-actions-pills__empty">'
                  .   '<span class="oi oi-info" aria-hidden="true"></span> '
                  .   htmlspecialchars($lbl_no_action, ENT_QUOTES, 'UTF-8')
                  . '</span>';
        } else {
            // -- Bloc Standard --
            if (count($std) > 0) {
                $out .= '<div class="acl-actions-pills__row">';
                $out .= '<span class="acl-actions-pills__label">'
                      . htmlspecialchars($lbl_standard, ENT_QUOTES, 'UTF-8')
                      . ' <span class="acl-actions-pills__count">(' . count($std) . ')</span>'
                      . '</span>';
                $out .= '<div class="acl-actions-pills__list">';
                foreach ($std as $entry) {
                    $out .= $this->_render_pill($entry['name'], $entry['cfg'], 'std');
                }
                $out .= '</div></div>';
            }

            // -- Bloc Métier --
            $out .= '<div class="acl-actions-pills__row acl-actions-pills__row--custom">';
            $out .= '<span class="acl-actions-pills__label">'
                  . htmlspecialchars($lbl_business, ENT_QUOTES, 'UTF-8');
            if (count($custom) > 0) {
                $out .= ' <span class="acl-actions-pills__count">(' . count($custom) . ')</span>';
            }
            $out .= '</span>';
            $out .= '<div class="acl-actions-pills__list">';
            if (count($custom) > 0) {
                foreach ($custom as $entry) {
                    $out .= $this->_render_pill($entry['name'], $entry['cfg'], 'custom');
                }
            } else {
                $out .= '<span class="acl-actions-pills__empty acl-actions-pills__empty--inline">'
                      .   '<span class="oi oi-minus" aria-hidden="true"></span> '
                      .   htmlspecialchars($lbl_no_custom, ENT_QUOTES, 'UTF-8')
                      . '</span>';
            }
            $out .= '</div></div>';
        }

        $out .= '</div>';

        // CSS injecté une seule fois par requête (drapeau statique)
        $out .= $this->_render_css_once();

        return $out;
    }

    /**
     * Helper : rend un seul pill.
     */
    protected function _render_pill($name, array $cfg, $kind)
    {
        $safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        return '<span class="badge acl-pill acl-pill--' . $kind . ' ' . $cfg['class'] . '" '
             . 'title="' . $safe . '">'
             .   '<span class="oi ' . $cfg['icon'] . '" aria-hidden="true"></span> '
             .   $safe
             . '</span>';
    }

    /**
     * Charge le modèle des actions (équivalent à _load_model du parent
     * qui est private, donc on duplique en protected pour pouvoir l'utiliser).
     */
    protected function _load_table_model()
    {
        if ($this->model && !isset($this->CI->{$this->model})) {
            $this->CI->load->model($this->model);
        }
    }

    /**
     * Injecte le CSS associé une seule fois par requête.
     */
    protected function _render_css_once()
    {
        static $css_done = false;
        if ($css_done) {
            return '';
        }
        $css_done = true;

        return <<<CSS
<style>
/* Rendu compact des actions ACL (element_actions_pills) */
.acl-actions-pills {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    padding: 0.2rem 0;
}
.acl-actions-pills__row {
    display: flex;
    align-items: flex-start;
    gap: 0.6rem;
    flex-wrap: nowrap;
}
.acl-actions-pills__label {
    flex: 0 0 auto;
    min-width: 70px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #8a8a8a;
    font-weight: 600;
    padding-top: 0.45em;
    white-space: nowrap;
}
.acl-actions-pills__count { color: #b0b0b0; font-weight: 400; }
.acl-actions-pills__list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    flex: 1 1 auto;
    min-width: 0;
}
.acl-pill {
    font-size: 0.8rem;
    padding: 0.35em 0.65em;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-weight: 500;
    text-transform: lowercase;
    border-radius: 999px;
    line-height: 1.2;
}
.acl-pill .oi { font-size: 0.7rem; }
.acl-pill--std { opacity: 0.85; }
.acl-pill--std:hover { opacity: 1; }
.acl-pill--custom { box-shadow: 0 0 0 1px rgba(0,0,0,0.04); }

.acl-actions-pills__empty {
    color: #b0b0b0;
    font-style: italic;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.acl-actions-pills__empty--inline { padding-top: 0.35em; }

@media (max-width: 600px) {
    .acl-actions-pills__row { flex-direction: column; gap: 0.2rem; }
    .acl-actions-pills__label { padding-top: 0; }
}
</style>
CSS;
    }
}
