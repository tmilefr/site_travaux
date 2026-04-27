<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* GENERIC LIST VIEW — version v2
 * ------------------------------
 * Variables disponibles (héritées de MY_Controller::list()) :
 *
 *   Vague 1
 *   - $datas, $_model_name, $footer_line
 *   - $total_rows, $per_page, $per_page_options, $cur_page
 *   - $active_filters, $global_search
 *
 *   Vague 2
 *   - $order_stack, $direction_stack    : pile de tris courante
 *   - $hidden_columns                   : colonnes masquées (session)
 *   - $hideable_columns                 : whitelist (vide = toutes masquables)
 *   - $bulk_actions                     : actions disponibles en lot
 */

$controller_name = $this->render_object->_getCi('_controller_name');
$action          = $this->render_object->_getCi('_action');
$base            = base_url($controller_name . '/' . $action);
$base_ctrl       = base_url($controller_name);

$has_filters = (is_array($active_filters) && count($active_filters) > 0)
            || (isset($global_search) && $global_search !== '');

$has_sort = is_array($order_stack) && count($order_stack) > 0;

$nb_pages = ($per_page > 0) ? (int) ceil($total_rows / $per_page) : 1;
if ($nb_pages < 1) { $nb_pages = 1; }

// Détermine si une colonne est masquable
$is_hideable = function($field) use ($hideable_columns) {
    return empty($hideable_columns) || in_array($field, $hideable_columns, true);
};

// Récupère les définitions de champs une seule fois
$defs = $this->{$_model_name}->_get('defs');

// Liste des champs visibles dans le tableau (list:true et non masqué)
$visible_fields = array();
foreach ($defs as $field => $def) {
    if ($def->list === true && !in_array($field, $hidden_columns, true)) {
        $visible_fields[] = $field;
    }
}

// Bulk actions activées ?
$has_bulk = is_array($bulk_actions) && count($bulk_actions) > 0;
?>

