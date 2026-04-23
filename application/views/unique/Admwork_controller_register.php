<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * application/views/unique/Admwork_controller_register.php  — v2
 * Design fidèle à la maquette : cartes blanches, bordure gauche colorée,
 * badge type, méta icônes, footer propre. Pas d'image.
 */

/* -----------------------------------------------------------------------
   Helpers PHP
   ----------------------------------------------------------------------- */
$upcoming = []; $past = [];
$nb_dispo_total = 0; $nb_my_registered = 0;

if (!empty($works)) {
    foreach ($works as $w) {
        if ($w->delay > 0) { $past[] = $w; }
        else {
            $upcoming[] = $w;
            if ($w->register) $nb_dispo_total += max(0, (int)$w->nb_inscrits_max - (int)$w->participant);
        }
        if (!empty($w->already_registred)) $nb_my_registered++;
    }
    usort($upcoming, function($a,$b){ return strcmp($a->date_travaux, $b->date_travaux); });
    usort($past,     function($a,$b){ return strcmp($b->date_travaux, $a->date_travaux); });
}

/* Groupement passées par mois */
$past_by_month = [];
$months_fr = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
foreach ($past as $w) {
    $ts  = strtotime($w->date_travaux);
    $key = date('Y-m', $ts);
    $past_by_month[$key]['label']   = $months_fr[(int)date('m',$ts)].' '.date('Y',$ts);
    $past_by_month[$key]['works'][] = $w;
}
ksort($past_by_month);
$past_by_month = array_reverse($past_by_month, true);

/* Countdown */
if (!function_exists('_aw_countdown')) {
    function _aw_countdown($delay) {
        $d = abs((int)$delay);
        if ($delay > 0) {
            if ($d <= 1)  return ['il y a 1 j',        'is-past'];
            if ($d < 30)  return ['il y a '.$d.' j',   'is-past'];
            if ($d < 60)  return ['il y a ~1 mois',     'is-past'];
            return ['il y a '.floor($d/30).' mois',     'is-past'];
        }
        if ($d == 0) return ["aujourd'hui", 'is-soon'];
        if ($d <= 7) return ['dans '.$d.' j', 'is-soon'];
        return ['dans '.$d.' j', ''];
    }
}

/* Classe stripe par type */
if (!function_exists('_aw_stripe')) {
    function _aw_stripe($type) {
        $map = ['TRA'=>'TRA','MEN'=>'MEN','INF'=>'INF','GOU'=>'GOU','LAV'=>'LAV','DEC'=>'DEC','URG'=>'URG'];
        return 'aw-stripe-'.(isset($map[$type]) ? $map[$type] : 'default');
    }
}
/* Classe badge par type */
if (!function_exists('_aw_badge')) {
    function _aw_badge($type) {
        $map = ['TRA'=>'TRA','MEN'=>'MEN','INF'=>'INF','GOU'=>'GOU','LAV'=>'LAV','DEC'=>'DEC','URG'=>'URG'];
        return 'aw-badge-'.(isset($map[$type]) ? $map[$type] : 'default');
    }
}

/* Libellé badge */
if (!function_exists('_aw_badge_label')) {
    function _aw_badge_label($design, $type) {
        if (!empty($design->title)) return $design->title;
        $labels = ['TRA'=>'Travaux','MEN'=>'Ménage','INF'=>'Informatique','GOU'=>'Goûter','LAV'=>'Lavage','DEC'=>'Déchetterie','URG'=>'Urgent'];
        return isset($labels[$type]) ? $labels[$type] : $type;
    }
}

