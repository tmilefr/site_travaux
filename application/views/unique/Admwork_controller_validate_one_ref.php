<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Vue de validation des présences par le RÉFÉRENT.
 *
 * Accès possible via :
 *   a) lien email tokenisé  -> $via_token = true,  $token = 'abc...'
 *   b) menu 'Mes sessions'  -> $via_token = false, utilisateur fam connecté
 *
 * Variables :
 *   - $work       : session avec ->registred (liste Infos + family)
 *   - $design     : rendu couleur
 *   - $msg        : messages flash
 *   - $via_token  : bool
 *   - $token      : string|null
 */

// URL de soumission adaptée au mode d'accès
$postUrl = $via_token
    ? base_url('Admwork_controller/validate_by_token/' . $token)
    : base_url('Admwork_controller/validate_one/' . $work->id);
?>
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <!-- Bandeau contexte session -->
        <div class="grid grid_12">
            <h1 class="subtitle greydark">
                <?php echo $this->lang->line('REF_VALIDATE_PAGE_TITLE'); ?> :
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

            <div class="alert alert-info">
                <b><?php echo $this->lang->line('REF_VALIDATE_INTRO'); ?></b><br/>
                <?php echo $this->lang->line('REF_VALIDATE_HELP'); ?>
            </div>

            <p>
                <b><?php echo $this->lang->line('INFO_NB_UNIT'); ?></b>
                <?php echo $this->render_object->RenderElement('nb_units', $work->nb_units); ?>
                <?php echo $this->lang->line('INFO_UNIT'); ?>
            </p>
        </div>

        <!-- Formulaire -->
        <div class="grid grid_12">
            <?php echo $msg; ?>

            <?php
            echo form_open(
                $postUrl,
                ['class' => '', 'id' => 'ref_validate'],
                ['form_mod' => 'validate', 'id_travaux' => $work->id]
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
                    echo '<tr><td colspan="6">' . $this->lang->line('REGISTRED_NONE') . '</td></tr>';
                }
                ?>
                </tbody>
            </table>

            <?php if ($work->registred) { ?>
                <div class="nicdark_space20"></div>

                <!-- Commentaire global optionnel -->
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
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1) Présent coché <-> champ nb unités actif
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

    // 2) Désinscription = décoche présence + désactive unités et barre la ligne
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