<!--start section-->
<section class="nicdark_section list-view-v2">
    <div class="nicdark_container nicdark_clearfix">
    <div class="nicdark_space30"></div>

    <div class="grid grid_12">
        <h1 class="subtitle greydark"><?php echo $this->lang->line($controller_name . '_' . $action); ?></h1>
        <div class="nicdark_space20"></div>
        <h3 class="subtitle grey">
        <?php
            if ($this->render_object->_get('_ui_rules') AND !$this->render_object->_get('form_mod')){
                foreach($this->render_object->_get('_ui_rules') AS $rule){
                    if (in_array($rule->term , $this->render_object->_get('_not_link_list')) AND $rule->autorize ){
                        echo '<a class="" href="'.$rule->url.'"><span class="'.$rule->icon.' "></span> '.$rule->name.'</a>&nbsp;';
                    }
                }
            }
        ?>
        </h3>
        <div class="nicdark_space20"></div>
        <div class="nicdark_divider left big"><span class="<?php echo $this->render_object->_getCi('_bg_color');?> nicdark_radius"></span></div>
        <div class="nicdark_space10"></div>
    </div>

    <?php /* ---------------------------------------------------------------
         FLASH MESSAGES (bulk actions)
       ---------------------------------------------------------------- */ ?>
    <?php if ($flash = $this->session->flashdata('bulk_success')) { ?>
        <div class="grid grid_12">
            <div class="alert alert-success"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    <?php } ?>
    <?php if ($flash = $this->session->flashdata('bulk_error')) { ?>
        <div class="grid grid_12">
            <div class="alert alert-danger"><?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
    <?php } ?>

    <?php /* ---------------------------------------------------------------
         BARRE D'OUTILS : tri actif, colonnes, export
       ---------------------------------------------------------------- */ ?>
    <div class="grid grid_12 list-toolbar">
        <div class="d-flex flex-wrap" style="gap:0.5em; margin-bottom:0.7em; align-items:center;">

            <?php /* Indicateur de tri courant + bouton reset */ ?>
            <?php if ($has_sort) { ?>
                <span class="navbar-text">
                    <strong><?php echo $this->lang->line('LIST_SORTED_BY'); ?> :</strong>
                    <?php
                    foreach ($order_stack as $i => $f) {
                        $arrow = (isset($direction_stack[$i]) && $direction_stack[$i] === 'desc') ? '↓' : '↑';
                        $label = $this->lang->line($f) ?: $f;
                        echo '<span class="badge badge-info" style="margin-left:0.3em;">'
                            . ($i + 1) . '. ' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . ' ' . $arrow
                            . '</span>';
                    }
                    ?>
                </span>
                <a href="<?php echo $base; ?>/order_clear/1"
                   class="btn btn-light btn-sm" title="<?php echo $this->lang->line('LIST_RESET_SORT'); ?>">
                    <span class="oi oi-circle-x"></span>
                </a>
            <?php } ?>

            <?php /* Dropdown colonnes (uniquement s'il y a des colonnes masquables) */ ?>
            <?php
            $any_hideable = false;
            foreach ($defs as $field => $def) {
                if ($def->list === true && $is_hideable($field)) { $any_hideable = true; break; }
            }
            ?>
            <?php if ($any_hideable) { ?>
                <div class="dropdown" style="margin-left:auto;">
                    <button class="btn btn-light btn-sm dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-expanded="false">
                        <span class="oi oi-cog"></span> <?php echo $this->lang->line('LIST_COLUMNS'); ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" style="padding:0.5em 1em;">
                        <?php foreach ($defs as $field => $def) { ?>
                            <?php if ($def->list === true && $is_hideable($field)) { ?>
                                <?php
                                $is_visible = !in_array($field, $hidden_columns, true);
                                $label = $this->lang->line($field) ?: $field;
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="col_<?php echo htmlspecialchars($field, ENT_QUOTES, 'UTF-8'); ?>"
                                           <?php echo $is_visible ? 'checked' : ''; ?>
                                           onchange="window.location.href='<?php echo $base; ?>/column_toggle/<?php echo urlencode($field); ?>';">
                                    <label class="form-check-label"
                                           for="col_<?php echo htmlspecialchars($field, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>

            <?php /* Bouton export CSV */ ?>
            <?php if ($total_rows > 0) { ?>
                <a href="<?php echo $base_ctrl; ?>/export_csv"
                   class="btn btn-success btn-sm">
                    <span class="oi oi-data-transfer-download"></span>
                    <?php echo $this->lang->line('LIST_EXPORT_CSV'); ?>
                </a>
            <?php } ?>
        </div>
    </div>

    <?php /* ---------------------------------------------------------------
         BANDEAU FILTRES ACTIFS (vague 1)
       ---------------------------------------------------------------- */ ?>
    <?php if ($has_filters) { ?>
        <div class="grid grid_12">
            <div class="alert alert-info" style="display:flex; flex-wrap:wrap; align-items:center; gap:0.5em;">
                <strong><?php echo $this->lang->line('LIST_ACTIVE_FILTERS'); ?> :</strong>

                <?php if (isset($global_search) && $global_search !== '') { ?>
                    <span class="badge badge-secondary" style="font-size:0.95em;">
                        <?php echo $this->lang->line('LIST_SEARCH'); ?> :
                        &laquo;&nbsp;<?php echo htmlspecialchars($global_search, ENT_QUOTES, 'UTF-8'); ?>&nbsp;&raquo;
                        <a href="<?php echo $base; ?>/search/reset"
                           class="text-white" style="margin-left:0.4em;">
                            <span class="oi oi-circle-x"></span>
                        </a>
                    </span>
                <?php } ?>

                <?php
                if (is_array($active_filters)) {
                    foreach ($active_filters as $field => $value) {
                        $field_label = $this->lang->line($field) ?: $field;
                        $value_label = $value;
                        if (isset($defs[$field])) {
                            $values = $defs[$field]->_get('values');
                            if (is_array($values) && isset($values[$value])) {
                                $value_label = $values[$value];
                            }
                        }
                        ?>
                        <span class="badge badge-success" style="font-size:0.95em;">
                            <?php echo htmlspecialchars($field_label, ENT_QUOTES, 'UTF-8'); ?> :
                            <?php echo htmlspecialchars($value_label, ENT_QUOTES, 'UTF-8'); ?>
                            <a href="<?php echo $base; ?>/filter/<?php echo urlencode($field); ?>/filter_value/all"
                               class="text-white" style="margin-left:0.4em;">
                                <span class="oi oi-circle-x"></span>
                            </a>
                        </span>
                        <?php
                    }
                }
                ?>

                <a href="<?php echo $base_ctrl; ?>/clear_filters"
                   class="btn btn-warning btn-sm" style="margin-left:auto;">
                    <span class="oi oi-trash"></span>
                    <?php echo $this->lang->line('LIST_RESET_ALL'); ?>
                </a>
            </div>
        </div>
    <?php } ?>

    <?php /* ---------------------------------------------------------------
         TABLEAU OU ÉTAT VIDE
         La div .list-table-responsive bascule en mode "carte" sous 768px
         via CSS pure (voir list_view_responsive.css).
       ---------------------------------------------------------------- */ ?>
    <?php if (empty($datas)) { ?>

        <div class="grid grid_12">
            <div class="alert alert-light text-center" style="padding:2em;">
                <p style="font-size:1.2em; margin-bottom:1em;">
                    <span class="oi oi-info" aria-hidden="true"></span>
                    <?php echo $this->lang->line(
                        $has_filters ? 'LIST_EMPTY_FILTERED' : 'LIST_EMPTY'
                    ); ?>
                </p>
                <?php if ($has_filters) { ?>
                    <a href="<?php echo $base_ctrl; ?>/clear_filters" class="btn btn-warning">
                        <span class="oi oi-trash"></span>
                        <?php echo $this->lang->line('LIST_RESET_ALL'); ?>
                    </a>
                <?php } ?>
            </div>
        </div>

    <?php } else { ?>

        <?php if ($has_bulk) { ?>
        <form method="post" action="<?php echo $base_ctrl; ?>/bulk" id="bulkForm"
              onsubmit="return listBulkSubmit(this);">
        <?php } ?>

        <div class="list-table-responsive grid grid_12">
        <table class="table table-striped table-sm list-table-v2">
        <thead>
            <tr>
                <?php if ($has_bulk) { ?>
                    <th scope="col" class="bulk-col">
                        <input type="checkbox" id="bulkSelectAll" aria-label="Tout sélectionner">
                    </th>
                <?php } ?>
                <th scope="col">&nbsp;</th>
                <?php foreach ($visible_fields as $field) { ?>
                    <th scope="col"><?php echo $this->render_object->render_link($field); ?></th>
                <?php } ?>
            </tr>
        </thead>
        <tbody>
        <?php
        $key = $this->{$_model_name}->_get('key');
        foreach ($datas as $data) {
            $row_id = isset($data->{$key}) ? $data->{$key} : '';
            echo '<tr>';

            if ($has_bulk) {
                echo '<td class="bulk-col" data-label="">';
                echo '<input type="checkbox" class="bulk-row-check" name="bulk_ids[]" value="'
                    . htmlspecialchars($row_id, ENT_QUOTES, 'UTF-8') . '">';
                echo '</td>';
            }

            echo '<td data-label="" class="actions-col">';
            echo $this->render_object->render_element_menu($data, ((isset($data->blocked)) ? $data->blocked : null));
            echo '</td>';

            foreach ($visible_fields as $field) {
                $label = $this->lang->line($field) ?: $field;
                echo '<td data-label="' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '">';
                echo $this->render_object->RenderElement($field, $data->{$field}, $row_id);
                echo '</td>';
            }
            echo '</tr>';
        }
        ?>
        </tbody>
        </table>
        </div>

        <?php /* Barre d'actions groupées (en bas, juste avant le footer) */ ?>
        <?php if ($has_bulk) { ?>
            <div class="grid grid_12 bulk-bar" id="bulkBar" style="display:none;">
                <div class="alert alert-light" style="display:flex; align-items:center; gap:0.7em; flex-wrap:wrap;">
                    <strong>
                        <span id="bulkCount">0</span>
                        <?php echo $this->lang->line('BULK_SELECTED'); ?>
                    </strong>

                    <?php foreach ($bulk_actions as $action_key => $opts) { ?>
                        <?php
                        $btn_class = isset($opts['class']) ? $opts['class'] : 'btn-secondary';
                        $confirm   = !empty($opts['confirm']);
                        $label     = $this->lang->line($opts['label_key']) ?: $action_key;
                        ?>
                        <button type="submit"
                                name="bulk_action"
                                value="<?php echo htmlspecialchars($action_key, ENT_QUOTES, 'UTF-8'); ?>"
                                class="btn btn-sm <?php echo $btn_class; ?>"
                                <?php if ($confirm) { ?>
                                    onclick="return confirm('<?php
                                        echo addslashes(
                                            $this->lang->line('BULK_CONFIRM_PREFIX') . ' ' .
                                            strtolower($label) . ' ?'
                                        );
                                    ?>');"
                                <?php } ?>>
                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
            </form>
        <?php } ?>

    <?php } ?>

    <?php /* ---------------------------------------------------------------
         FOOTER : pagination + compteur + sélecteur per_page
       ---------------------------------------------------------------- */ ?>
    <footer class="footer mt-auto py-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <ul class="navbar-nav mr-auto" style="align-items:center; flex-wrap:wrap;">
                <li class="nav-item">
                    <?php echo ((isset($this->pagination)) ? $this->pagination->create_links() : ''); ?>
                </li>

                <?php if ($total_rows > 0) { ?>
                    <li class="nav-item" style="margin-left:1em;">
                        <span class="navbar-text">
                            <?php
                            printf(
                                '%d %s',
                                $total_rows,
                                $this->lang->line($total_rows > 1 ? 'LIST_RESULTS' : 'LIST_RESULT')
                            );
                            if ($nb_pages > 1) {
                                echo ' &mdash; ';
                                printf($this->lang->line('LIST_PAGE_X_OF_Y'), $cur_page, $nb_pages);
                            }
                            ?>
                        </span>
                    </li>
                <?php } ?>

                <li class="nav-item" style="margin-left:1em;">
                    <label for="per_page_select" class="navbar-text" style="margin-right:0.5em;">
                        <?php echo $this->lang->line('LIST_PER_PAGE'); ?>
                    </label>
                    <select id="per_page_select"
                            class="form-control form-control-sm"
                            onchange="window.location.href='<?php echo $base; ?>/per_page/' + this.value;">
                        <?php foreach ($per_page_options as $opt) { ?>
                            <option value="<?php echo (int) $opt; ?>"
                                <?php echo ((int) $opt === (int) $per_page) ? 'selected' : ''; ?>>
                                <?php echo (int) $opt; ?>
                            </option>
                        <?php } ?>
                    </select>
                </li>

                <li class="nav-item">
                    <?php echo $footer_line; ?>
                </li>
            </ul>
        </nav>
    </footer>

    </div>
</section>

<?php /* ---------------------------------------------------------------
     JS minimal pour les bulk actions (sélection + barre flottante)
   ---------------------------------------------------------------- */ ?>
<?php if ($has_bulk && !empty($datas)) { ?>
<script>
(function(){
    var selectAll = document.getElementById('bulkSelectAll');
    var rowChecks = document.querySelectorAll('.bulk-row-check');
    var bulkBar   = document.getElementById('bulkBar');
    var counter   = document.getElementById('bulkCount');

    function refresh(){
        var n = 0;
        rowChecks.forEach(function(c){ if (c.checked) n++; });
        counter.textContent = n;
        bulkBar.style.display = n > 0 ? '' : 'none';
        if (selectAll) {
            selectAll.checked = (n === rowChecks.length && n > 0);
            selectAll.indeterminate = (n > 0 && n < rowChecks.length);
        }
    }
    if (selectAll) {
        selectAll.addEventListener('change', function(){
            rowChecks.forEach(function(c){ c.checked = selectAll.checked; });
            refresh();
        });
    }
    rowChecks.forEach(function(c){ c.addEventListener('change', refresh); });
    refresh();
})();

function listBulkSubmit(form){
    var n = form.querySelectorAll('.bulk-row-check:checked').length;
    if (n === 0) {
        alert('<?php echo addslashes($this->lang->line('BULK_NOTHING_SELECTED')); ?>');
        return false;
    }
    return true;
}
</script>
<?php } ?>
