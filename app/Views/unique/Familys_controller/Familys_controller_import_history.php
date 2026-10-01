<?php

/**
 * Familys_controller_import_history.php
 *
 * Liste des imports CSV ABCM précédents (table family_import).
 * Affiche le nom du fichier, la civil_year ciblée, et les compteurs
 * d'actions (créations / mises à jour / marquages / réactivations / erreurs).
 */
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

        <?php if ($this->session->flashdata('import_success')): ?>
            <div class="grid grid_12">
                <div class="alert alert-success">
                    <i class="oi oi-check"></i>
                    <?php echo htmlspecialchars($this->session->flashdata('import_success'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('import_error')): ?>
            <div class="grid grid_12">
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($this->session->flashdata('import_error'), ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid_12">
            <a href="<?php echo base_url($this->render_object->_getCi('_controller_name').'/import'); ?>" class="btn btn-primary">
                <i class="oi oi-data-transfer-upload"></i>
                <?php echo $this->lang->line('IMPORT_NEW_BTN'); ?>
            </a>
            <div class="nicdark_space20"></div>
        </div>

        <div class="grid grid_12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $this->lang->line('IMPORT_HISTORY_TITLE'); ?></h5>

                    <?php if (empty($imports)): ?>
                        <p class="text-muted"><?php echo $this->lang->line('IMPORT_HISTORY_EMPTY'); ?></p>
                    <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo $this->lang->line('created'); ?></th>
                                <th><?php echo $this->lang->line('filename'); ?></th>
                                <th><?php echo $this->lang->line('civil_year'); ?></th>
                                <th class="text-center"><?php echo $this->lang->line('nb_lines'); ?></th>
                                <th class="text-center"><?php echo $this->lang->line('nb_families'); ?></th>
                                <th class="text-center"><span class="badge badge-success"><?php echo $this->lang->line('nb_created'); ?></span></th>
                                <th class="text-center"><span class="badge badge-primary"><?php echo $this->lang->line('nb_updated'); ?></span></th>
                                <th class="text-center"><span class="badge badge-info"><?php echo $this->lang->line('nb_reactivated'); ?></span></th>
                                <th class="text-center"><span class="badge badge-warning"><?php echo $this->lang->line('nb_marked'); ?></span></th>
                                <th class="text-center"><span class="badge badge-danger"><?php echo $this->lang->line('nb_errors'); ?></span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($imports AS $imp): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($imp->created, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><code><?php echo htmlspecialchars($imp->filename, ENT_QUOTES, 'UTF-8'); ?></code></td>
                                <td><?php echo htmlspecialchars($imp->civil_year, ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_lines; ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_families; ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_created; ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_updated; ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_reactivated; ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_marked; ?></td>
                                <td class="text-center"><?php echo (int)$imp->nb_errors; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</section>
