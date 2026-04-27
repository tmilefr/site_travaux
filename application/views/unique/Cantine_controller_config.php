<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Vue admin : agenda mensuel + paramétrage cantine + génération.
 *
 * Variables disponibles :
 *  $config            : array[1..5] (CantineConfig_model::GetConfig)
 *  $ecole             : 'B' | 'M' | 'L'
 *  $civil_year        : ex. '2025-2026'
 *  $referents         : [id => label]
 *  $generations       : 5 dernières générations
 *  $nb_upcoming       : compteur sessions à venir
 *  $agenda_ym         : 'YYYY-MM' du mois affiché
 *  $agenda_prev_ym    : navigation
 *  $agenda_next_ym    : navigation
 *  $agenda_label      : libellé "Avril 2026"
 *  $agenda_sessions   : ['Y-m-d' => [ {id, heure_deb_trav, heure_fin_trav, nb_inscrits_max, nb_inscrits} ] ]
 *  $default_date_deb  : aujourd'hui
 *  $default_date_fin  : 31/05 année scolaire
 */

// Pré-calculs pour l'agenda
$_first   = $agenda_ym.'-01';
$_t_first = strtotime($_first);
$_days    = (int)date('t', $_t_first);                  // nb jours du mois
$_offset  = ((int)date('N', $_t_first) - 1);            // 0 = lundi, 6 = dimanche
$_today   = date('Y-m-d');
$_fr_days = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
?>

<style>
/* ============================================================ */
/* Styles spécifiques cantine - portée locale uniquement        */
/* ============================================================ */

