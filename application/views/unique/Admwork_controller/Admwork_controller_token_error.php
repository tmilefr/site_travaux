<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Vue affichée si le token de validation référent est invalide ou expiré.
 * Variables : $error (string)
 */
?>
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space50"></div>

        <div class="grid grid_12">
            <div class="alert alert-danger">
                <h3><i class="icon-attention"></i> <?php echo $this->lang->line('REF_TOKEN_ERROR_TITLE'); ?></h3>
                <p><?php echo isset($error) ? $error : $this->lang->line('REF_TOKEN_INVALID'); ?></p>
                <p class="small">
                    <?php echo $this->lang->line('REF_TOKEN_ERROR_HELP'); ?>
                </p>
            </div>

            <div class="nicdark_space30"></div>

            <a href="<?php echo base_url('Home/login'); ?>"
               class="nicdark_btn nicdark_bg_blue white nicdark_radius medium">
                <i class="icon-login"></i>
                <?php echo $this->lang->line('LOGIN_TO_CONTINUE'); ?>
            </a>
        </div>
    </div>
</section>
