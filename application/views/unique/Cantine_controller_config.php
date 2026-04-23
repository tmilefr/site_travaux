<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <div class="grid grid_12">
            <h1 class="subtitle greydark"><?php echo $this->lang->line('cantine_config_title');?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey"><?php echo $this->lang->line('cantine_config_subtitle');?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_blue nicdark_radius"></span></div>
            <div class="nicdark_space20"></div>

            <a href="<?php echo base_url('Cantine_controller/register');?>"
               class="nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey">
                &laquo; <?php echo $this->lang->line('cantine_back_to_agenda');?>
            </a>
            <div class="nicdark_space20"></div>
        </div>

        <!-- Sélecteur d'école -->
        <div class="grid grid_12">
            <h4 class="greydark"><?php echo $this->lang->line('cantine_school');?> :</h4>
            <div class="nicdark_space10"></div>
            <?php foreach(['B' => 'Mulhouse + Lutterbach', 'M' => 'Mulhouse', 'L' => 'Lutterbach'] AS $code => $label){
                $active = ($ecole === $code);
            ?>
                <a href="<?php echo base_url('Cantine_controller/config?ecole='.$code);?>"
                   class="nicdark_btn small nicdark_shadow nicdark_radius <?php echo $active ? 'nicdark_bg_blue white' : 'nicdark_bg_grey grey';?>">
                    <?php echo $label;?>
                </a>
            <?php } ?>
            <div class="nicdark_space30"></div>
        </div>

        <!-- Formulaire de configuration -->
        <?php echo form_open('Cantine_controller/save_config', ['class' => 'nicdark_form']);?>
        <input type="hidden" name="ecole" value="<?php echo html_escape($ecole);?>" />

        <?php
        $days_labels = [
            1 => $this->lang->line('cantine_day_1'),
            2 => $this->lang->line('cantine_day_2'),
            3 => $this->lang->line('cantine_day_3'),
            4 => $this->lang->line('cantine_day_4'),
            5 => $this->lang->line('cantine_day_5'),
        ];
        foreach($days_labels AS $id_day => $label){
            $cfg = $config[$id_day];
            $is_active = ((int)$cfg->active === 1);
            $card_color = $is_active ? 'nicdark_bg_green' : 'nicdark_bg_grey2';
            $txt_color  = $is_active ? 'white' : 'greydark';
        ?>
        <div class="grid grid_2 nicdark_relative">
            <div class="nicdark_archive1 <?php echo $card_color;?> nicdark_radius nicdark_shadow">
                <div class="nicdark_margin20">
                    <h3 class="<?php echo $txt_color;?>"><?php echo $label;?></h3>
                    <div class="nicdark_space20"></div>

                    <label class="<?php echo $txt_color;?>">
                        <input type="checkbox" name="active_<?php echo $id_day;?>" value="1"
                               <?php echo $is_active ? 'checked' : '';?> />
                        <?php echo $this->lang->line('cantine_day_needed');?>
                    </label>

                    <div class="nicdark_space20"></div>
                    <label class="<?php echo $txt_color;?>">
                        <?php echo $this->lang->line('cantine_nb_parents');?> :
                    </label>
                    <div class="nicdark_space10"></div>
                    <input type="number" min="0" max="20" step="1"
                           name="nb_slots_<?php echo $id_day;?>"
                           value="<?php echo (int)$cfg->nb_slots;?>"
                           class="nicdark_input"
                           style="width:80px; padding:8px; border-radius:4px; border:0; text-align:center;" />
                    <div class="nicdark_space20"></div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="grid grid_12">
            <div class="nicdark_space30"></div>
            <button type="submit" class="nicdark_press nicdark_btn nicdark_bg_green white nicdark_radius nicdark_shadow medium">
                <i class="icon-ok"></i> <?php echo $this->lang->line('cantine_save_config');?>
            </button>
            <div class="nicdark_space30"></div>
            <p class="grey"><small><?php echo $this->lang->line('cantine_config_hint');?></small></p>
        </div>

        <?php echo form_close();?>

    </div>
</section>