/* JSON pour FullCalendar */
$nicdark_hex = ['nicdark_bg_green'=>'#6fc191','nicdark_bg_blue'=>'#74cee4','nicdark_bg_violet'=>'#c389ce','nicdark_bg_orange'=>'#ec774b','nicdark_bg_red'=>'#e16c6c','nicdark_bg_yellow'=>'#edbf47','nicdark_bg_greendark'=>'#6ab78a','nicdark_bg_greydark'=>'#495052'];
$cal_events = [];
if (!empty($works)) {
    foreach ($works as $w) {
        $design = $this->render_object->GetDesign($w->type);
        $hex = isset($nicdark_hex[$design->color]) ? $nicdark_hex[$design->color] : '#74cee4';
        $cal_events[] = [
            'id'=>$w->id,'title'=>$w->titre,'start'=>$w->date_travaux,'color'=>$hex,
            'extendedProps'=>[
                'ecole'    =>strip_tags($this->render_object->RenderElement('ecole',$w->ecole)),
                'type_lbl' =>strip_tags($this->render_object->RenderElement('type_session',$w->type_session)),
                'heure_deb'=>($w->type_session==1)?$w->heure_deb_trav:null,
                'heure_fin'=>($w->type_session==1)?$w->heure_fin_trav:null,
                'inscrits' =>(int)$w->participant,'max'=>(int)$w->nb_inscrits_max,
                'is_past'  =>($w->delay>0),
                'url'      =>base_url('Admwork_controller/register_one/'.$w->id),
                'url_manage'=>($this->acl->getType()=='sys')?base_url('Admwork_controller/managed_one/'.$w->id):null,
            ],
        ];
    }
}

