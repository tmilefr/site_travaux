<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!--start section-->
<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <!-- Titre + sous-titre -->
        <div class="grid grid_9">
            <h1 class="subtitle greydark"><?php echo $this->lang->line('cantine_title');?></h1>
            <div class="nicdark_space20"></div>
            <h3 class="subtitle grey"><?php echo $this->lang->line('cantine_subtitle');?></h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
            <div class="nicdark_space10"></div>
        </div>

        <!-- Sélecteur d'école -->
        <div class="grid grid_3">
            <h4 class="greydark"><?php echo $this->lang->line('cantine_school');?> :</h4>
            <div class="nicdark_space10"></div>
            <?php foreach(['M' => 'Mulhouse', 'L' => 'Lutterbach'] AS $code => $label){
                $active = ($ecole === $code);
            ?>
                <a href="<?php echo base_url('Cantine_controller/register?ecole='.$code);?>"
                   class="nicdark_btn small nicdark_shadow nicdark_radius <?php echo $active ? 'nicdark_bg_blue white' : 'nicdark_bg_grey grey';?>">
                    <?php echo $label;?>
                </a>
            <?php } ?>
            <div class="nicdark_space30"></div>
        </div>

        <!-- Barre navigation semaine -->
        <div class="grid grid_12">
            <div class="nicdark_space20"></div>
            <a href="<?php echo base_url('Cantine_controller/register/'.($week_offset - 1));?>"
               class="nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey">
                &laquo; <?php echo $this->lang->line('cantine_prev_week');?>
            </a>
            <a href="<?php echo base_url('Cantine_controller/register/0');?>"
               class="nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey">
                <?php echo $this->lang->line('cantine_this_week');?>
            </a>
            <a href="<?php echo base_url('Cantine_controller/register/'.($week_offset + 1));?>"
               class="nicdark_btn nicdark_bg_grey small nicdark_shadow nicdark_radius grey">
                <?php echo $this->lang->line('cantine_next_week');?> &raquo;
            </a>

            <?php if ($is_admin){ ?>
                <a href="<?php echo base_url('Cantine_controller/config?ecole='.$ecole);?>"
                   class="nicdark_btn nicdark_bg_blue small nicdark_shadow nicdark_radius white right">
                    <i class="icon-cog"></i> <?php echo $this->lang->line('cantine_config_link');?>
                </a>
            <?php } ?>
            <div class="nicdark_space20"></div>
            <h4 class="greydark"><?php echo $week_label;?></h4>
            <div class="nicdark_space20"></div>
        </div>

        <!-- Compteurs -->
        <div class="grid grid_4">
            <div class="nicdark_archive1 nicdark_bg_grey nicdark_radius nicdark_shadow nicdark_padding20 center">
                <h4 class="greydark"><?php echo $this->lang->line('cantine_stat_days');?></h4>
                <h1 class="greydark"><?php echo $stats->active_days;?></h1>
            </div>
        </div>
        <div class="grid grid_4">
            <div class="nicdark_archive1 nicdark_bg_green nicdark_radius nicdark_shadow nicdark_padding20 center">
                <h4 class="white"><?php echo $this->lang->line('cantine_stat_mine');?></h4>
                <h1 class="white"><?php echo $stats->mine;?></h1>
            </div>
        </div>
        <div class="grid grid_4">
            <div class="nicdark_archive1 nicdark_bg_orange nicdark_radius nicdark_shadow nicdark_padding20 center">
                <h4 class="white"><?php echo $this->lang->line('cantine_stat_open');?></h4>
                <h1 class="white"><?php echo $stats->open;?></h1>
            </div>
        </div>

        <div class="grid grid_12">
            <div class="nicdark_space30"></div>
        </div>

        <!-- Cartes des 5 jours -->
        <?php foreach($days AS $day){
            $has_session = ($day->session !== null);

            // Couleur de carte selon l'état
            if (!$has_session){
                $card_color = 'nicdark_bg_grey2';
                $btn_color  = 'nicdark_bg_greydark';
                $text_color = 'greydark';
            } elseif (!empty($day->my_validated)){
                $card_color = 'nicdark_bg_greendark';
                $btn_color  = 'nicdark_bg_greendark';
                $text_color = 'white';
            } elseif (!empty($day->mine)){
                $card_color = 'nicdark_bg_green';
                $btn_color  = 'nicdark_bg_greendark';
                $text_color = 'white';
            } elseif (!empty($day->full)){
                $card_color = 'nicdark_bg_red';
                $btn_color  = 'nicdark_bg_reddark';
                $text_color = 'white';
            } else {
                $card_color = 'nicdark_bg_blue';
                $btn_color  = 'nicdark_bg_bluedark';
                $text_color = 'white';
            }
        ?>
        <div class="grid grid_2">
            <div class="nicdark_archive1 <?php echo $card_color;?> nicdark_radius nicdark_shadow">

                <!-- Date en haut à gauche -->
                <a href="#" class="nicdark_btn nicdark_bg_greydark white small nicdark_radius nicdark_absolute_left">
                    <?php echo $day->day_num.' '.$day->month_fr;?>
                </a>

                <!-- Ratio inscrits/capacité en haut à droite -->
                <?php if ($has_session){ ?>
                <a href="#" class="nicdark_btn <?php echo $btn_color;?> white small nicdark_radius nicdark_absolute_right">
                    <?php echo $day->nb_inscrits.'/'.$day->nb_slots;?>
                </a>
                <?php } ?>

                <!-- Zone titre jour -->
                <div class="nicdark_textevidence nicdark_bg_greydark">
                    <h4 class="white nicdark_margin20"><?php echo $day->day_label;?></h4>
                </div>

                <!-- Corps -->
                <div class="nicdark_margin20">
                    <?php if (!$has_session){ ?>
                        <div class="nicdark_space20"></div>
                        <h5 class="<?php echo $text_color;?>">
                            <i class="icon-minus-circled"></i> <?php echo $this->lang->line('cantine_day_inactive');?>
                        </h5>
                        <div class="nicdark_space20"></div>
                    <?php } else { ?>

                        <div class="nicdark_space10"></div>

                        <!-- Horaires -->
                        <p class="<?php echo $text_color;?>" style="font-size:12px;">
                            <i class="icon-clock-1"></i>
                            <?php echo html_escape($day->session->heure_deb_trav);?>
                            →
                            <?php echo html_escape($day->session->heure_fin_trav);?>
                            &nbsp;·&nbsp;
                            <b><?php echo (float)$day->nb_units;?></b> u.
                        </p>

                        <div class="nicdark_space10"></div>
                        <h5 class="<?php echo $text_color;?>">
                            <i class="icon-users"></i> <?php echo $this->lang->line('cantine_registered');?>
                        </h5>
                        <div class="nicdark_space10"></div>

                        <!-- Liste des inscrits (slots) -->
                        <ul class="nicdark_ul">
                            <?php for($i = 0; $i < $day->nb_slots; $i++){
                                $ins = isset($day->inscrits[$i]) ? $day->inscrits[$i] : null;
                                if ($ins){
                                    $name = !empty($ins->nom) ? $ins->nom : $ins->login;
                                    $is_me = ((int)$ins->id_famille === (int)$id_fam);
                                    $validated = ((float)$ins->nb_unites_valides_effectif > 0);
                            ?>
                                <li class="<?php echo $text_color;?>">
                                    <i class="icon-user"></i>
                                    <?php echo html_escape($name);?>
                                    <?php if ($is_me){ echo ' <b>('.$this->lang->line('cantine_you').')</b>'; } ?>
                                    <?php if ($validated){ echo ' <i class="icon-ok" title="'.$this->lang->line('cantine_validated').'"></i>'; } ?>
                                </li>
                            <?php } else { ?>
                                <li class="<?php echo $text_color;?>" style="opacity:0.7;">
                                    <i class="icon-plus-circled"></i>
                                    <i><?php echo $this->lang->line('cantine_slot_free');?></i>
                                </li>
                            <?php }
                            } ?>
                        </ul>

                        <div class="nicdark_space20"></div>

                        <!-- Boutons d'action -->
                        <?php if ($can_register && !$day->passed){ ?>
                            <?php if (!empty($day->my_validated)){ ?>
                                <span class="nicdark_btn nicdark_bg_greendark white nicdark_radius small">
                                    <i class="icon-ok"></i> <?php echo $this->lang->line('cantine_validated');?>
                                </span>
                            <?php } elseif (!empty($day->mine)){ ?>
                                <a href="<?php echo base_url('Cantine_controller/unregister_one/'.$day->session->id);?>"
                                   onclick="return confirm('<?php echo $this->lang->line('cantine_confirm_cancel');?>');"
                                   class="nicdark_press nicdark_btn nicdark_bg_red white nicdark_radius nicdark_shadow small">
                                    <i class="icon-cancel"></i> <?php echo $this->lang->line('cantine_btn_cancel');?>
                                </a>
                            <?php } elseif (!empty($day->full)){ ?>
                                <span class="nicdark_btn nicdark_bg_greydark white nicdark_radius small">
                                    <?php echo $this->lang->line('cantine_full');?>
                                </span>
                            <?php } else { ?>
                                <a href="<?php echo base_url('Cantine_controller/register_one/'.$day->session->id);?>"
                                   class="nicdark_press nicdark_btn nicdark_bg_green white nicdark_radius nicdark_shadow small">
                                    <i class="icon-ok"></i> <?php echo $this->lang->line('cantine_btn_register');?>
                                </a>
                            <?php } ?>
                        <?php } elseif ($day->passed){ ?>
                            <span class="nicdark_btn nicdark_bg_greydark white nicdark_radius small">
                                <?php echo $this->lang->line('cantine_passed');?>
                            </span>
                        <?php } ?>

                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="grid grid_12">
            <div class="nicdark_space30"></div>
            <p class="grey"><small><?php echo $this->lang->line('cantine_register_hint');?></small></p>
        </div>

    </div>
</section>
<!--end section-->
