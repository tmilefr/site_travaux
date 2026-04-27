<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Liste des sessions où l'utilisateur connecté est référent.
 * Variable : $my_works
 *
 * Le bouton change selon la date :
 *   - Session à venir → "Voir les inscrits" (mode aperçu)
 *   - Session du jour ou passée → "Valider les présences" (mode validation)
 */
?>
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
            <h1 class="subtitle greydark"><?php echo $this->lang->line('REF_MY_SESSIONS_TITLE'); ?></h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey"><?php echo $this->lang->line('REF_MY_SESSIONS_SUBTITLE'); ?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_green nicdark_radius"></span>
            </div>
            <div class="nicdark_space30"></div>
        </div>

        <?php if (empty($my_works)) { ?>
            <div class="grid grid_12">
                <div class="alert alert-info">
                    <?php echo $this->lang->line('REF_NO_SESSIONS'); ?>
                </div>
            </div>
        <?php } else {
            foreach ($my_works as $work) {
                $design    = $this->render_object->GetDesign($work->type);
                $is_open   = (strtotime($work->date_travaux) <= strtotime('today'));
                $days_diff = floor((strtotime($work->date_travaux) - strtotime('today')) / 86400);
        ?>
            <div class="grid grid_4">
                <div class="nicdark_archive1 nicdark_bg_grey nicdark_radius nicdark_shadow">
                    <a href="#" class="nicdark_btn nicdark_bg_greydark white medium nicdark_radius nicdark_absolute_left">
                        <?php echo $this->render_object->RenderElement('date_travaux', $work->date_travaux); ?>
                    </a>
                    <div class="nicdark_textevidence <?php echo $design->color; ?>">
                        <h4 class="white nicdark_margin20">
                            <?php echo $this->render_object->RenderElement('titre', $work->titre); ?>
                        </h4>
                    </div>
                    <div class="nicdark_margin20">
                        <h5>
                            <i class="icon-pin-outline"></i>
                            <?php echo $this->render_object->RenderElement('ecole', $work->ecole); ?>
                        </h5>
                        <div class="nicdark_space10"></div>

                        <?php if ($is_open) { ?>
                            <a href="<?php echo base_url('Admwork_controller/validate_one/' . $work->id); ?>"
                               class="nicdark_btn nicdark_bg_green white nicdark_radius medium">
                                <i class="icon-ok"></i>
                                <?php echo $this->lang->line('REF_VALIDATE_ACTION'); ?>
                            </a>
                        <?php } else { ?>
                            <a href="<?php echo base_url('Admwork_controller/validate_one/' . $work->id); ?>"
                               class="nicdark_btn nicdark_bg_blue white nicdark_radius medium">
                                <i class="icon-eye"></i>
                                <?php echo $this->lang->line('REF_PREVIEW_ACTION'); ?>
                            </a>
                            <p class="small">
                                <i class="icon-clock-1"></i>
                                <?php
                                if ($days_diff == 1) {
                                    echo $this->lang->line('REF_PREVIEW_TOMORROW');
                                } else {
                                    echo sprintf($this->lang->line('REF_PREVIEW_DAYS_LEFT'), (int) $days_diff);
                                }
                                ?>
                            </p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php
            }
        } ?>
    </div>
</section>
