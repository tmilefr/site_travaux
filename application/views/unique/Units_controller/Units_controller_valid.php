<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Units_controller/valid — Refonte (vue moderne)
 *
 * Données disponibles (injectées par Units_controller::valid()) :
 *   $units->sessions  : tableau [ref => [unit, unit, ...]]  (ref = id_travaux ou "s{type_session}")
 *   $units->works     : tableau [ref => stdClass(titre,referent_travaux,type_session,date_travaux)]
 *   $units->familys   : familles présentes (pour filtres)
 *   $units->dates     : dates distinctes (pour filtres)
 *
 * Ce template :
 *   - Affiche une carte par session, avec ses inscrits dans un tableau dense
 *   - Propose des filtres rapides (recherche, date, famille, type) côté client
 *   - Permet le "tout cocher" par session ET globalement
 *   - Met en avant un résumé (sessions, lignes sélectionnées) dans une barre sticky
 *   - Soumet vers Units_controller/valids (inchangé)
 *
 * Aucun champ POST n'a été renommé : la cible (valids) reçoit toujours
 * "elements[]" et ouvre la vue Units_controller_valids pour la saisie effective
 * des heures et nombres d'unités.
 */

// Petits raccourcis ------------------------------------------------------
$ctrl   = $this->render_object->_getCi('_controller_name');
$action = $this->render_object->_getCi('_action');

/**
 * Retourne la traduction pour $key si elle existe, sinon $default.
 * Évite les ternaires PHP imbriqués non parenthésés (PHP 8+).
 */
$L = function ($key, $default = '') {
    $line = $this->lang->line($key);
    return ($line !== false && $line !== '') ? $line : $default;
};

// Comptage global pour le résumé de tête
$total_sessions = is_array($units->sessions) ? count($units->sessions) : 0;
$total_units    = 0;
if ($total_sessions) {
    foreach ($units->sessions as $list) {
        $total_units += count($list);
    }
}

// Helper local pour libellé du type de session (1 = Horaire, autres = Action)
$type_label = function ($type_session) use ($L) {
    return ($type_session == 1)
        ? $L('type_session_horaire', 'Horaire')
        : $L('type_session_action',  'Action');
};
?>

