<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Vue du référent — deux modes selon $is_validation_open
 *
 * AVANT la session ($is_validation_open = false) → mode GESTION
 *   - liste des inscrits avec bouton "Retirer"
 *   - formulaire d'ajout d'une famille
 *
 * JOUR J ou APRÈS ($is_validation_open = true) → mode VALIDATION
 *   - cocher présents, saisir unités, commentaires, no-shows
 *
 * Variables :
 *   $work, $design, $msg, $via_token, $token, $is_validation_open
 *   $available_families (uniquement en mode gestion)
 *   $type_participant_values (uniquement en mode gestion)
 */

$postUrl = $via_token
    ? base_url('Admwork_controller/validate_by_token/' . $token)
    : base_url('Admwork_controller/validate_one/' . $work->id);

$days_to_go = floor((strtotime($work->date_travaux) - strtotime('today')) / 86400);
?>
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <!-- En-tête contextuel ------------------------------------------------>
        <div class="grid grid_12">
            <h1 class="subtitle greydark">
                <?php echo $is_validation_open
                    ? $this->lang->line('REF_VALIDATE_PAGE_TITLE')
                    : $this->lang->line('REF_PREVIEW_PAGE_TITLE'); ?>
                :
                <?php echo $this->render_object->RenderElement('titre', $work->titre); ?>
            </h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey">
                <i class="icon-calendar"></i>
                <?php echo $this->render_object->RenderElement('date_travaux', $work->date_travaux); ?>
                &nbsp;&nbsp;
                <?php if ($work->type_session == 1) { ?>
                    <i class="icon-clock-1"></i>
                    <?php echo $this->render_object->RenderElement('heure_deb_trav', $work->heure_deb_trav); ?>
                    –
                    <?php echo $this->render_object->RenderElement('heure_fin_trav', $work->heure_fin_trav); ?>
                    &nbsp;&nbsp;
                <?php } ?>
                <i class="icon-pin-outline"></i>
                <?php echo $this->render_object->RenderElement('ecole', $work->ecole); ?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big">
                <span class="<?php echo $design->color; ?> nicdark_radius"></span>
            </div>
            <div class="nicdark_space10"></div>

            <?php if (!$is_validation_open) { /* MODE GESTION ------------------------- */ ?>
                <div class="alert alert-info">
                    <i class="icon-info-outline"></i>
                    <b><?php echo $this->lang->line('REF_PREVIEW_INTRO'); ?></b>
                    <br/>
                    <?php
                    if ($days_to_go > 1) {
                        echo sprintf($this->lang->line('REF_PREVIEW_DAYS_LEFT'), (int) $days_to_go);
                    } elseif ($days_to_go == 1) {
                        echo $this->lang->line('REF_PREVIEW_TOMORROW');
                    } else {
                        echo $this->lang->line('REF_PREVIEW_TODAY');
                    }
                    ?>
                    <br/><br/>
                    <?php echo $this->lang->line('REF_MANAGE_HELP'); ?>
                </div>
            <?php } else { /* MODE VALIDATION ----------------------------------------- */ ?>
                <div class="alert alert-info">
                    <b><?php echo $this->lang->line('REF_VALIDATE_INTRO'); ?></b><br/>
                    <?php echo $this->lang->line('REF_VALIDATE_HELP'); ?>
                </div>
            <?php } ?>

            <?php /*if ($work->description) { ?>
                <p>
                    <?php echo $this->render_object->RenderElement('description', $work->description); ?>
                </p>
            <?php } */?>

            <p>
                <b><?php echo $this->lang->line('INFO_NB_UNIT'); ?></b>
                <?php echo $this->render_object->RenderElement('nb_units', $work->nb_units); ?>
                <?php echo $this->lang->line('INFO_UNIT'); ?>
                &nbsp;|&nbsp;
                <b><?php echo $this->lang->line('REF_PLACES_TAKEN'); ?></b>
                <?php echo $work->nb_inscrits; ?> / <?php echo $work->nb_inscrits_max; ?>
                <?php if ($work->places_restantes > 0 && !$is_validation_open) { ?>
                    <span class="text-success">
                        (<?php echo $work->places_restantes; ?>
                        <?php echo $this->lang->line('REF_PLACES_LEFT'); ?>)
                    </span>
                <?php } ?>
            </p>
        </div>

        <!-- Messages flash --------------------------------------------------->
        <div class="grid grid_12">
            <?php echo $msg; ?>
        </div>

        <!-- ============================================================== -->
        <!-- MODE GESTION : Liste des inscrits + ajout                      -->
        <!-- ============================================================== -->
        <?php if (!$is_validation_open) { ?>

            <!-- Liste des inscrits avec bouton "Retirer" -->
            <div class="grid grid_12">
                <h3 class="subtitle greydark">
                    <?php echo $this->lang->line('REF_REGISTERED_LIST'); ?>
                    (<?php echo count($work->registred); ?>)
                </h3>
                <div class="nicdark_space10"></div>

                <table class="nicdark_table extrabig <?php echo $design->color; ?> nicdark_radius">
                    <thead class="<?php echo $design->bordercolor; ?>">
                        <tr>
                            <th><h5 class="white">#</h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('nom'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('type_participant'); ?></h5></th>
                            <?php if ($work->type_session == 1) { ?>
                                <th><h5 class="white"><?php echo $this->lang->line('horaires'); ?></h5></th>
                            <?php } ?>
                            <th><h5 class="white"><?php echo $this->lang->line('ACTION'); ?></h5></th>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">
                    <?php
                    if ($work->registred) {
                        $i = 0;
                        foreach ($work->registred as $unit) {
                            $i++;
                    ?>
                        <tr>
                            <td class="center"><?php echo $i; ?></td>
                            <td>
                                <b><?php echo isset($unit->family) ? $unit->family->nom : '—'; ?></b>
                            </td>
                            <td>
                                <?php echo $this->render_object->RenderElement('type_participant', $unit->type_participant, null, 'Infos_model'); ?>
                                <?php if ($unit->nb_participants > 1) { ?>
                                    <span class="badge badge-info">
                                        ×<?php echo $unit->nb_participants; ?>
                                    </span>
                                <?php } ?>
                            </td>
                            <?php if ($work->type_session == 1) { ?>
                                <td>
                                    <?php
                                    echo $this->render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_prevue, null, 'Infos_model');
                                    echo ' → ';
                                    echo $this->render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_prevue, null, 'Infos_model');
                                    ?>
                                </td>
                            <?php } ?>
                            <td>
                                <?php
                                echo form_open(
                                    $postUrl,
                                    ['class' => 'd-inline', 'onsubmit' => 'return confirm("' . $this->lang->line('REF_REMOVE_CONFIRM') . '");'],
                                    ['action' => 'remove', 'id_info' => $unit->id]
                                );
                                ?>
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="icon-trash"></i>
                                        <?php echo $this->lang->line('REF_REMOVE_BTN'); ?>
                                    </button>
                                <?php echo form_close(); ?>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        $colspan = ($work->type_session == 1) ? 5 : 4;
                        echo '<tr><td colspan="' . $colspan . '" class="center">'
                            . $this->lang->line('REGISTRED_NONE') . '</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <!-- Formulaire d'ajout d'une famille -->
            <?php if ($work->places_restantes > 0 && !empty($available_families)) { ?>
                <div class="grid grid_12">
                    <div class="nicdark_space20"></div>
                    <div class="card">
                        <div class="card-header <?php echo $design->color; ?>">
                            <h4 class="white"><?php echo $this->lang->line('REF_ADD_FAMILY'); ?></h4>
                        </div>
                        <div class="card-body">
                            <?php
                            echo form_open(
                                $postUrl,
                                ['class' => '', 'id' => 'ref_add_form'],
                                ['action' => 'add', 'type_session' => $work->type_session]
                            );
                            ?>
                            <div class="form-row">
                                <div class="form-group col-md-5">
                                    <label for="id_famille">
                                        <b><?php echo $this->lang->line('id_famille'); ?> *</b>
                                    </label>
                                    <select name="id_famille" id="id_famille" class="form-control" required>
                                        <option value="">— <?php echo $this->lang->line('SELECT_FAMILY'); ?> —</option>
                                        <?php foreach ($available_families as $fam) { ?>
                                            <option value="<?php echo $fam->id; ?>">
                                                <?php echo htmlspecialchars($fam->nom, ENT_QUOTES); ?>
                                                <?php if (!empty($fam->ecole)) { ?>
                                                    (<?php echo $fam->ecole; ?>)
                                                <?php } ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="type_participant">
                                        <b><?php echo $this->lang->line('type_participant'); ?> *</b>
                                    </label>
                                    <select name="type_participant" id="type_participant" class="form-control" required>
                                        <?php
                                        if (!empty($type_participant_values)) {
                                            foreach ($type_participant_values as $cle => $label) {
                                                echo '<option value="' . htmlspecialchars($cle, ENT_QUOTES) . '">'
                                                    . htmlspecialchars($label, ENT_QUOTES) . '</option>';
                                            }
                                        } else {
                                            echo '<option value="One">1 parent</option>';
                                            echo '<option value="Both">2 parents</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="icon-plus"></i>
                                        <?php echo $this->lang->line('REF_ADD_BTN'); ?>
                                    </button>
                                </div>
                            </div>
                            <?php echo form_close(); ?>
                            <p class="small text-muted">
                                * <?php echo $this->lang->line('REQUIRED_FIELDS'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } elseif ($work->places_restantes <= 0) { ?>
                <div class="grid grid_12">
                    <div class="nicdark_space20"></div>
                    <div class="alert alert-warning">
                        <i class="icon-attention"></i>
                        <?php echo $this->lang->line('REF_NO_PLACES_LEFT'); ?>
                    </div>
                </div>
            <?php } ?>

            <div class="grid grid_12">
                <div class="nicdark_space20"></div>
                <div class="alert alert-secondary small">
                    <i class="icon-clock-1"></i>
                    <?php echo $this->lang->line('REF_PREVIEW_FOOTER'); ?>
                </div>
            </div>

        <?php } else { /* ============= MODE VALIDATION ============================ */ ?>

            <div class="grid grid_12">
                <?php
                echo form_open(
                    $postUrl,
                    ['class' => '', 'id' => 'ref_validate'],
                    ['action' => 'validate', 'id_travaux' => $work->id]
                );
                ?>

                <table class="nicdark_table extrabig <?php echo $design->color; ?> nicdark_radius">
                    <thead class="<?php echo $design->bordercolor; ?>">
                        <tr>
                            <th><h5 class="white"><?php echo $this->lang->line('PRESENT'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('nom'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('type_participant'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('nb_unites_valides'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('REF_COMMENT'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('REF_NO_SHOW'); ?></h5></th>
                        </tr>
                    </thead>
                    <tbody class="nicdark_bg_grey nicdark_border_grey">
                    <?php
                    if ($work->registred) {
                        foreach ($work->registred as $unit) {
                            $already_valid  = ($unit->nb_unites_valides > 0);
                            $default_units  = $already_valid
                                ? $unit->nb_unites_valides
                                : ($work->nb_units * $unit->nb_participants);
                            $existing_com   = isset($unit->commentaire_ref) ? $unit->commentaire_ref : '';
                            $family_display = isset($unit->family) ? $unit->family->nom : '—';
                    ?>
                        <tr data-unit-id="<?php echo $unit->id; ?>">
                            <td class="center">
                                <input type="hidden" name="elements[]" value="<?php echo $unit->id; ?>">
                                <input type="checkbox"
                                       name="present_<?php echo $unit->id; ?>"
                                       value="1"
                                       class="presence-check"
                                       data-unit-id="<?php echo $unit->id; ?>"
                                       <?php echo $already_valid ? 'checked' : ''; ?>>
                            </td>
                            <td>
                                <p><b><?php echo $family_display; ?></b></p>
                                <?php if ($work->type_session == 1) { ?>
                                    <p class="small">
                                        <?php
                                        echo $this->render_object->RenderElement('heure_debut_prevue', $unit->heure_debut_prevue, null, 'Infos_model');
                                        echo ' → ';
                                        echo $this->render_object->RenderElement('heure_fin_prevue', $unit->heure_fin_prevue, null, 'Infos_model');
                                        ?>
                                    </p>
                                <?php } ?>
                            </td>
                            <td>
                                <?php echo $this->render_object->RenderElement('type_participant', $unit->type_participant, null, 'Infos_model'); ?>
                            </td>
                            <td>
                                <input type="number"
                                       step="0.5"
                                       min="0"
                                       max="<?php echo $work->nb_units * 2; ?>"
                                       name="nb_unites_<?php echo $unit->id; ?>"
                                       value="<?php echo $default_units; ?>"
                                       class="form-control form-control-sm unit-input"
                                       data-unit-id="<?php echo $unit->id; ?>"
                                       style="width: 90px;"
                                       <?php echo $already_valid ? '' : 'disabled'; ?>>
                            </td>
                            <td>
                                <input type="text"
                                       name="commentaire_<?php echo $unit->id; ?>"
                                       value="<?php echo htmlspecialchars($existing_com, ENT_QUOTES); ?>"
                                       placeholder="<?php echo $this->lang->line('REF_COMMENT_PLACEHOLDER'); ?>"
                                       class="form-control form-control-sm"
                                       maxlength="500">
                            </td>
                            <td class="center">
                                <label class="small">
                                    <input type="checkbox"
                                           name="unregister[]"
                                           value="<?php echo $unit->id; ?>"
                                           class="unregister-check"
                                           data-unit-id="<?php echo $unit->id; ?>">
                                    <?php echo $this->lang->line('REF_NO_SHOW_LABEL'); ?>
                                </label>
                            </td>
                        </tr>
                    <?php
                        }
                    } else {
                        echo '<tr><td colspan="6" class="center">'
                            . $this->lang->line('REGISTRED_NONE') . '</td></tr>';
                    }
                    ?>
                    </tbody>
                </table>

                <?php if ($work->registred) { ?>
                    <div class="nicdark_space20"></div>
                    <div class="form-group">
                        <label for="commentaire_global">
                            <b><?php echo $this->lang->line('REF_COMMENT_GLOBAL'); ?></b>
                        </label>
                        <textarea name="commentaire_global"
                                  id="commentaire_global"
                                  class="form-control"
                                  rows="2"
                                  maxlength="500"
                                  placeholder="<?php echo $this->lang->line('REF_COMMENT_GLOBAL_PLACEHOLDER'); ?>"></textarea>
                    </div>
                    <div class="nicdark_space20"></div>
                    <div class="right">
                        <button type="submit"
                                class="btn btn-success btn-lg"
                                onclick="return confirm('<?php echo $this->lang->line('REF_VALIDATE_CONFIRM'); ?>');">
                            <i class="icon-ok"></i>
                            <?php echo $this->lang->line('REF_VALIDATE_SUBMIT'); ?>
                        </button>
                    </div>
                <?php } ?>

                <?php echo form_close(); ?>
            </div>

        <?php } /* fin mode validation */ ?>
    </div>
</section>

<?php if ($is_validation_open) { ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.presence-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var id    = this.getAttribute('data-unit-id');
            var input = document.querySelector('.unit-input[data-unit-id="' + id + '"]');
            if (input) {
                input.disabled = !this.checked;
                if (!this.checked) input.value = 0;
            }
        });
    });
    document.querySelectorAll('.unregister-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var id      = this.getAttribute('data-unit-id');
            var row     = document.querySelector('tr[data-unit-id="' + id + '"]');
            var presCb  = document.querySelector('.presence-check[data-unit-id="' + id + '"]');
            var unitInp = document.querySelector('.unit-input[data-unit-id="' + id + '"]');

            if (this.checked) {
                if (presCb)  { presCb.checked = false; presCb.disabled = true; }
                if (unitInp) { unitInp.disabled = true; unitInp.value = 0; }
                if (row)     row.style.textDecoration = 'line-through';
            } else {
                if (presCb)  presCb.disabled = false;
                if (row)     row.style.textDecoration = 'none';
            }
        });
    });
});
</script>
<?php } ?>
