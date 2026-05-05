<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vue d'édition des droits ACL d'un rôle.
 * --------------------------------------
 * Variables fournies par Acl_roles_controller::set_rules() :
 *   $id     : id du rôle en cours d'édition
 *   $ctrls  : liste plate (legacy) des contrôleurs avec leurs actions
 *   $zones  : map de zones, prête à afficher
 *             [zone_key => stdClass {key, label, icon, color, ctrls,
 *                                    total_acts, total_allow}]
 *
 * Ergonomie :
 *   - Une carte (collapse) par zone fonctionnelle (sys, config, orga, ...)
 *   - Compteurs "actifs / total" par zone et par contrôleur
 *   - Bouton "tout cocher / décocher la zone"
 *   - Bouton "tout cocher / décocher le contrôleur"
 *   - Champ de recherche live qui masque les contrôleurs/actions
 *     ne correspondant pas (titre + nom d'action)
 *   - Footer sticky avec compteur global + Valider
 *
 * La logique POST (rules[] = 'id_ctrl_id_act') est inchangée.
 */

// Hauteur du sticky footer (utilisée via CSS) : voir set_rules.css
$total_acts_global  = 0;
$total_allow_global = 0;
foreach ($zones as $z) {
    $total_acts_global  += $z->total_acts;
    $total_allow_global += $z->total_allow;
}
?>
<!--start section-->
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
            <h1 class="subtitle greydark">
                <?php echo $title; ?>
            </h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo $this->lang->line('Acl_controllers_controller_subtitle'); ?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_red nicdark_radius"></span>
            </div>
            <div class="nicdark_space10"></div>
        </div>

        <?php
            echo form_open(
                'Acl_roles_controller/set_rules/' . $id,
                array('class' => 'acl-rules-form', 'id' => 'edit'),
                array('form_mod' => 'roles', 'id' => $id)
            );
        ?>

        <!-- ============== Bandeau de contrôle global ============== -->
        <div class="card acl-rules-toolbar">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="acl-rules-toolbar__search mr-3 mb-2 mb-md-0">
                        <span class="oi oi-magnifying-glass" aria-hidden="true"></span>
                        <input type="text"
                               id="aclRulesSearch"
                               class="form-control form-control-sm"
                               placeholder="<?php echo $this->lang->line('ACL_RULES_SEARCH_PH') ?: 'Rechercher un contrôleur ou une action...'; ?>"
                               autocomplete="off">
                    </div>

                    <div class="acl-rules-toolbar__buttons mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-success js-acl-all-on">
                            <span class="oi oi-check" aria-hidden="true"></span>
                            <?php echo $this->lang->line('ACL_RULES_ALL_ON') ?: 'Tout cocher'; ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary js-acl-all-off">
                            <span class="oi oi-x" aria-hidden="true"></span>
                            <?php echo $this->lang->line('ACL_RULES_ALL_OFF') ?: 'Tout décocher'; ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info js-acl-expand">
                            <span class="oi oi-fullscreen-enter" aria-hidden="true"></span>
                            <?php echo $this->lang->line('ACL_RULES_EXPAND') ?: 'Tout déplier'; ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info js-acl-collapse">
                            <span class="oi oi-fullscreen-exit" aria-hidden="true"></span>
                            <?php echo $this->lang->line('ACL_RULES_COLLAPSE') ?: 'Tout replier'; ?>
                        </button>
                    </div>

                    <div class="ml-md-auto acl-rules-toolbar__counter">
                        <span class="badge badge-pill badge-light">
                            <span class="js-acl-count-active"><?php echo (int) $total_allow_global; ?></span>
                            /
                            <span class="js-acl-count-total"><?php echo (int) $total_acts_global; ?></span>
                            <?php echo $this->lang->line('ACL_RULES_RIGHTS_LBL') ?: 'droits'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============== Une carte (collapse) par zone ============== -->
        <?php
        $zone_idx = 0;
        foreach ($zones as $zkey => $zone):
            $zone_idx++;
            $collapse_id = 'aclZone_' . ($zkey);
            $zone_label  = $this->lang->line($zone->label) ?: $zkey;
            $zone_color  = ($zone->color);
            $zone_icon   = ($zone->icon);
        ?>
        <div class="card acl-zone-card"
             data-zone-key="<?php echo $zkey; ?>"
             data-zone-total="<?php echo (int) $zone->total_acts; ?>">

            <!-- En-tête zone : titre + bouton tout cocher zone + collapse -->
            <div class="card-header acl-zone-header <?php echo $zone_color; ?>">
                <div class="d-flex align-items-center flex-wrap">
                    <button class="btn btn-link acl-zone-toggle p-0 mr-2"
                            type="button"
                            data-toggle="collapse"
                            data-target="#<?php echo $collapse_id; ?>"
                            aria-expanded="true"
                            aria-controls="<?php echo $collapse_id; ?>">
                        <span class="oi <?php echo $zone_icon; ?>" aria-hidden="true"></span>
                        <strong><?php echo $zone_label; ?></strong>
                        <span class="oi oi-chevron-bottom acl-zone-chevron ml-1" aria-hidden="true"></span>
                    </button>

                    <span class="badge badge-light acl-zone-badge ml-2">
                        <span class="js-zone-active"><?php echo (int) $zone->total_allow; ?></span>
                        /
                        <span class="js-zone-total"><?php echo (int) $zone->total_acts; ?></span>
                    </span>

                    <span class="badge badge-secondary ml-2">
                        <?php echo count($zone->ctrls); ?>
                        <?php echo $this->lang->line('ACL_KPI_CTRL_SHORT') ?: 'ctrl'; ?>
                    </span>

                    <div class="ml-auto acl-zone-actions">
                        <!-- Master switch zone : coche / décoche TOUTES les actions de la zone -->
                        <div class="custom-control custom-switch d-inline-block mr-3">
                            <input type="checkbox"
                                   class="custom-control-input js-zone-toggle"
                                   id="zoneToggle_<?php echo $zone_idx; ?>"
                                   <?php echo ($zone->total_allow === $zone->total_acts && $zone->total_acts > 0) ? 'checked="checked"' : ''; ?>>
                            <label class="custom-control-label text-white"
                                   for="zoneToggle_<?php echo $zone_idx; ?>">
                                <?php echo $this->lang->line('ACL_RULES_SELECT_ALL_ZONE') ?: 'Tout cocher la zone'; ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Corps zone : table des contrôleurs (collapsable) -->
            <div class="collapse show" id="<?php echo $collapse_id; ?>">
                <div class="card-body p-0">
                    <table class="table table-striped table-sm acl-rules-table mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="acl-col-master">&nbsp;</th>
                                <th scope="col" class="acl-col-name">
                                    <?php echo $this->lang->line('controller'); ?>
                                </th>
                                <th scope="col">
                                    <?php echo $this->lang->line('actions'); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($zone->ctrls as $ctrl):
                            $ctrl_total = is_array($ctrl->actions) ? count($ctrl->actions) : 0;
                            $ctrl_allow = 0;
                            if ($ctrl_total) {
                                foreach ($ctrl->actions as $a) {
                                    if ($a->allow) { $ctrl_allow++; }
                                }
                            }
                            $ctrl_id_safe = (int) $ctrl->id;
                        ?>
                            <tr class="acl-rules-row"
                                data-ctrl-name="<?php echo strtolower($ctrl->controller); ?>"
                                data-ctrl-total="<?php echo (int) $ctrl_total; ?>">
                                <!-- Master switch contrôleur -->
                                <td class="acl-col-master">
                                    <?php if ($ctrl_total > 0): ?>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input js-ctrl-toggle"
                                               id="ctrlToggle_<?php echo $ctrl_id_safe; ?>"
                                               <?php echo ($ctrl_allow === $ctrl_total && $ctrl_total > 0) ? 'checked="checked"' : ''; ?>>
                                        <label class="custom-control-label sr-only"
                                               for="ctrlToggle_<?php echo $ctrl_id_safe; ?>">
                                            <?php echo $this->lang->line('ACL_RULES_SELECT_ALL_CTRL') ?: 'Tout cocher'; ?>
                                        </label>
                                    </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Nom du contrôleur + compteur -->
                                <td class="acl-col-name">
                                    <span class="acl-ctrl-name"><?php echo $ctrl->controller; ?></span>
                                    <?php if ($ctrl_total > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1">
                                        <span class="js-ctrl-active"><?php echo (int) $ctrl_allow; ?></span>
                                        /
                                        <span class="js-ctrl-total"><?php echo (int) $ctrl_total; ?></span>
                                    </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions du contrôleur -->
                                <td>
                                    <?php if ($ctrl_total === 0): ?>
                                        <em class="text-muted">
                                            <?php echo $this->lang->line('NO_ACTION_DEFINED') ?: 'Aucune action définie.'; ?>
                                        </em>
                                    <?php else: ?>
                                    <div class="acl-actions-list">
                                        <?php foreach ($ctrl->actions as $action):
                                            $sw_id = 'customSwitch' . (int) $ctrl->id . '_' . (int) $action->id;
                                            $sw_val = (int) $ctrl->id . '_' . (int) $action->id;
                                        ?>
                                        <div class="acl-action-item"
                                             data-action-name="<?php echo strtolower($action->action); ?>">
                                            <div class="custom-control custom-switch form-check-inline">
                                                <input type="checkbox"
                                                       <?php echo ($action->allow) ? 'checked="checked"' : ''; ?>
                                                       class="custom-control-input js-action-cb"
                                                       name="rules[]"
                                                       id="<?php echo $sw_id; ?>"
                                                       value="<?php echo $sw_val; ?>">
                                                <label class="custom-control-label" for="<?php echo $sw_id; ?>">
                                                    <?php echo $action->action; ?>
                                                </label>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- ============== Footer sticky : compteur + Valider ============== -->
        <div class="acl-rules-footer">
            <div class="acl-rules-footer__inner">
                <div class="acl-rules-footer__count">
                    <span class="oi oi-shield" aria-hidden="true"></span>
                    <span class="js-acl-count-active"><?php echo (int) $total_allow_global; ?></span>
                    /
                    <span class="js-acl-count-total"><?php echo (int) $total_acts_global; ?></span>
                    <?php echo $this->lang->line('ACL_RULES_RIGHTS_ACTIVE') ?: 'droits actifs'; ?>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="oi oi-check" aria-hidden="true"></span>
                    <?php echo $this->lang->line('VALIDER'); ?>
                </button>
            </div>
        </div>

        <?php echo form_close(); ?>
    </div>
</section>
<!--end section-->

<link rel="stylesheet" href="<?php echo base_url('assets/css/set_rules.css'); ?>">
<script src="<?php echo base_url('assets/js/set_rules.js'); ?>" defer></script>
