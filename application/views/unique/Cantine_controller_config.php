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

            <span class="nicdark_btn nicdark_bg_green small nicdark_radius white right">
                <?php echo $nb_upcoming;?> <?php echo $this->lang->line('cantine_nb_upcoming');?>
            </span>
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

        <!-- ============================================================ -->
        <!-- ÉTAPE 1 : Configuration des jours                              -->
        <!-- ============================================================ -->
        <div class="grid grid_12">
            <h2 class="greydark">
                <span class="nicdark_btn nicdark_bg_blue white small nicdark_radius">1</span>
                <?php echo $this->lang->line('cantine_step1_title');?>
            </h2>
            <div class="nicdark_space10"></div>
            <p class="grey"><?php echo $this->lang->line('cantine_step1_hint');?></p>
            <div class="nicdark_space20"></div>
        </div>

        <?php echo form_open('Cantine_controller/save_config');?>
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
                           style="width:100%; padding:8px; border-radius:4px; border:0; text-align:center;" />

                    <div class="nicdark_space20"></div>

                    <label class="<?php echo $txt_color;?>">
                        <?php echo $this->lang->line('cantine_nb_units');?> :
                    </label>
                    <div class="nicdark_space10"></div>
                    <input type="number" min="0" max="10" step="0.25"
                           name="nb_units_<?php echo $id_day;?>"
                           value="<?php echo (float)$cfg->nb_units;?>"
                           style="width:100%; padding:8px; border-radius:4px; border:0; text-align:center;" />

                    <div class="nicdark_space20"></div>

                    <label class="<?php echo $txt_color;?>">
                        <?php echo $this->lang->line('cantine_hours');?> :
                    </label>
                    <div class="nicdark_space10"></div>
                    <div style="display:flex; gap:6px;">
                        <input type="time" name="heure_deb_<?php echo $id_day;?>"
                               value="<?php echo html_escape(!empty($cfg->heure_deb) ? $cfg->heure_deb : '11:45');?>"
                               style="flex:1; padding:6px; border-radius:4px; border:0; text-align:center;" />
                        <input type="time" name="heure_fin_<?php echo $id_day;?>"
                               value="<?php echo html_escape(!empty($cfg->heure_fin) ? $cfg->heure_fin : '13:30');?>"
                               style="flex:1; padding:6px; border-radius:4px; border:0; text-align:center;" />
                    </div>

                    <div class="nicdark_space20"></div>

                    <label class="<?php echo $txt_color;?>">
                        <?php echo $this->lang->line('cantine_referent');?> :
                    </label>
                    <div class="nicdark_space10"></div>
                    <select name="id_referent_<?php echo $id_day;?>"
                            style="width:100%; padding:6px; border-radius:4px; border:0;">
                        <option value="">-- <?php echo $this->lang->line('cantine_referent_none');?> --</option>
                        <?php foreach($referents AS $rid => $rtitle){ ?>
                            <option value="<?php echo html_escape($rid);?>"
                                    <?php echo ((string)$cfg->id_referent === (string)$rid) ? 'selected' : '';?>>
                                <?php echo html_escape($rtitle);?>
                            </option>
                        <?php } ?>
                    </select>

                    <div class="nicdark_space20"></div>
                </div>
            </div>
        </div>
        <?php } ?>

        <div class="grid grid_12">
            <div class="nicdark_space20"></div>
            <button type="submit" class="nicdark_press nicdark_btn nicdark_bg_green white nicdark_radius nicdark_shadow medium">
                <i class="icon-ok"></i> <?php echo $this->lang->line('cantine_save_config');?>
            </button>
            <div class="nicdark_space30"></div>
        </div>

        <?php echo form_close();?>

        <!-- ============================================================ -->
        <!-- ÉTAPE 2 : Génération des sessions                              -->
        <!-- ============================================================ -->
        <div class="grid grid_12">
            <div class="nicdark_divider left big"><span class="nicdark_bg_orange nicdark_radius"></span></div>
            <div class="nicdark_space20"></div>
            <h2 class="greydark">
                <span class="nicdark_btn nicdark_bg_orange white small nicdark_radius">2</span>
                <?php echo $this->lang->line('cantine_step2_title');?>
            </h2>
            <div class="nicdark_space10"></div>
            <p class="grey"><?php echo $this->lang->line('cantine_step2_hint');?></p>
            <div class="nicdark_space20"></div>
        </div>

        <div class="grid grid_12">
            <?php echo form_open('Cantine_controller/generate');?>
            <input type="hidden" name="ecole" value="<?php echo html_escape($ecole);?>" />

            <div class="nicdark_archive1 nicdark_bg_grey nicdark_radius nicdark_shadow">
                <div class="nicdark_margin20">

                    <!-- Choix du mode -->
                    <div class="grid grid_12">
                        <label class="greydark" style="display:block; margin-bottom:10px;">
                            <input type="radio" name="period_mode" value="school_end" checked
                                   onclick="cantineTogglePeriod(false);" />
                            <b><?php echo $this->lang->line('cantine_period_school_end');?></b>
                            <span class="grey">
                                (<?php echo date('d/m/Y', strtotime($default_date_deb));?>
                                &nbsp;→&nbsp;
                                <?php echo date('d/m/Y', strtotime($default_date_fin));?>)
                            </span>
                        </label>
                        <label class="greydark" style="display:block;">
                            <input type="radio" name="period_mode" value="custom"
                                   onclick="cantineTogglePeriod(true);" />
                            <b><?php echo $this->lang->line('cantine_period_custom');?></b>
                        </label>
                    </div>

                    <div class="nicdark_space20"></div>

                    <!-- Dates personnalisées (masquées par défaut) -->
                    <div id="cantine_custom_dates" style="display:none;">
                        <div class="grid grid_4">
                            <label class="greydark">
                                <b><?php echo $this->lang->line('cantine_date_deb');?></b>
                            </label>
                            <div class="nicdark_space10"></div>
                            <input type="date" name="date_deb"
                                   value="<?php echo html_escape($default_date_deb);?>"
                                   style="width:100%; padding:10px; border-radius:4px; border:1px solid #ddd;" />
                        </div>

                        <div class="grid grid_4">
                            <label class="greydark">
                                <b><?php echo $this->lang->line('cantine_date_fin');?></b>
                            </label>
                            <div class="nicdark_space10"></div>
                            <input type="date" name="date_fin"
                                   value="<?php echo html_escape($default_date_fin);?>"
                                   style="width:100%; padding:10px; border-radius:4px; border:1px solid #ddd;" />
                        </div>

                        <div class="grid grid_4">&nbsp;</div>
                    </div>

                    <!-- Bouton de génération -->
                    <div class="grid grid_12">
                        <div class="nicdark_space10"></div>
                        <button type="submit"
                                class="nicdark_press nicdark_btn nicdark_bg_orange white nicdark_radius nicdark_shadow medium"
                                onclick="return confirm('<?php echo $this->lang->line('cantine_confirm_generate');?>');">
                            <i class="icon-calendar"></i>
                            <?php echo $this->lang->line('cantine_btn_generate');?>
                        </button>
                    </div>
                </div>
            </div>
            <?php echo form_close();?>
        </div>

        <script>
            function cantineTogglePeriod(show){
                var el = document.getElementById('cantine_custom_dates');
                if (el) el.style.display = show ? 'block' : 'none';
            }
        </script>

        <!-- Historique des générations -->
        <?php if (!empty($generations)){ ?>
        <div class="grid grid_12">
            <div class="nicdark_space30"></div>
            <h4 class="greydark"><?php echo $this->lang->line('cantine_gen_history');?></h4>
            <div class="nicdark_space10"></div>

            <table class="nicdark_table nicdark_bg_grey nicdark_radius">
                <thead class="nicdark_bg_greydark">
                    <tr>
                        <td><h5 class="white"><?php echo $this->lang->line('cantine_gen_date');?></h5></td>
                        <td><h5 class="white"><?php echo $this->lang->line('cantine_gen_period');?></h5></td>
                        <td class="center"><h5 class="white"><?php echo $this->lang->line('cantine_gen_created');?></h5></td>
                        <td class="center"><h5 class="white"><?php echo $this->lang->line('cantine_gen_skipped');?></h5></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($generations AS $gen){ ?>
                    <tr>
                        <td><p><?php echo date('d/m/Y H:i', strtotime($gen->created));?></p></td>
                        <td><p>
                            <?php echo date('d/m/Y', strtotime($gen->date_deb));?>
                            &nbsp;→&nbsp;
                            <?php echo date('d/m/Y', strtotime($gen->date_fin));?>
                        </p></td>
                        <td class="center"><p><b class="green"><?php echo (int)$gen->nb_created;?></b></p></td>
                        <td class="center"><p class="grey"><?php echo (int)$gen->nb_skipped;?></p></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>

        <div class="grid grid_12">
            <div class="nicdark_space30"></div>
            <p class="grey"><small><?php echo $this->lang->line('cantine_config_hint');?></small></p>
        </div>

    </div>
</section>