/* Agenda */
.cantine-agenda-wrap { background:#fff; border-radius:6px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.cantine-agenda-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px; }
.cantine-agenda-head h3 { margin:0; color:#2c3e50; font-size:18px; text-transform:capitalize; }
.cantine-agenda-nav a { display:inline-block; padding:6px 12px; margin-left:6px; background:#eef1f4; color:#3a4a5c; border-radius:4px; text-decoration:none; font-size:13px; transition:all .15s; }
.cantine-agenda-nav a:hover { background:#3a4a5c; color:#fff; }
.cantine-agenda-nav a.today { background:#3498db; color:#fff; }
.cantine-agenda-nav a.today:hover { background:#2980b9; }

.cantine-agenda-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.cantine-agenda-grid .dow { text-align:center; font-size:11px; font-weight:700; color:#7a8a99; text-transform:uppercase; padding:6px 0; letter-spacing:.5px; }
.cantine-agenda-grid .cell { background:#f7f8fa; border-radius:4px; min-height:74px; padding:5px 6px; position:relative; border:1px solid transparent; }
.cantine-agenda-grid .cell.empty { background:transparent; }
.cantine-agenda-grid .cell.weekend { background:#f0f1f3; }
.cantine-agenda-grid .cell.today { border-color:#3498db; background:#fff; box-shadow:0 0 0 2px rgba(52,152,219,.15); }
.cantine-agenda-grid .cell .num { font-size:11px; font-weight:600; color:#7a8a99; display:block; margin-bottom:3px; }
.cantine-agenda-grid .cell.today .num { color:#3498db; }

.session-pill { display:block; font-size:11px; padding:3px 5px; border-radius:3px; margin-bottom:2px; line-height:1.3; cursor:default; border-left:3px solid; }
.session-pill.full     { background:#fdecea; border-color:#e74c3c; color:#922b21; }
.session-pill.partial  { background:#fff4e0; border-color:#f39c12; color:#7e5109; }
.session-pill.empty-sess{ background:#eaf4fb; border-color:#3498db; color:#1f618d; }
.session-pill .h    { font-weight:600; }
.session-pill .nb   { float:right; font-weight:600; }

.cantine-agenda-empty { text-align:center; padding:30px 10px; color:#7a8a99; font-style:italic; }
.cantine-agenda-legend { margin-top:12px; font-size:11px; color:#7a8a99; }
.cantine-agenda-legend span.lg { display:inline-block; width:10px; height:10px; border-radius:2px; vertical-align:middle; margin:0 4px 0 12px; }
.cantine-agenda-legend span.lg.full { background:#e74c3c; }
.cantine-agenda-legend span.lg.partial { background:#f39c12; }
.cantine-agenda-legend span.lg.empty-sess { background:#3498db; }

/* Bloc unifié règles + génération */
.cantine-admin-card { background:#fff; border-radius:6px; box-shadow:0 1px 3px rgba(0,0,0,.08); overflow:hidden; }
.cantine-admin-section { padding:18px 20px; border-bottom:1px solid #ececec; }
.cantine-admin-section:last-child { border-bottom:0; }
.cantine-admin-section h3 { margin:0 0 4px 0; color:#2c3e50; font-size:16px; }
.cantine-admin-section h3 .badge { display:inline-block; width:22px; height:22px; line-height:22px; text-align:center; border-radius:50%; color:#fff; font-size:12px; font-weight:700; margin-right:6px; vertical-align:middle; }
.cantine-admin-section h3 .badge.b1 { background:#3498db; }
.cantine-admin-section h3 .badge.b2 { background:#e67e22; }
.cantine-admin-section .hint { color:#7a8a99; font-size:13px; margin:0 0 14px 0; }

/* Liste règles compacte */
.cantine-rules-list { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border:1px solid #ececec; border-radius:5px; overflow:hidden; }
.cantine-rules-list th { background:#3a4a5c; color:#fff; padding:9px 10px; font-size:11px; text-transform:uppercase; letter-spacing:.5px; text-align:left; font-weight:600; }
.cantine-rules-list th.center, .cantine-rules-list td.center { text-align:center; }
.cantine-rules-list td { padding:7px 10px; border-bottom:1px solid #f0f0f0; vertical-align:middle; font-size:13px; }
.cantine-rules-list tr:last-child td { border-bottom:0; }
.cantine-rules-list tr.row-active { background:#f4faf6; }
.cantine-rules-list tr.row-inactive { background:#fafafa; opacity:.7; }
.cantine-rules-list .day-label { font-weight:600; color:#2c3e50; }
.cantine-rules-list input[type=number],
.cantine-rules-list input[type=time],
.cantine-rules-list select { padding:5px 6px; border:1px solid #d0d7de; border-radius:4px; background:#fff; font-size:13px; }
.cantine-rules-list input[type=number] { width:65px; text-align:center; }
.cantine-rules-list input[type=time]   { width:90px; }
.cantine-rules-list select             { max-width:200px; }
.cantine-rules-list .switch input { vertical-align:middle; transform:scale(1.2); margin-right:4px; }

/* Bloc génération inline */
.cantine-gen-row { display:flex; align-items:center; flex-wrap:wrap; gap:14px; padding:10px 12px; background:#fafbfc; border:1px solid #ececec; border-radius:5px; }
.cantine-gen-row label { font-size:13px; color:#3a4a5c; cursor:pointer; }
.cantine-gen-row label b { color:#2c3e50; }
.cantine-gen-row .grey { color:#7a8a99; font-size:12px; }
.cantine-gen-row input[type=date] { padding:6px 8px; border:1px solid #d0d7de; border-radius:4px; font-size:13px; }
.cantine-gen-actions { display:flex; gap:10px; margin-top:14px; flex-wrap:wrap; }

/* Responsive */
@media (max-width:767px){
    .cantine-agenda-grid { gap:2px; }
    .cantine-agenda-grid .cell { min-height:55px; padding:3px; }
    .cantine-agenda-grid .cell .num { font-size:10px; }
    .session-pill { font-size:10px; padding:2px 3px; }
    .session-pill .nb { display:none; }
    .cantine-rules-list { font-size:12px; }
    .cantine-rules-list select { max-width:140px; }
}
</style>

<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <!-- En-tête -->
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
        <!-- AGENDA MENSUEL DES SESSIONS PLANIFIÉES (en premier)           -->
        <!-- ============================================================ -->
        <div class="grid grid_12">
            <h2 class="greydark">
                <i class="icon-calendar"></i>
                <?php echo $this->lang->line('cantine_agenda_title');?>
            </h2>
            <div class="nicdark_space10"></div>
            <p class="grey"><?php echo $this->lang->line('cantine_agenda_hint');?></p>
            <div class="nicdark_space20"></div>
        </div>

        <div class="grid grid_12">
            <div class="cantine-agenda-wrap">

                <div class="cantine-agenda-head">
                    <h3><?php echo $agenda_label;?></h3>
                    <div class="cantine-agenda-nav">
                        <a href="<?php echo base_url('Cantine_controller/config?ecole='.$ecole.'&ym='.$agenda_prev_ym);?>"
                           title="<?php echo $this->lang->line('cantine_agenda_prev');?>">&laquo;</a>
                        <a href="<?php echo base_url('Cantine_controller/config?ecole='.$ecole.'&ym='.date('Y-m'));?>"
                           class="today"><?php echo $this->lang->line('cantine_agenda_today');?></a>
                        <a href="<?php echo base_url('Cantine_controller/config?ecole='.$ecole.'&ym='.$agenda_next_ym);?>"
                           title="<?php echo $this->lang->line('cantine_agenda_next');?>">&raquo;</a>
                    </div>
                </div>

                <?php
                // S'il n'y a aucune session sur ce mois, on affiche un état vide
                $has_any = false;
                for($d = 1; $d <= $_days; $d++){
                    $dkey = $agenda_ym.'-'.str_pad($d, 2, '0', STR_PAD_LEFT);
                    if (!empty($agenda_sessions[$dkey])){ $has_any = true; break; }
                }
                ?>

                <div class="cantine-agenda-grid">
                    <?php foreach($_fr_days AS $dn){ ?>
                        <div class="dow"><?php echo $dn;?></div>
                    <?php } ?>

                    <?php
                    // Cellules vides avant le 1er du mois
                    for($i = 0; $i < $_offset; $i++){
                        echo '<div class="cell empty"></div>';
                    }

                    // Cellules des jours du mois
                    for($d = 1; $d <= $_days; $d++){
                        $dkey   = $agenda_ym.'-'.str_pad($d, 2, '0', STR_PAD_LEFT);
                        $dow    = (int)date('N', strtotime($dkey)); // 1..7
                        $is_we  = ($dow >= 6);
                        $is_today = ($dkey === $_today);

                        $classes = ['cell'];
                        if ($is_we)    $classes[] = 'weekend';
                        if ($is_today) $classes[] = 'today';
                    ?>
                        <div class="<?php echo implode(' ', $classes);?>">
                            <span class="num"><?php echo $d;?></span>
                            <?php if (!empty($agenda_sessions[$dkey])){
                                foreach($agenda_sessions[$dkey] AS $s){
                                    $nb     = (int)$s->nb_inscrits;
                                    $max    = (int)$s->nb_inscrits_max;
                                    $reste  = max(0, $max - $nb);

                                    if ($max > 0 && $nb >= $max){
                                        $cls = 'full';
                                        $title = $this->lang->line('cantine_agenda_full');
                                    } elseif ($nb === 0){
                                        $cls = 'empty-sess';
                                        $title = $max.' '.$this->lang->line('cantine_agenda_open');
                                    } else {
                                        $cls = 'partial';
                                        $title = $reste.' '.$this->lang->line('cantine_agenda_open');
                                    }
                                    $h_deb = substr($s->heure_deb_trav, 0, 5);
                            ?>
                                <span class="session-pill <?php echo $cls;?>"
                                      title="<?php echo html_escape($h_deb.' — '.$nb.'/'.$max.' inscrits ('.$title.')');?>">
                                    <span class="h"><?php echo $h_deb;?></span>
                                    <span class="nb"><?php echo $nb;?>/<?php echo $max;?></span>
                                </span>
                            <?php   }
                            } ?>
                        </div>
                    <?php } ?>
                </div>

                <?php if (!$has_any){ ?>
                    <div class="cantine-agenda-empty">
                        <i class="icon-calendar"></i> <?php echo $this->lang->line('cantine_agenda_empty');?>
                    </div>
                <?php } ?>

                <div class="cantine-agenda-legend">
                    <span class="lg empty-sess"></span><?php echo $this->lang->line('cantine_agenda_legend_empty');?>
                    <span class="lg partial"></span><?php echo $this->lang->line('cantine_agenda_legend_partial');?>
                    <span class="lg full"></span><?php echo $this->lang->line('cantine_agenda_legend_full');?>
                </div>

            </div>
            <div class="nicdark_space30"></div>
        </div>

        <!-- ============================================================ -->
        <!-- BLOC ADMIN UNIFIÉ : Règles (1) + Génération (2)               -->
        <!-- ============================================================ -->
        <div class="grid grid_12">
            <h2 class="greydark">
                <i class="icon-cog"></i>
                <?php echo $this->lang->line('cantine_admin_title');?>
            </h2>
            <div class="nicdark_space10"></div>
            <p class="grey"><?php echo $this->lang->line('cantine_admin_hint');?></p>
            <div class="nicdark_space20"></div>
        </div>

        <div class="grid grid_12">
            <div class="cantine-admin-card">

                <!-- ============================ -->
                <!-- 1) Règles                    -->
                <!-- ============================ -->
                <div class="cantine-admin-section">
                    <h3>
                        <span class="badge b1">1</span>
                        <?php echo $this->lang->line('cantine_step1_title');?>
                    </h3>
                    <p class="hint"><?php echo $this->lang->line('cantine_step1_hint');?></p>

                    <?php echo form_open('Cantine_controller/save_config');?>
                    <input type="hidden" name="ecole" value="<?php echo html_escape($ecole);?>" />

                    <table class="cantine-rules-list">
                        <thead>
                            <tr>
                                <th class="center" style="width:60px;"><?php echo $this->lang->line('cantine_rules_col_active');?></th>
                                <th style="width:110px;"><?php echo $this->lang->line('cantine_rules_col_day');?></th>
                                <th class="center" style="width:90px;"><?php echo $this->lang->line('cantine_rules_col_parents');?></th>
                                <th class="center" style="width:90px;"><?php echo $this->lang->line('cantine_rules_col_units');?></th>
                                <th class="center" style="width:210px;"><?php echo $this->lang->line('cantine_rules_col_hours');?></th>
                                <th><?php echo $this->lang->line('cantine_rules_col_referent');?></th>
                            </tr>
                        </thead>
                        <tbody>
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
                        ?>
                            <tr class="<?php echo $is_active ? 'row-active' : 'row-inactive';?>">
                                <td class="center">
                                    <label class="switch" title="<?php echo $this->lang->line('cantine_day_needed');?>">
                                        <input type="checkbox" name="active_<?php echo $id_day;?>" value="1"
                                               <?php echo $is_active ? 'checked' : '';?> />
                                    </label>
                                </td>
                                <td>
                                    <span class="day-label"><?php echo $label;?></span>
                                </td>
                                <td class="center">
                                    <input type="number" min="0" max="20" step="1"
                                           name="nb_slots_<?php echo $id_day;?>"
                                           value="<?php echo (int)$cfg->nb_slots;?>" />
                                </td>
                                <td class="center">
                                    <input type="number" min="0" max="10" step="0.25"
                                           name="nb_units_<?php echo $id_day;?>"
                                           value="<?php echo (float)$cfg->nb_units;?>" />
                                </td>
                                <td class="center">
                                    <input type="time" name="heure_deb_<?php echo $id_day;?>"
                                           value="<?php echo html_escape(!empty($cfg->heure_deb) ? $cfg->heure_deb : '11:45');?>" />
                                    <span class="grey">→</span>
                                    <input type="time" name="heure_fin_<?php echo $id_day;?>"
                                           value="<?php echo html_escape(!empty($cfg->heure_fin) ? $cfg->heure_fin : '13:30');?>" />
                                </td>
                                <td>
                                    <select name="id_referent_<?php echo $id_day;?>">
                                        <option value=""><?php echo $this->lang->line('cantine_referent_none');?></option>
                                        <?php foreach($referents AS $rid => $rtitle){ ?>
                                            <option value="<?php echo (int)$rid;?>"
                                                <?php echo ((int)$cfg->id_referent === (int)$rid) ? 'selected' : '';?>>
                                                <?php echo html_escape($rtitle);?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>

                    <div style="margin-top:14px;">
                        <button type="submit" class="nicdark_press nicdark_btn nicdark_bg_green white nicdark_radius nicdark_shadow small">
                            <i class="icon-ok"></i> <?php echo $this->lang->line('cantine_save_config');?>
                        </button>
                    </div>

                    <?php echo form_close();?>
                </div>

                <!-- ============================ -->
                <!-- 2) Génération                -->
                <!-- ============================ -->
                <div class="cantine-admin-section">
                    <h3>
                        <span class="badge b2">2</span>
                        <?php echo $this->lang->line('cantine_step2_title');?>
                    </h3>
                    <p class="hint"><?php echo $this->lang->line('cantine_step2_hint');?></p>

                    <?php echo form_open('Cantine_controller/generate');?>
                    <input type="hidden" name="ecole" value="<?php echo html_escape($ecole);?>" />

                    <div class="cantine-gen-row">
                        <label>
                            <input type="radio" name="period_mode" value="school_end" checked
                                   onclick="cantineTogglePeriod(false);" />
                            <b><?php echo $this->lang->line('cantine_period_school_end');?></b>
                            <span class="grey">
                                (<?php echo date('d/m/Y', strtotime($default_date_deb));?>
                                &nbsp;→&nbsp;
                                <?php echo date('d/m/Y', strtotime($default_date_fin));?>)
                            </span>
                        </label>
                    </div>

                    <div style="height:8px;"></div>

                    <div class="cantine-gen-row">
                        <label>
                            <input type="radio" name="period_mode" value="custom"
                                   onclick="cantineTogglePeriod(true);" />
                            <b><?php echo $this->lang->line('cantine_period_custom');?></b>
                        </label>

                        <span id="cantine_custom_dates" style="display:none; gap:14px; align-items:center; flex-wrap:wrap;">
                            <span>
                                <?php echo $this->lang->line('cantine_date_deb');?>
                                <input type="date" name="date_deb"
                                       value="<?php echo html_escape($default_date_deb);?>" />
                            </span>
                            <span>
                                <?php echo $this->lang->line('cantine_date_fin');?>
                                <input type="date" name="date_fin"
                                       value="<?php echo html_escape($default_date_fin);?>" />
                            </span>
                        </span>
                    </div>

                    <div class="cantine-gen-actions">
                        <button type="submit"
                                class="nicdark_press nicdark_btn nicdark_bg_orange white nicdark_radius nicdark_shadow small"
                                onclick="return confirm('<?php echo $this->lang->line('cantine_confirm_generate');?>');">
                            <i class="icon-calendar"></i>
                            <?php echo $this->lang->line('cantine_btn_generate');?>
                        </button>
                    </div>

                    <?php echo form_close();?>
                </div>

            </div>
            <div class="nicdark_space30"></div>
        </div>

        <script>
            function cantineTogglePeriod(show){
                var el = document.getElementById('cantine_custom_dates');
                if (el) el.style.display = show ? 'inline-flex' : 'none';
            }
        </script>

        <!-- Historique des générations -->
        <?php if (!empty($generations)){ ?>
        <div class="grid grid_12">
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
