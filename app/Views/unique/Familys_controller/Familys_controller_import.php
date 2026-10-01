<?php

/**
 * Familys_controller_import.php
 *
 * Vue à 2 modes :
 *  - mode UPLOAD : pas de preview, on affiche le formulaire d'upload
 *  - mode PREVIEW: un fichier vient d'être uploadé et parsé, on affiche
 *                  le diff (5 sections) + le bouton "Confirmer" qui
 *                  poste vers Familys_controller/import_apply.
 */

$has_preview = !empty($preview);
?>
<!--start section-->
<section class="nicdark_section ">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
            <h1 class="subtitle greydark">
                <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action'));?>
            </h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey">
                <?php echo $this->lang->line($this->render_object->_getCi('_controller_name').'_'.$this->render_object->_getCi('_action').'_subtitle');?>
            </h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_orange nicdark_radius"></span></div>
            <div class="nicdark_space20"></div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="grid grid_12">
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!$has_preview): /* ---------- MODE UPLOAD ---------- */ ?>

            <div class="grid grid_12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $this->lang->line('IMPORT_UPLOAD_TITLE'); ?></h5>
                        <p class="card-text"><?php echo $this->lang->line('IMPORT_UPLOAD_HELP'); ?></p>

                        <?php echo form_open_multipart(base_url($this->render_object->_getCi('_controller_name').'/import')); ?>
                            <div class="form-row">
                                <div class="form-group col-md-8">
                                    <label for="csv_file"><?php echo $this->lang->line('IMPORT_FILE_LABEL'); ?></label>
                                    <input type="file" name="csv_file" id="csv_file" class="form-control-file" accept=".csv,text/csv" required>
                                    <small class="form-text text-muted"><?php echo $this->lang->line('IMPORT_FILE_HELP'); ?></small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="oi oi-data-transfer-upload"></i>
                                <?php echo $this->lang->line('IMPORT_UPLOAD_BTN'); ?>
                            </button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

        <?php else: /* ---------- MODE PREVIEW ---------- */ ?>

            <?php
                $nb_create     = count($preview['to_create']);
                $nb_update     = count($preview['to_update']);
                $nb_mark       = count($preview['to_mark']);
                $nb_reactivate = count($preview['to_reactivate']);
                $nb_errors     = count($preview['errors']);
                $nb_total_actions = $nb_create + $nb_update + $nb_mark + $nb_reactivate;
            ?>

            <div class="grid grid_12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php echo $this->lang->line('IMPORT_PREVIEW_TITLE'); ?>
                            <small class="text-muted"> &mdash; <?php echo htmlspecialchars($orig_name, ENT_QUOTES, 'UTF-8'); ?></small>
                        </h5>
                        <p>
                            <?php echo sprintf($this->lang->line('IMPORT_PREVIEW_STATS'),
                                $parse_stats['nb_lines'], $parse_stats['nb_families']); ?>
                        </p>

                        <div class="row">
                            <div class="col-md-2 text-center">
                                <span class="badge badge-success" style="font-size:1.5em; padding:0.5em 1em;"><?php echo $nb_create; ?></span>
                                <div><small><?php echo $this->lang->line('IMPORT_TO_CREATE'); ?></small></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge badge-primary" style="font-size:1.5em; padding:0.5em 1em;"><?php echo $nb_update; ?></span>
                                <div><small><?php echo $this->lang->line('IMPORT_TO_UPDATE'); ?></small></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge badge-info" style="font-size:1.5em; padding:0.5em 1em;"><?php echo $nb_reactivate; ?></span>
                                <div><small><?php echo $this->lang->line('IMPORT_TO_REACTIVATE'); ?></small></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge badge-warning" style="font-size:1.5em; padding:0.5em 1em;"><?php echo $nb_mark; ?></span>
                                <div><small><?php echo $this->lang->line('IMPORT_TO_MARK'); ?></small></div>
                            </div>
                            <div class="col-md-2 text-center">
                                <span class="badge badge-danger" style="font-size:1.5em; padding:0.5em 1em;"><?php echo $nb_errors; ?></span>
                                <div><small><?php echo $this->lang->line('IMPORT_ERRORS'); ?></small></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nicdark_space20"></div>

            <!-- Accordéon des 5 sections -->
            <div class="grid grid_12">
                <div id="importAccordion">

                    <!-- À créer -->
                    <?php if ($nb_create > 0): ?>
                    <div class="card">
                        <div class="card-header" id="headingCreate">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseCreate">
                                    <span class="badge badge-success"><?php echo $nb_create; ?></span>
                                    <?php echo $this->lang->line('IMPORT_TO_CREATE_TITLE'); ?>
                                </button>
                            </h5>
                        </div>
                        <div id="collapseCreate" class="collapse" data-parent="#importAccordion">
                            <div class="card-body">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('code_famille_abcm') ?: 'Code famille ABCM'; ?></th>
                                            <th><?php echo $this->lang->line('nom') ?: 'Nom'; ?></th>
                                            <th><?php echo $this->lang->line('e_mail') ?: 'E-mail'; ?></th>
                                            <th><?php echo $this->lang->line('ville') ?: 'Ville'; ?></th>
                                            <th><?php echo $this->lang->line('ecole') ?: 'École'; ?></th>
                                            <th><?php echo $this->lang->line('nb_enfants') ?: 'Enfants'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($preview['to_create'] AS $entry):
                                        $f = $entry->csv;
                                    ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($f->code, ENT_QUOTES, 'UTF-8'); ?></code></td>
                                            <td><?php echo htmlspecialchars($f->nom.' '.$f->prenom, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($f->e_mail, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($f->ville, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($f->ecole, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo count($f->members); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- À mettre à jour -->
                    <?php if ($nb_update > 0): ?>
                    <div class="card">
                        <div class="card-header" id="headingUpdate">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseUpdate">
                                    <span class="badge badge-primary"><?php echo $nb_update; ?></span>
                                    <?php echo $this->lang->line('IMPORT_TO_UPDATE_TITLE'); ?>
                                </button>
                            </h5>
                        </div>
                        <div id="collapseUpdate" class="collapse" data-parent="#importAccordion">
                            <div class="card-body">
                            <?php foreach($preview['to_update'] AS $entry):
                                $f = $entry->csv;
                                $existing = $entry->existing;
                            ?>
                                <div class="border-bottom pb-2 mb-2">
                                    <strong><?php echo htmlspecialchars($f->nom.' '.$f->prenom, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <small class="text-muted">
                                        (id #<?php echo (int)$existing->id; ?>
                                        &mdash; <?php echo htmlspecialchars($f->e_mail, ENT_QUOTES, 'UTF-8'); ?>)
                                    </small>
                                    <?php if (!empty($entry->fields_diff)): ?>
                                        <ul class="mb-1 mt-1">
                                        <?php foreach($entry->fields_diff AS $field => $vv): ?>
                                            <li>
                                                <strong><?php $lbl = $this->lang->line($field); echo htmlspecialchars($lbl ?: $field, ENT_QUOTES, 'UTF-8'); ?></strong> :
                                                <span class="text-danger" style="text-decoration:line-through"><?php echo htmlspecialchars($vv['old'], ENT_QUOTES, 'UTF-8') ?: '<em>(vide)</em>'; ?></span>
                                                &rarr;
                                                <span class="text-success"><?php echo htmlspecialchars($vv['new'], ENT_QUOTES, 'UTF-8') ?: '<em>(vide)</em>'; ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <?php if (!empty($entry->members_diff['to_create'])): ?>
                                        <div><small class="text-success">
                                            <i class="oi oi-plus"></i>
                                            <?php echo sprintf($this->lang->line('IMPORT_X_NEW_CHILDREN'), count($entry->members_diff['to_create'])); ?> :
                                            <?php
                                                $names = [];
                                                foreach($entry->members_diff['to_create'] AS $cm){
                                                    $names[] = htmlspecialchars($cm->prenom.' '.$cm->nom, ENT_QUOTES, 'UTF-8');
                                                }
                                                echo implode(', ', $names);
                                            ?>
                                        </small></div>
                                    <?php endif; ?>
                                    <?php if (!empty($entry->members_diff['to_update'])): ?>
                                        <div><small class="text-primary">
                                            <i class="oi oi-pencil"></i>
                                            <?php echo sprintf($this->lang->line('IMPORT_X_UPDATED_CHILDREN'), count($entry->members_diff['to_update'])); ?>
                                        </small></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- À réactiver -->
                    <?php if ($nb_reactivate > 0): ?>
                    <div class="card">
                        <div class="card-header" id="headingReactivate">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseReactivate">
                                    <span class="badge badge-info"><?php echo $nb_reactivate; ?></span>
                                    <?php echo $this->lang->line('IMPORT_TO_REACTIVATE_TITLE'); ?>
                                </button>
                            </h5>
                        </div>
                        <div id="collapseReactivate" class="collapse" data-parent="#importAccordion">
                            <div class="card-body">
                                <p class="text-muted small"><?php echo $this->lang->line('IMPORT_TO_REACTIVATE_HELP'); ?></p>
                                <ul>
                                <?php foreach($preview['to_reactivate'] AS $entry): ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($entry->csv->nom.' '.$entry->csv->prenom, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small class="text-muted">
                                            (id #<?php echo (int)$entry->existing->id; ?>
                                            &mdash; <?php echo htmlspecialchars($entry->csv->e_mail, ENT_QUOTES, 'UTF-8'); ?>)
                                        </small>
                                    </li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- À marquer "à désactiver" -->
                    <?php if ($nb_mark > 0): ?>
                    <div class="card">
                        <div class="card-header" id="headingMark">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseMark">
                                    <span class="badge badge-warning"><?php echo $nb_mark; ?></span>
                                    <?php echo $this->lang->line('IMPORT_TO_MARK_TITLE'); ?>
                                </button>
                            </h5>
                        </div>
                        <div id="collapseMark" class="collapse" data-parent="#importAccordion">
                            <div class="card-body">
                                <p class="text-muted small"><?php echo $this->lang->line('IMPORT_TO_MARK_HELP'); ?></p>
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('code_famille_abcm') ?: 'Code famille ABCM'; ?></th>
                                            <th><?php echo $this->lang->line('nom') ?: 'Nom'; ?></th>
                                            <th><?php echo $this->lang->line('e_mail') ?: 'E-mail'; ?></th>
                                            <th><?php echo $this->lang->line('ecole') ?: 'École'; ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($preview['to_mark'] AS $entry):
                                        $f = $entry->existing;
                                    ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($f->code_famille_abcm, ENT_QUOTES, 'UTF-8'); ?></code></td>
                                            <td><?php echo htmlspecialchars($f->nom, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($f->e_mail, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($f->ecole, ENT_QUOTES, 'UTF-8'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Erreurs de parsing -->
                    <?php if ($nb_errors > 0): ?>
                    <div class="card">
                        <div class="card-header" id="headingErrors">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseErrors">
                                    <span class="badge badge-danger"><?php echo $nb_errors; ?></span>
                                    <?php echo $this->lang->line('IMPORT_ERRORS_TITLE'); ?>
                                </button>
                            </h5>
                        </div>
                        <div id="collapseErrors" class="collapse" data-parent="#importAccordion">
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead><tr><th>Ligne</th><th>Raison</th><th>Détails</th></tr></thead>
                                    <tbody>
                                    <?php foreach($preview['errors'] AS $err): ?>
                                        <tr>
                                            <td><?php echo (int)$err['line']; ?></td>
                                            <td><code><?php echo htmlspecialchars($err['reason'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                            <td>
                                                <?php
                                                    $extras = $err;
                                                    unset($extras['line'], $extras['reason']);
                                                    echo htmlspecialchars(json_encode($extras, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div><!-- /accordion -->
            </div>

            <div class="nicdark_space30"></div>

            <!-- Confirmation -->
            <div class="grid grid_12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($nb_total_actions > 0): ?>
                            <p><strong><?php echo sprintf($this->lang->line('IMPORT_CONFIRM_QUESTION'), $nb_total_actions); ?></strong></p>
                            <?php echo form_open(base_url($this->render_object->_getCi('_controller_name').'/import_apply')); ?>
                                <input type="hidden" name="stored_path" value="<?php echo htmlspecialchars($stored_path, ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn btn-primary"
                                        onclick="return confirm('<?php echo $this->lang->line('IMPORT_CONFIRM_JS'); ?>');">
                                    <i class="oi oi-check"></i>
                                    <?php echo $this->lang->line('IMPORT_CONFIRM_BTN'); ?>
                                </button>
                                <a href="<?php echo base_url($this->render_object->_getCi('_controller_name').'/import'); ?>" class="btn btn-secondary">
                                    <?php echo $this->lang->line('IMPORT_CANCEL_BTN'); ?>
                                </a>
                            <?php echo form_close(); ?>
                        <?php else: ?>
                            <p class="text-muted"><?php echo $this->lang->line('IMPORT_NOTHING_TO_DO'); ?></p>
                            <a href="<?php echo base_url($this->render_object->_getCi('_controller_name').'/import'); ?>" class="btn btn-secondary">
                                <?php echo $this->lang->line('IMPORT_BACK_BTN'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</section>
