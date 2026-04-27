<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/* GENERIC LIST VIEW
 * -----------------
 * Variables disponibles (héritées de MY_Controller::list()) :
 *   - $datas              : lignes à afficher
 *   - $_model_name        : nom du modèle courant
 *   - $total_rows         : nombre total de résultats (avant pagination)
 *   - $per_page           : nombre courant de lignes par page
 *   - $per_page_options   : tableau des choix possibles (15, 30, 50, 100)
 *   - $cur_page           : numéro de page courante
 *   - $active_filters     : tableau associatif champ => valeur des filtres posés
 *   - $global_search      : chaîne de recherche globale courante
 *   - $footer_line        : ligne de footer (compatibilité existante)
 */

$controller_name = $this->render_object->_getCi('_controller_name');
$action          = $this->render_object->_getCi('_action');
$base            = base_url($controller_name . '/' . $action);

// Y a-t-il quelque chose qui réduit la liste ?
$has_filters = (is_array($active_filters) && count($active_filters) > 0)
            || (isset($global_search) && $global_search !== '');

// Calcul page x sur n
$nb_pages = ($per_page > 0) ? (int) ceil($total_rows / $per_page) : 1;
if ($nb_pages < 1) { $nb_pages = 1; }
?>
<!--start section-->
<section class="nicdark_section ">
    <!--start nicdark_container-->
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
         BANDEAU FILTRES ACTIFS
         Affiché uniquement si au moins un filtre ou une recherche globale
         est en cours. Chaque badge contient une croix qui supprime ce
         filtre précis ; un bouton "Tout réinitialiser" appelle l'action
         clear_filters du contrôleur.
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
                           class="text-white" style="margin-left:0.4em;"
                           title="<?php echo $this->lang->line('LIST_REMOVE_FILTER'); ?>">
                            <span class="oi oi-circle-x"></span>
                        </a>
                    </span>
                <?php } ?>

                <?php
                if (is_array($active_filters)) {
                    $defs = $this->{$_model_name}->_get('defs');
                    foreach ($active_filters as $field => $value) {
                        // Libellé champ
                        $field_label = $this->lang->line($field);
                        if (!$field_label) { $field_label = $field; }

                        // Libellé valeur (via les "values" du champ si dispo)
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
                               class="text-white" style="margin-left:0.4em;"
                               title="<?php echo $this->lang->line('LIST_REMOVE_FILTER'); ?>">
                                <span class="oi oi-circle-x"></span>
                            </a>
                        </span>
                        <?php
                    }
                }
                ?>

                <a href="<?php echo base_url($controller_name . '/clear_filters'); ?>"
                   class="btn btn-warning btn-sm" style="margin-left:auto;">
                    <span class="oi oi-trash"></span>
                    <?php echo $this->lang->line('LIST_RESET_ALL'); ?>
                </a>
            </div>
        </div>
    <?php } ?>

    <?php /* ---------------------------------------------------------------
         TABLEAU OU ÉTAT VIDE
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
                    <a href="<?php echo base_url($controller_name . '/clear_filters'); ?>"
                       class="btn btn-warning">
                        <span class="oi oi-trash"></span>
                        <?php echo $this->lang->line('LIST_RESET_ALL'); ?>
                    </a>
                <?php } ?>
            </div>
        </div>

    <?php } else { ?>

        <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th scope="col">&nbsp;</th>
                <?php
                foreach($this->{$_model_name}->_get('defs') AS $field=>$defs){
                    if ($defs->list === true){
                        echo '<th scope="col">'.$this->render_object->render_link($field).'</th>';
                    }
                }
                ?>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($datas AS $key => $data){
            echo '<tr>';
            echo '<td>';
                echo $this->render_object->render_element_menu($data, ((isset($data->blocked)) ?$data->blocked:null));
            echo '</td>';

            foreach($this->{$_model_name}->_get('defs') AS $field=>$defs){
                if ($defs->list === true){
                    echo '<td>'.$this->render_object->RenderElement($field, $data->{$field}, $data->{$this->{$_model_name}->_get('key')}).'</td>';
                }
            }
            echo '</tr>';
        }
        ?>
        </tbody>
        </table>

    <?php } ?>

    <?php /* ---------------------------------------------------------------
         FOOTER : pagination + compteur + sélecteur per_page
       ---------------------------------------------------------------- */ ?>
    <footer class="footer mt-auto py-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <ul class="navbar-nav mr-auto" style="align-items:center;">
                <li class="nav-item">
                    <?php echo ((isset($this->pagination)) ? $this->pagination->create_links():''); ?>
                </li>

                <?php if ($total_rows > 0) { ?>
                    <li class="nav-item" style="margin-left:1em;">
                        <span class="navbar-text">
                            <?php
                            // « 32 résultats — page 2 sur 4 »
                            printf(
                                '%d %s',
                                $total_rows,
                                $this->lang->line($total_rows > 1 ? 'LIST_RESULTS' : 'LIST_RESULT')
                            );
                            if ($nb_pages > 1) {
                                echo ' &mdash; ';
                                printf(
                                    $this->lang->line('LIST_PAGE_X_OF_Y'),
                                    $cur_page,
                                    $nb_pages
                                );
                            }
                            ?>
                        </span>
                    </li>
                <?php } ?>

                <li class="nav-item" style="margin-left:1em;">
                    <form method="get" action="" class="form-inline" id="perPageForm">
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
                    </form>
                </li>

                <li class="nav-item">
                    <?php echo $footer_line; ?>
                </li>
            </ul>
            <span class="navbar-text"></span>
        </nav>
    </footer>

    <?php //echo $this->_render_debug(); ?>
    </div>
</section>