/* Macro carte — évite la répétition pour À venir et Passées */
function _aw_render_card($work, $design, $is_past, $acl_type, $lang) {
    list($cd_label, $cd_class) = _aw_countdown($work->delay);
    $stripe = _aw_stripe($work->type);
    $badge  = _aw_badge($work->type);
    $badge_label = _aw_badge_label($design, $work->type);
    $mine_cls  = (!empty($work->already_registred)) ? 'mine' : '';
    $dispo_cls = (!$is_past && $work->register) ? 'dispo' : '';
    $past_cls  = $is_past ? 'is-past archived' : '';
    ob_start(); ?>
    <div class="aw-card <?php echo $past_cls; ?> <?php echo $work->type; ?> <?php echo $dispo_cls; ?> <?php echo $mine_cls; ?> nicdark_masonry_item">
        <div class="aw-card-inner <?php echo $stripe; ?>">
            <!-- Corps -->
            <div class="aw-card-body">
                <!-- Date + countdown -->
                <div class="aw-card-dateline">
                    <span class="aw-card-date"><?php
                        /* Formatage date en "Sam. 26 avril" */
                        $ts = strtotime($work->date_travaux);
                        $jours = ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'];
                        $mois  = ['','janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];
                        echo $jours[(int)date('w',$ts)].' '.date('j',$ts).' '.$mois[(int)date('n',$ts)];
                    ?></span>
                    <span class="aw-countdown <?php echo $cd_class; ?>"><?php echo $cd_label; ?></span>
                </div>
                <!-- Titre -->
                <p class="aw-card-title"><?php echo $work->titre; ?></p>
                <!-- Badge type -->
                <span class="aw-type-badge <?php echo $badge; ?>"><?php echo $badge_label; ?></span>
                <!-- Méta -->
                <div class="aw-card-meta">
                    <div class="aw-meta-row"><i class="icon-pin-outline"></i><?php echo strip_tags($lang->line('') ?: $work->ecole); ?></div>
                    <?php if ($work->type_session == 1): ?>
                    <div class="aw-meta-row"><i class="icon-clock-1"></i><?php echo $work->heure_deb_trav; ?> – <?php echo $work->heure_fin_trav; ?></div>
                    <?php endif; ?>
                    <div class="aw-meta-row"><i class="icon-info-outline"></i>
                        <?php /* type_session rendu via render_object */ ?>
                        Session <?php echo strtolower($badge_label); ?>
                    </div>
                </div>
            </div><!-- /.aw-card-body -->
            <!-- Pied -->
            <div class="aw-card-footer">
                <span class="aw-slots">
                    <strong><?php echo (int)$work->participant; ?></strong> / <?php echo (int)$work->nb_inscrits_max; ?>
                    <?php if (!empty($work->already_registred) && !$is_past) echo ' · <span class="aw-badge-inscrit">&#10003; Inscrit</span>'; ?>
                    <?php if ($is_past && !empty($work->already_registred)) echo ' · participé'; ?>
                    <?php if ($is_past && empty($work->already_registred))  echo ' · Non inscrit'; ?>
                </span>
                <div class="aw-footer-actions">
                    <?php if ($acl_type == 'sys'): ?>
                        <?php if ($is_past): ?>
                            <a href="<?php echo base_url('Admwork_controller/managed_one/'.$work->id); ?>" class="aw-btn adm">Gérer</a>
                        <?php else: ?>
                            <a href="<?php echo base_url('Admwork_controller/managed_one/'.$work->id); ?>" class="aw-btn adm">Gérer</a>
                        <?php endif; ?>
                    <?php elseif ($is_past): ?>
                        <a href="<?php echo base_url('Admwork_controller/register_one/'.$work->id); ?>" class="aw-btn details">Détails</a>
                    <?php elseif ($work->register): ?>
                        <?php if (!empty($work->already_registred)): ?>
                            <a href="<?php echo base_url('Admwork_controller/register_one/'.$work->id); ?>" class="aw-btn inscrit">&#10003; Inscrit</a>
                        <?php else: ?>
                            <a href="<?php echo base_url('Admwork_controller/register_one/'.$work->id); ?>" class="aw-btn">S'inscrire</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="aw-btn complet">Complet</span>
                    <?php endif; ?>
                </div>
            </div><!-- /.aw-card-footer -->
        </div><!-- /.aw-card-inner -->
    </div><!-- /.aw-card -->
    <?php return ob_get_clean();
}
?>

<!--start section-->
<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
<div class="nicdark_space30"></div>

<!-- ===== EN-TÊTE + TOGGLE VUE ===== -->
<div class="grid grid_12">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 class="subtitle greydark"><?php echo $this->lang->line('work_planned'); ?></h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey"><?php echo $this->lang->line('work_planned_subtitle'); ?></h3>
        </div>
        <div style="padding-top:6px;">
            <div class="view-toggle">
                <a href="#" id="btn-view-cards" class="active" onclick="awSwitchView('cards');return false;"><i class="icon-layout"></i> <span>Cartes</span></a>
                <a href="#" id="btn-view-calendar" onclick="awSwitchView('calendar');return false;"><i class="icon-calendar"></i> <span>Calendrier</span></a>
            </div>
        </div>
    </div>
    <div class="nicdark_space20"></div>
    <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
    <div class="nicdark_space20"></div>
</div>

<!-- ===== MÉTRIQUES ===== -->
<div class="grid grid_12">
    <div class="aw-stats">
        <div class="aw-stat"><div class="st-label"><?php echo $this->lang->line('STATS_UPCOMING'); ?></div><div class="st-value"><?php echo count($upcoming); ?></div></div>
        <div class="aw-stat"><div class="st-label"><?php echo $this->lang->line('STATS_AVAILABLE'); ?></div><div class="st-value"><?php echo $nb_dispo_total; ?></div></div>
        <div class="aw-stat"><div class="st-label"><?php echo $this->lang->line('STATS_MY_REGISTRATIONS'); ?></div><div class="st-value"><?php echo $nb_my_registered; ?></div></div>
        <div class="aw-stat"><div class="st-label"><?php echo $this->lang->line('STATS_PAST'); ?></div><div class="st-value"><?php echo count($past); ?></div></div>
    </div>
</div>


<!-- ================================================================
     VUE CARTES
     ================================================================ -->
<div id="view-cards">

    <!-- Filtres chips -->
    <div class="grid grid_12">
        <div class="aw-filters-bar">
            <div class="aw-search-wrap">
                <i class="icon-search"></i>
                <input type="text" class="aw-search" placeholder="Rechercher…" id="aw-search-input" oninput="awSearch(this.value)">
            </div>
            <a href="#" class="aw-chip is-active" data-filter="*"      onclick="awFilter(this,'*');return false;"><?php echo $this->lang->line('All') ?: 'Tous'; ?></a>
            <a href="#" class="aw-chip"            data-filter=".dispo" onclick="awFilter(this,'.dispo');return false;"><?php echo $this->lang->line('NotFull') ?: 'Disponibles'; ?></a>
            <a href="#" class="aw-chip"            data-filter=".mine"  onclick="awFilter(this,'.mine');return false;"><?php echo $this->lang->line('STATS_MY_REGISTRATIONS') ?: 'Mes inscriptions'; ?></a>
            <a href="#" class="aw-chip"            data-filter=".archived" onclick="awFilter(this,'.archived');return false;"><?php echo $this->lang->line('SECTION_PAST') ?: 'Passées'; ?></a>
            <?php foreach ($WorkType as $key => $value): ?>
                <a href="#" class="aw-chip" data-filter=".<?php echo $key; ?>" onclick="awFilter(this,'.<?php echo $key; ?>');return false;"><?php echo $value; ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ---- À VENIR ---- -->
    <div class="grid grid_12">
        <div class="aw-section-head upcoming">
            <span class="sh-dot"></span>
            <h2><?php echo $this->lang->line('SECTION_UPCOMING') ?: 'À venir'; ?></h2>
            <span class="sh-count"><?php echo count($upcoming); ?></span>
            <span class="sh-line"></span>
        </div>
    </div>

    <?php if (empty($upcoming)): ?>
        <div class="grid grid_12"><p class="aw-empty"><?php echo $this->lang->line('SECTION_NO_UPCOMING') ?: 'Aucune session à venir.'; ?></p></div>
    <?php else: ?>
        <div class="grid grid_12">
            <div class="aw-cards-grid nicdark_masonry_container">
                <?php foreach ($upcoming as $work):
                    $design = $this->render_object->GetDesign($work->type);
                    echo _aw_render_card($work, $design, false, $this->acl->getType(), $this->lang);
                endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- ---- MARQUEUR AUJOURD'HUI ---- -->
    <?php if (!empty($upcoming) && !empty($past)): ?>
    <div class="grid grid_12">
        <div class="aw-today-marker">
            <span class="tm-line"></span>
            <i class="icon-calendar"></i>&nbsp;<?php echo $this->lang->line('TODAY_MARKER') ?: "Aujourd'hui"; ?> · <?php echo date('d/m/Y'); ?>
            <span class="tm-line"></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- ---- PASSÉES (accordéons par mois) ---- -->
    <div class="grid grid_12">
        <div class="aw-section-head past">
            <span class="sh-dot"></span>
            <h2><?php echo $this->lang->line('SECTION_PAST') ?: 'Passées'; ?></h2>
            <span class="sh-count"><?php echo count($past); ?></span>
            <span class="sh-line"></span>
        </div>
    </div>

    <?php if (empty($past_by_month)): ?>
        <div class="grid grid_12"><p class="aw-empty"><?php echo $this->lang->line('SECTION_NO_PAST') ?: 'Aucune session passée.'; ?></p></div>
    <?php else: ?>
        <div class="grid grid_12 aw-accordion-past">
        <?php $midx = 0; foreach ($past_by_month as $mkey => $mdata): $first = ($midx === 0); ?>

            <div class="aw-month-header <?php echo $first?'is-open':''; ?>" data-target="month-<?php echo $mkey; ?>">
                <div class="aw-month-title">
                    <i class="icon-calendar"></i>
                    <?php echo $mdata['label']; ?>
                    <span class="aw-month-badge"><?php echo count($mdata['works']); ?> session<?php echo count($mdata['works'])>1?'s':''; ?></span>
                </div>
                <i class="aw-month-chevron icon-down-open"></i>
            </div>

            <div class="aw-month-body <?php echo $first?'is-open':''; ?>" id="month-<?php echo $mkey; ?>">
                <div class="aw-cards-grid nicdark_masonry_container">
                    <?php foreach ($mdata['works'] as $work):
                        $design = $this->render_object->GetDesign($work->type);
                        echo _aw_render_card($work, $design, true, $this->acl->getType(), $this->lang);
                    endforeach; ?>
                </div>
            </div>

        <?php $midx++; endforeach; ?>
        </div>
    <?php endif; ?>

</div><!-- /#view-cards -->


<!-- ================================================================
     VUE CALENDRIER
     ================================================================ -->
<div id="view-calendar">
    <div class="grid grid_12">
        <div class="nicdark_space10"></div>
        <div id="aw-fullcalendar"></div>
        <div class="nicdark_space30"></div>
    </div>
</div>

<div class="nicdark_space50"></div>
</div><!-- /.nicdark_container -->
</section>

<script>var awCalendarEvents = <?php echo json_encode(array_values($cal_events), JSON_UNESCAPED_UNICODE); ?>;</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="<?php echo base_url('assets/js/admwork_register.js'); ?>"></script>
