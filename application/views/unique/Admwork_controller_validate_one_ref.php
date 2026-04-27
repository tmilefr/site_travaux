<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Vue du référent pour une session donnée.
 *
 * Deux modes selon $is_validation_open :
 *   - FALSE (avant la session) : mode APERÇU
 *       → liste des inscrits en lecture seule
 *       → rappel de la date, du lieu, des horaires
 *       → pas de bouton de validation
 *   - TRUE  (jour de la session ou après) : mode VALIDATION
 *       → cocher présents / saisir unités / commentaires / no-shows
 *       → bouton "Enregistrer les présences"
 *
 * Accès possible via :
 *   a) lien email tokenisé  → $via_token = true,  $token = 'abc...'
 *   b) menu 'Mes sessions'  → $via_token = false, utilisateur fam connecté
 *
 * Variables :
 *   - $work                : session avec ->registred (liste Infos + family)
 *   - $design              : rendu couleur
 *   - $msg                 : messages flash
 *   - $via_token           : bool
 *   - $token               : string|null
 *   - $is_validation_open  : bool (jour J ou après = true)
 */

$postUrl = $via_token
    ? base_url('Admwork_controller/validate_by_token/' . $token)
    : base_url('Admwork_controller/validate_one/' . $work->id);

$days_to_go = floor((strtotime($work->date_travaux) - strtotime('today')) / 86400);
?>
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <!-- En-tête contextuel -->
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

            <?php if (!$is_validation_open) { /* ---------- MODE APERÇU ---------- */ ?>
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
                    <?php echo $this->lang->line('REF_PREVIEW_HELP'); ?>
                </div>
            <?php } else { /* ---------- MODE VALIDATION ---------- */ ?>
                <div class="alert alert-info">
                    <b><?php echo $this->lang->line('REF_VALIDATE_INTRO'); ?></b><br/>
                    <?php echo $this->lang->line('REF_VALIDATE_HELP'); ?>
                </div>
            <?php } ?>

            <?php if ($work->description) { ?>
                <p>
                    <?php echo $this->render_object->RenderElement('description', $work->description); ?>
                </p>
            <?php } ?>

            <p>
                <b><?php echo $this->lang->line('INFO_NB_UNIT'); ?></b>
                <?php echo $this->render_object->RenderElement('nb_units', $work->nb_units); ?>
                <?php echo $this->lang->line('INFO_UNIT'); ?>
            </p>
        </div>

        <!-- Formulaire / liste des inscrits -->
        <div class="grid grid_12">
            <?php echo $msg; ?>

            <?php
            if ($is_validation_open) {
                echo form_open(
                    $postUrl,
                    ['class' => '', 'id' => 'ref_validate'],
                    ['form_mod' => 'validate', 'id_travaux' => $work->id]
                );
            }
            ?>

            <table class="nicdark_table extrabig <?php echo $design->color; ?> nicdark_radius">
                <thead class="<?php echo $design->bordercolor; ?>">
                    <tr>
                        <?php if ($is_validation_open) { ?>
                            <th><h5 class="white"><?php echo $this->lang->line('PRESENT'); ?></h5></th>
                        <?php } else { ?>
                            <th><h5 class="white">#</h5></th>
                        <?php } ?>
                        <th><h5 class="white"><?php echo $this->lang->line('nom'); ?></h5></th>
                        <th><h5 class="white"><?php echo $this->lang->line('type_participant'); ?></h5></th>
                        <?php if ($is_validation_open) { ?>
                            <th><h5 class="white"><?php echo $this->lang->line('nb_unites_valides'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('REF_COMMENT'); ?></h5></th>
                            <th><h5 class="white"><?php echo $this->lang->line('REF_NO_SHOW'); ?></h5></th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody class="nicdark_bg_grey nicdark_border_grey">
                <?php
                if ($work->registred) {
                    $i = 0;
                    foreach ($work->registred as $unit) {
                        $i++;
                        $already_valid  = ($unit->nb_unites_valides > 0);
                        $default_units  = $already_valid
                            ? $unit->nb_unites_valides
                            : ($work->nb_units * $unit->nb_participants);
                        $existing_com   = isset($unit->commentaire_ref) ? $unit->commentaire_ref : '';
                        $family_display = isset($unit->family) ? $unit->family->nom : '—';
                ?>
                    <tr data-unit-id="<?php echo $unit->id; ?>">
                        <?php if ($is_validation_open) { ?>
                            <td class="center">
                                <input type="hidden" name="elements[]" value="<?php echo $unit->id; ?>">
                                <input type="checkbox"
                                       name="present_<?php echo $unit->id; ?>"
                                       value="1"
                                       class="presence-check"
                                       data-unit-id="<?php echo $unit->id; ?>"
                                       <?php echo $already_valid ? 'checked' : ''; ?>>
                            </td>
                        <?php } else { ?>
                            <td class="center"><?php echo $i; ?></td>
                        <?php } ?>

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

                        <?php if ($is_validation_open) { ?>
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
                        <?php } ?>
                    </tr>
                <?php
                    }
                } else {
                    $colspan = $is_validation_open ? 6 : 3;
                    echo '<tr><td colspan="' . $colspan . '">'
                        . $this->lang->line('REGISTRED_NONE') . '</td></tr>';
                }
                ?>
                </tbody>
            </table>

            <?php if ($is_validation_open && $work->registred) { ?>
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

            <?php if ($is_validation_open) echo form_close(); ?>

            <?php if (!$is_validation_open && $work->registred) { ?>
                <div class="nicdark_space20"></div>
                <div class="alert alert-secondary small">
                    <i class="icon-clock-1"></i>
                    <?php echo $this->lang->line('REF_PREVIEW_FOOTER'); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<?php if ($is_validation_open) { ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Présent coché <-> champ nb unités actif
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

    // Désinscription = décoche présence + désactive unités + barre la ligne
    document.querySelectorAll('.unregister-check').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var id       = this.getAttribute('data-unit-id');
            var row      = document.querySelector('tr[data-unit-id="' + id + '"]');
            var presCb   = document.querySelector('.presence-check[data-unit-id="' + id + '"]');
            var unitInp  = document.querySelector('.unit-input[data-unit-id="' + id + '"]');

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