<!--start section-->
<section class="nicdark_section uv-section">
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space30"></div>

        <div class="grid grid_12">

            <!-- ===== En-tête ===== -->
            <h1 class="subtitle greydark">
                <?php echo $L($ctrl.'_'.$action); ?>
            </h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo $L($ctrl.'_'.$action.'_subtitle'); ?>
            </h3>
            <div class="nicdark_space10"></div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_violet nicdark_radius"></span>
            </div>
            <div class="nicdark_space20"></div>

            <?php if ($total_sessions === 0) { ?>

                <!-- ===== État vide ===== -->
                <div class="uv-empty">
                    <i class="icon-ok-circled"></i>
                    <h3><?php echo $L('UV_EMPTY_TITLE', 'Aucune unité en attente de validation'); ?></h3>
                    <p><?php echo $L('UV_EMPTY_HELP', 'Toutes les sessions ont été traitées. Revenez après la prochaine session.'); ?></p>
                </div>

            <?php } else { ?>

            <!-- ===== Barre de filtres (sticky) ===== -->
            <div class="uv-toolbar" id="uv-toolbar">
                <div class="uv-toolbar-row">

                    <!-- Recherche libre -->
                    <div class="uv-tool uv-tool-search">
                        <i class="icon-search"></i>
                        <input type="search"
                               id="uv-search"
                               class="uv-input"
                               placeholder="<?php echo $L('UV_SEARCH_PLACEHOLDER', 'Rechercher (titre, famille, référent...)'); ?>"
                               autocomplete="off">
                    </div>

                    <!-- Filtre date -->
                    <div class="uv-tool">
                        <select id="uv-filter-date" class="uv-input">
                            <option value="*"><?php echo $L('UV_ALL_DATES', 'Toutes les dates'); ?></option>
                            <?php
                            $dates = $units->dates;
                            if (is_array($dates)) {
                                arsort($dates);
                                foreach ($dates as $d) {
                                    $label = $this->render_object->RenderElement('date_travaux', $d, null, 'Admwork_model');
                                    echo '<option value="'.htmlspecialchars($d, ENT_QUOTES).'">'.$label.'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Filtre famille -->
                    <div class="uv-tool">
                        <select id="uv-filter-famille" class="uv-input">
                            <option value="*"><?php echo $L('UV_ALL_FAMILYS', 'Toutes les familles'); ?></option>
                            <?php
                            if (is_array($units->familys)) {
                                $fams = $units->familys;
                                asort($fams);
                                foreach ($fams as $id_fam => $label) {
                                    echo '<option value="'.(int)$id_fam.'">'.htmlspecialchars($label, ENT_QUOTES).'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Filtre type de session -->
                    <div class="uv-tool">
                        <select id="uv-filter-type" class="uv-input">
                            <option value="*"><?php echo $L('UV_ALL_TYPES', 'Tous les types'); ?></option>
                            <option value="1"><?php echo $type_label(1); ?></option>
                            <option value="2"><?php echo $type_label(2); ?></option>
                        </select>
                    </div>

                    <!-- Reset filtres -->
                    <div class="uv-tool">
                        <button type="button" id="uv-reset" class="uv-btn-ghost"
                                title="<?php echo $L('UV_RESET', 'Réinitialiser les filtres'); ?>">
                            <i class="icon-cancel"></i>
                            <span><?php echo $L('UV_RESET', 'Réinitialiser'); ?></span>
                        </button>
                    </div>

                </div>

                <!-- Compteurs -->
                <div class="uv-toolbar-summary">
                    <span class="uv-chip">
                        <b id="uv-count-sessions"><?php echo $total_sessions; ?></b>
                        <?php echo $L('UV_LBL_SESSIONS', 'sessions'); ?>
                    </span>
                    <span class="uv-chip">
                        <b id="uv-count-units"><?php echo $total_units; ?></b>
                        <?php echo $L('UV_LBL_UNITS', 'unités à valider'); ?>
                    </span>
                    <span class="uv-chip uv-chip-accent">
                        <b id="uv-count-selected">0</b>
                        <?php echo $L('UV_LBL_SELECTED', 'sélectionnées'); ?>
                    </span>
                </div>
            </div>

            <!-- ===== Formulaire ===== -->
            <?php
                echo form_open(
                    base_url($ctrl.'/valids'),
                    ['class' => '', 'id' => 'uv-form'],
                    ['form_mod' => 'edit', 'id' => '']
                );
            ?>

            <!-- Liste des cartes session -->
            <?php
            /*
             * Le contrôleur (populate) regroupe les sessions de type 1 (Horaire)
             * par id_travaux mais agrège TOUTES les sessions de type "Action"
             * (type_session != 1) sous une même clé "s2"/"s3", ce qui fait
             * disparaître les titres des autres travaux. On ré-éclate ici
             * proprement par id_travaux pour avoir une carte par session.
             *
             * NB : à terme, le mieux serait de corriger Units_controller::populate()
             * pour grouper systématiquement par $unit->id_travaux. Cette logique
             * locale reste compatible avec les deux versions du contrôleur.
             */
            $cards = [];   // [id_travaux => ['work' => stdClass, 'units' => array]]
            foreach ($units->sessions as $session_key => $session_units) {
                foreach ($session_units as $u) {
                    $tid = $u->id_travaux;
                    if (!isset($cards[$tid])) {
                        $cards[$tid] = [
                            'work'  => (object) [
                                'titre'            => $u->titre,
                                'referent_travaux' => $u->referent_travaux,
                                'type_session'     => $u->type_session,
                                'date_travaux'     => $u->date_travaux,
                                'nb_units'         => isset($u->nb_units) ? $u->nb_units : null,
                            ],
                            'units' => [],
                        ];
                    }
                    $cards[$tid]['units'][] = $u;
                }
            }

            // Tri par date décroissante (les plus récentes en haut),
            // identique à l'ordre du contrôleur
            uasort($cards, function ($a, $b) {
                return strcmp($b['work']->date_travaux, $a['work']->date_travaux);
            });
            ?>

            <div class="uv-cards">
                <?php
                foreach ($cards as $id_travaux => $card) {
                    $work       = $card['work'];
                    $works      = $card['units'];
                    $is_horaire = ($work->type_session == 1);
                    $card_date  = htmlspecialchars($work->date_travaux, ENT_QUOTES);
                    $card_type  = (int) $work->type_session;
                    $card_count = count($works);

                    $title_render  = $this->render_object->RenderElement('titre',            $work->titre,            null, 'Admwork_model');
                    $referent_text = $this->render_object->RenderElement('referent_travaux', $work->referent_travaux, null, 'Admwork_model');
                    $type_text     = $type_label($work->type_session);

                    $haystack = mb_strtolower(strip_tags(
                        $work->titre.' '.$referent_text.' '.$type_text.' '.$work->date_travaux
                    ), 'UTF-8');

                    $nb_units_value = isset($work->nb_units) ? (float) $work->nb_units : '';
                ?>
                <article class="uv-card"
                         data-date="<?php echo $card_date; ?>"
                         data-type="<?php echo $card_type; ?>"
                         data-count="<?php echo $card_count; ?>"
                         data-search="<?php echo htmlspecialchars($haystack, ENT_QUOTES); ?>">

                    <!-- En-tête de carte -->
                    <header class="uv-card-head">
                        <div class="uv-card-head-main">
                            <div class="uv-card-date">
                                <i class="icon-calendar"></i>
                                <?php echo $this->render_object->RenderElement('date_travaux', $work->date_travaux, null, 'Admwork_model'); ?>
                            </div>
                            <h4 class="uv-card-title">
                                <?php echo $title_render; ?>
                            </h4>
                            <div class="uv-card-meta">
                                <span class="uv-badge <?php echo $is_horaire ? 'uv-badge-blue' : 'uv-badge-violet'; ?>">
                                    <?php echo $type_text; ?>
                                </span>
                                <span class="uv-meta-item" title="<?php echo $L('referent_travaux'); ?>">
                                    <i class="icon-user-1"></i>
                                    <?php echo $referent_text; ?>
                                </span>
                                <span class="uv-meta-item" title="<?php echo $L('nb_units'); ?>">
                                    <i class="icon-hourglass"></i>
                                    <?php echo $L('nb_units', 'Unités'); ?> :
                                    <b><?php echo $nb_units_value; ?></b>
                                </span>
                                <span class="uv-meta-item">
                                    <i class="icon-users"></i>
                                    <span class="uv-card-count"><?php echo $card_count; ?></span>
                                    <?php echo $L('UV_LBL_REGISTERED', 'inscrits'); ?>
                                </span>
                            </div>
                        </div>

                        <div class="uv-card-head-actions">
                            <a class="uv-btn-link"
                               href="<?php echo base_url('Admwork_controller/register_one/'.$id_travaux); ?>"
                               title="<?php echo $L('UV_OPEN_SESSION', 'Ouvrir la session'); ?>">
                                <i class="icon-link-ext"></i>
                                <span><?php echo $L('UV_OPEN_SESSION', 'Ouvrir'); ?></span>
                            </a>
                            <label class="uv-checkall">
                                <input type="checkbox"
                                       class="uv-checkall-input"
                                       data-target="checkbox<?php echo $id_travaux; ?>">
                                <span><?php echo $L('UV_CHECK_ALL', 'Tout cocher'); ?></span>
                            </label>
                        </div>
                    </header>

                    <!-- Tableau des inscrits -->
                    <div class="uv-table-wrap">
                        <table class="uv-table">
                            <thead>
                                <tr>
                                    <th class="uv-col-check"></th>
                                    <th class="uv-col-fam">
                                        <?php echo $this->render_object->render_link('id_famille', 'valid', 'Infos_model'); ?>
                                    </th>
                                    <?php if ($is_horaire) { ?>
                                        <th class="uv-col-time">
                                            <?php echo $this->render_object->render_link('heure_debut_prevue', 'valid', 'Infos_model'); ?>
                                        </th>
                                        <th class="uv-col-time">
                                            <?php echo $this->render_object->render_link('heure_fin_prevue', 'valid', 'Infos_model'); ?>
                                        </th>
                                    <?php } ?>
                                    <th class="uv-col-units">
                                        <?php echo $L('nb_units', 'Unités'); ?>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($works as $unit) {
                                    $row_fam    = (int) $unit->id_famille;
                                    $fam_label  = isset($units->familys[$row_fam]) ? $units->familys[$row_fam] : '';
                                    $row_search = mb_strtolower(strip_tags($fam_label), 'UTF-8');
                                ?>
                                <tr class="uv-row"
                                    data-famille="<?php echo $row_fam; ?>"
                                    data-search="<?php echo htmlspecialchars($row_search, ENT_QUOTES); ?>">
                                    <td class="uv-col-check">
                                        <input type="checkbox"
                                               class="uv-row-check checkbox<?php echo $id_travaux; ?>"
                                               value="<?php echo (int) $unit->id; ?>"
                                               name="elements[]">
                                    </td>
                                    <td class="uv-col-fam">
                                        <span class="uv-fam-name">
                                            <?php echo $this->render_object->RenderElement('id_famille', $unit->id_famille, null, 'Infos_model'); ?>
                                        </span>
                                    </td>
                                    <?php if ($is_horaire) { ?>
                                        <td class="uv-col-time">
                                            <?php echo $this->render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_prevue, null, 'Infos_model'); ?>
                                        </td>
                                        <td class="uv-col-time">
                                            <?php echo $this->render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_prevue, null, 'Infos_model'); ?>
                                        </td>
                                    <?php } ?>
                                    <td class="uv-col-units">
                                        <?php echo $this->render_object->RenderElement('nb_units', $unit->nb_units, null, 'Admwork_model'); ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                </article>
                <?php } ?>
            </div>

            <!-- État vide pour le filtrage -->
            <div id="uv-no-result" class="uv-no-result" style="display:none;">
                <i class="icon-search"></i>
                <p><?php echo $L('UV_NO_RESULT', 'Aucune session ne correspond aux filtres en cours.'); ?></p>
            </div>

            <!-- Barre d'action sticky en bas -->
            <div class="uv-actionbar" id="uv-actionbar">
                <div class="uv-actionbar-info">
                    <i class="icon-ok"></i>
                    <span>
                        <b id="uv-actionbar-count">0</b>
                        <?php echo $L('UV_LBL_SELECTED_LONG', 'unité(s) sélectionnée(s)'); ?>
                    </span>
                </div>
                <div class="uv-actionbar-actions">
                    <button type="submit"
                            id="uv-submit"
                            class="btn btn-success uv-btn-submit"
                            disabled>
                        <i class="icon-right-circled"></i>
                        <?php echo $L('VALIDS_EDITION', 'Valider'); ?>
                    </button>
                </div>
            </div>

            <?php echo form_close(); ?>

            <?php } /* end if total_sessions */ ?>

            <div class="nicdark_space50"></div>
        </div>
    </div>
</section>
<!--end section-->
