<?php
/**
 * Vue Cantine_controller/register — agenda hebdomadaire des sessions cantine.
 *
 * Évolutions ergonomiques (vs version initiale) :
 *  - Grille CSS Grid 5 colonnes (égalité largeur + hauteur des cartes)
 *  - Carte en flex-column : action toujours alignée en bas
 *  - "Place libre" = lien d'inscription direct (suppression du bouton M'inscrire en doublon)
 *  - "Date passée" = classe is-passed (atténuation + tag dans le bandeau, plus de bouton flottant)
 *  - Heures sans secondes (substr 0,5)
 *  - Stats compactées en barre horizontale
 *  - Sélecteur école rapproché du titre
 *
 * Variables disponibles (cf. Cantine_controller::register) :
 *   $days, $week_offset, $week_label, $stats, $is_admin, $id_fam, $ecole, $can_register
 */
?>

<style>
/* ====================================================================
   Cantine — Vue parent (agenda hebdomadaire)  -- styles scopés
   ==================================================================== */

/* En-tête : titres + sélecteur école sur la même ligne ---------------- */
.cantine-head { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:24px; }
.cantine-head .titles { flex:1 1 60%; min-width:280px; }
.cantine-head .schools { flex:0 0 auto; text-align:right; }
.cantine-head .schools .lbl { display:block; font-size:13px; color:#7a8a99; margin-bottom:6px; }
.cantine-head .schools a { display:inline-block; padding:7px 16px; margin-left:4px; border-radius:4px; font-size:13px; font-weight:600; text-decoration:none; transition:.15s; }
.cantine-head .schools a.on  { background:#5dade2; color:#fff; box-shadow:0 1px 2px rgba(0,0,0,.1); }
.cantine-head .schools a.off { background:#eef1f4; color:#5d6d7e; }
.cantine-head .schools a.off:hover { background:#dfe4e8; }

/* Barre semaine ------------------------------------------------------- */
.cantine-weekbar { display:flex; align-items:center; flex-wrap:wrap; gap:6px; margin-bottom:6px; }
.cantine-weekbar .nav { display:flex; gap:4px; }
.cantine-weekbar .spacer { flex:1 1 auto; }
.cantine-weekbar a.btn { padding:7px 13px; border-radius:4px; font-size:13px; font-weight:600; text-decoration:none; background:#eef1f4; color:#5d6d7e; transition:.15s; }
.cantine-weekbar a.btn:hover { background:#dfe4e8; }
.cantine-weekbar a.btn-cfg { background:#5dade2; color:#fff; }
.cantine-weekbar a.btn-cfg:hover { background:#4a9ed1; }
.cantine-weeklabel { font-size:14px; color:#5d6d7e; margin:6px 0 18px 0; font-weight:500; }

/* Stats compactes ----------------------------------------------------- */
.cantine-stats { display:flex; gap:10px; margin-bottom:24px; flex-wrap:wrap; }
.cantine-stats .stat { flex:1 1 0; min-width:160px; padding:11px 16px; border-radius:5px; box-shadow:0 1px 2px rgba(0,0,0,.06); display:flex; align-items:center; justify-content:space-between; gap:12px; }
.cantine-stats .stat .lbl { font-size:13px; opacity:.95; }
.cantine-stats .stat .val { font-size:26px; font-weight:700; line-height:1; }
.cantine-stats .stat.s-grey  { background:#f4f6f8; color:#3a4a5c; }
.cantine-stats .stat.s-green { background:#5cb85c; color:#fff; }
.cantine-stats .stat.s-orng  { background:#ed7d31; color:#fff; }

/* Grille des 5 jours -------------------------------------------------- */
.cantine-week { display:grid; grid-template-columns:repeat(5, 1fr); gap:14px; align-items:stretch; }
@media (max-width:1100px){ .cantine-week { grid-template-columns:repeat(3, 1fr); } }
@media (max-width:760px) { .cantine-week { grid-template-columns:repeat(2, 1fr); } }
@media (max-width:480px) { .cantine-week { grid-template-columns:1fr; } }

/* Carte jour ---------------------------------------------------------- */
.day-card { display:flex; flex-direction:column; border-radius:6px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.10); position:relative; min-height:280px; }

/* Bandeau jour (toujours sombre) */
.day-card .dch { padding:10px 14px; display:flex; align-items:center; justify-content:space-between; background:#3a4a5c; color:#fff; gap:6px; }
.day-card .dch .day { font-size:15px; font-weight:600; }
.day-card .dch .right { display:flex; align-items:center; gap:5px; }
.day-card .dch .date { font-size:12px; opacity:.9; padding:2px 7px; background:rgba(255,255,255,.16); border-radius:3px; white-space:nowrap; }
.day-card .dch .past { font-size:10px; font-weight:700; padding:2px 7px; background:#c0392b; border-radius:3px; text-transform:uppercase; letter-spacing:.4px; }

/* Corps */
.day-card .dcb { flex:1 1 auto; padding:12px 14px; display:flex; flex-direction:column; }
.day-card .meta { display:flex; align-items:center; justify-content:space-between; font-size:12px; margin-bottom:10px; }
.day-card .meta .h i { margin-right:3px; }
.day-card .meta .ratio { font-weight:600; padding:2px 8px; border-radius:3px; font-size:12px; }

.day-card .seclbl { font-size:11px; text-transform:uppercase; letter-spacing:.5px; opacity:.85; margin:2px 0 6px 0; }
.day-card .seclbl i { margin-right:4px; }

/* Liste de places */
.day-card .slots { list-style:none; padding:0; margin:0; }
.day-card .slots li { padding:5px 0; border-bottom:1px dashed rgba(255,255,255,.20); font-size:13px; display:flex; align-items:center; gap:6px; min-height:26px; }
.day-card .slots li:last-child { border-bottom:0; }
.day-card .slots li i { font-size:13px; opacity:.85; }
.day-card .slots li.me { font-weight:700; }
.day-card .slots li .ok { margin-left:auto; opacity:.95; }

/* Place libre = lien cliquable (suppression du bouton M'inscrire en doublon) */
.day-card .slots li.free a, .day-card .slots li.free .nolink { display:flex; align-items:center; gap:6px; width:100%; text-decoration:none; color:inherit; opacity:.85; font-style:italic; }
.day-card .slots li.free a:hover { opacity:1; background:rgba(255,255,255,.10); margin:0 -6px; padding:0 6px; border-radius:3px; }

/* Action en pied de corps (alignée en bas par margin-top:auto) */
.day-card .action { margin-top:auto; padding-top:12px; }
.day-card .action .btn-unreg { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:4px; font-size:13px; font-weight:600; text-decoration:none; background:#c0392b; color:#fff; transition:.15s; }
.day-card .action .btn-unreg:hover { background:#a93226; }
.day-card .action .badge { display:inline-flex; align-items:center; gap:5px; padding:6px 11px; border-radius:4px; font-size:12px; font-weight:600; background:rgba(0,0,0,.18); color:#fff; }

/* Couleurs de carte selon l'état */
.day-card.c-blue      { background:#5dade2; color:#fff; }
.day-card.c-blue   .meta .ratio { background:#2e86c1; }
.day-card.c-green     { background:#58d68d; color:#fff; }
.day-card.c-green  .meta .ratio { background:#1e8449; }
.day-card.c-greendk   { background:#239b56; color:#fff; }
.day-card.c-greendk .meta .ratio { background:#196f3d; }
.day-card.c-red       { background:#ec7063; color:#fff; }
.day-card.c-red    .meta .ratio { background:#a93226; }

/* Carte vide (Pas de garde) — même hauteur que les autres */
.day-card.c-grey      { background:#f1f3f5; color:#7a8a99; }
.day-card.c-grey .dch { background:#7f8c95; }
.day-card.c-grey .empty { flex:1 1 auto; display:flex; align-items:center; justify-content:center; text-align:center; padding:20px; font-style:italic; }
.day-card.c-grey .empty i { margin-right:6px; font-size:15px; }

/* État "Date passée" : atténuation du corps + tag dans le bandeau ----- */
.day-card.is-passed .dcb, .day-card.is-passed .empty { opacity:.55; }
.day-card.is-passed .action a { pointer-events:none; }
.day-card.is-passed .slots li.free a { pointer-events:none; cursor:default; }

/* Hint bas de page */
.cantine-hint { color:#7a8a99; font-size:12px; margin-top:18px; }
</style>

<section class="nicdark_section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <!-- 1. EN-TÊTE : titres + sélecteur école -->
        <div class="grid grid_12">
            <div class="cantine-head">
                <div class="titles">
                    <h1 class="subtitle greydark"><?php echo $this->lang->line('cantine_title');?></h1>
                    <div class="nicdark_space10"></div>
                    <h3 class="subtitle grey"><?php echo $this->lang->line('cantine_subtitle');?></h3>
                    <div class="nicdark_space10"></div>
                    <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
                </div>
                <div class="schools">
                    <span class="lbl"><?php echo $this->lang->line('cantine_school');?> :</span>
                    <?php foreach(['M' => 'Mulhouse', 'L' => 'Lutterbach'] AS $code => $label){
                        $cls = ($ecole === $code) ? 'on' : 'off';
                    ?>
                        <a href="<?php echo base_url('Cantine_controller/register?ecole='.$code);?>" class="<?php echo $cls;?>"><?php echo $label;?></a>
                    <?php } ?>
                </div>
            </div>
            <div class="nicdark_space20"></div>
        </div>

        <!-- 2. BARRE SEMAINE + bouton Paramétrer -->
        <div class="grid grid_12">
            <div class="cantine-weekbar">
                <div class="nav">
                    <a href="<?php echo base_url('Cantine_controller/register/'.($week_offset - 1));?>" class="btn">&laquo; <?php echo $this->lang->line('cantine_prev_week');?></a>
                    <a href="<?php echo base_url('Cantine_controller/register/0');?>" class="btn"><?php echo $this->lang->line('cantine_this_week');?></a>
                    <a href="<?php echo base_url('Cantine_controller/register/'.($week_offset + 1));?>" class="btn"><?php echo $this->lang->line('cantine_next_week');?> &raquo;</a>
                </div>
                <div class="spacer"></div>
                <?php if ($is_admin){ ?>
                    <a href="<?php echo base_url('Cantine_controller/config?ecole='.$ecole);?>" class="btn btn-cfg">
                        <i class="icon-cog"></i> <?php echo $this->lang->line('cantine_config_link');?>
                    </a>
                <?php } ?>
            </div>
            <div class="cantine-weeklabel"><?php echo $week_label;?></div>
        </div>

        <!-- 3. STATS COMPACTES -->
        <div class="grid grid_12">
            <div class="cantine-stats">
                <div class="stat s-grey">
                    <span class="lbl"><?php echo $this->lang->line('cantine_stat_days');?></span>
                    <span class="val"><?php echo $stats->active_days;?></span>
                </div>
                <div class="stat s-green">
                    <span class="lbl"><?php echo $this->lang->line('cantine_stat_mine');?></span>
                    <span class="val"><?php echo $stats->mine;?></span>
                </div>
                <div class="stat s-orng">
                    <span class="lbl"><?php echo $this->lang->line('cantine_stat_open');?></span>
                    <span class="val"><?php echo $stats->open;?></span>
                </div>
            </div>
        </div>

        <!-- 4. GRILLE 5 JOURS -->
        <div class="grid grid_12">
            <div class="cantine-week">

                <?php foreach($days AS $day){
                    $has_session = ($day->session !== null);
                    $is_passed   = !empty($day->passed);

                    // Choix de la couleur de carte
                    if (!$has_session)              $card_cls = 'c-grey';
                    elseif (!empty($day->my_validated)) $card_cls = 'c-greendk';
                    elseif (!empty($day->mine))     $card_cls = 'c-green';
                    elseif (!empty($day->full))     $card_cls = 'c-red';
                    else                            $card_cls = 'c-blue';
                ?>
                <div class="day-card <?php echo $card_cls.($is_passed ? ' is-passed' : '');?>">

                    <!-- Bandeau du jour : nom + (tag passée) + date -->
                    <div class="dch">
                        <span class="day"><?php echo $day->day_label;?></span>
                        <span class="right">
                            <?php if ($is_passed && $has_session){ ?>
                                <span class="past"><?php echo $this->lang->line('cantine_passed');?></span>
                            <?php } ?>
                            <span class="date"><?php echo $day->day_num.' '.$day->month_fr;?></span>
                        </span>
                    </div>

                    <?php if (!$has_session){ ?>

                        <!-- Pas de garde ce jour -->
                        <div class="empty">
                            <span><i class="icon-minus-circled"></i><?php echo $this->lang->line('cantine_day_inactive');?></span>
                        </div>

                    <?php } else {
                        // Heures sans secondes
                        $h_deb = substr((string)$day->session->heure_deb_trav, 0, 5);
                        $h_fin = substr((string)$day->session->heure_fin_trav, 0, 5);
                    ?>

                        <!-- Corps -->
                        <div class="dcb">

                            <!-- Méta : horaires + ratio inscrits/max -->
                            <div class="meta">
                                <span class="h"><i class="icon-clock-1"></i><?php echo $h_deb;?> &rarr; <?php echo $h_fin;?> &middot; <b><?php echo (float)$day->nb_units;?></b>&nbsp;u.</span>
                                <span class="ratio"><?php echo $day->nb_inscrits.'/'.$day->nb_slots;?></span>
                            </div>

                            <div class="seclbl"><i class="icon-users"></i><?php echo $this->lang->line('cantine_registered');?></div>

                            <!-- Liste des places (slots) -->
                            <ul class="slots">
                                <?php for($i = 0; $i < $day->nb_slots; $i++){
                                    $ins = isset($day->inscrits[$i]) ? $day->inscrits[$i] : null;
                                    if ($ins){
                                        $name      = !empty($ins->nom) ? $ins->nom : $ins->login;
                                        $is_me     = ((int)$ins->id_famille === (int)$id_fam);
                                        $validated = ((float)$ins->nb_unites_valides_effectif > 0);
                                ?>
                                    <li class="<?php echo $is_me ? 'me' : '';?>">
                                        <i class="icon-user"></i>
                                        <span><?php echo html_escape($name);?><?php if ($is_me) echo ' ('.$this->lang->line('cantine_you').')';?></span>
                                        <?php if ($validated){ ?><i class="icon-ok ok" title="<?php echo $this->lang->line('cantine_validated');?>"></i><?php } ?>
                                    </li>
                                <?php } else {
                                    // Place libre : cliquable si la famille peut prendre le créneau
                                    $can_click = ($can_register && !$is_passed && empty($day->mine) && empty($day->full));
                                ?>
                                    <li class="free">
                                        <?php if ($can_click){ ?>
                                            <a href="<?php echo base_url('Cantine_controller/register_one/'.$day->session->id);?>" title="<?php echo $this->lang->line('cantine_btn_register');?>">
                                                <i class="icon-plus-circled"></i><?php echo $this->lang->line('cantine_slot_free');?>
                                            </a>
                                        <?php } else { ?>
                                            <span class="nolink"><i class="icon-plus-circled"></i><?php echo $this->lang->line('cantine_slot_free');?></span>
                                        <?php } ?>
                                    </li>
                                <?php }
                                } ?>
                            </ul>

                            <!-- Action en bas (poussée par margin-top:auto) -->
                            <div class="action">
                                <?php if (!empty($day->my_validated)){ ?>
                                    <span class="badge"><i class="icon-ok"></i><?php echo $this->lang->line('cantine_validated');?></span>
                                <?php } elseif (!empty($day->mine)){ ?>
                                    <?php if ($can_register && !$is_passed){ ?>
                                        <a href="<?php echo base_url('Cantine_controller/unregister_one/'.$day->session->id);?>"
                                           onclick="return confirm('<?php echo $this->lang->line('cantine_confirm_cancel');?>');"
                                           class="btn-unreg">
                                            <i class="icon-cancel"></i><?php echo $this->lang->line('cantine_btn_cancel');?>
                                        </a>
                                    <?php } else { ?>
                                        <span class="badge"><i class="icon-ok"></i><?php echo $this->lang->line('cantine_you');?></span>
                                    <?php } ?>
                                <?php } elseif (!empty($day->full)){ ?>
                                    <span class="badge"><?php echo $this->lang->line('cantine_full');?></span>
                                <?php } ?>
                            </div>

                        </div>

                    <?php } ?>

                </div>
                <?php } ?>

            </div>
            <p class="cantine-hint"><small><?php echo $this->lang->line('cantine_register_hint');?></small></p>
        </div>

        <div class="grid grid_12"><div class="nicdark_space30"></div></div>

    </div>
</section>
