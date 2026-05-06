<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * Admwork_controller_my_sessions.php — v5 (aligné sur register)
 * Liste des sessions où l'utilisateur connecté est référent.
 *
 * Toggle :  [⊞ Cartes]  [≡ Liste]  (préférence sauvée localStorage)
 * À venir  : cartes OU liste compacte + séparateurs de mois
 * Passées  : accordéons par mois
 *
 * Variable d'entrée : $my_works (toutes les sessions référent, à venir + passées)
 *
 * Bouton d'action contextuel :
 *   - Session à venir          → "Gérer les inscrits"   (validate_one en mode gestion)
 *   - Session du jour ou passée → "Valider les présences" (validate_one en mode validation)
 */

/* ------------------------------------------------------------------ DATA */
$upcoming = []; $past = [];
$nb_a_valider = 0;        // sessions terminées non encore validées (placeholder)
$nb_aujourdhui = 0;
$today_ts = strtotime(date('Y-m-d'));

foreach ($my_works as $w) {
    $w_ts = strtotime($w->date_travaux);
    $is_past = $w_ts < $today_ts;

    if ($is_past) {
        $past[] = $w;
    } else {
        $upcoming[] = $w;
        if ($w_ts === $today_ts) $nb_aujourdhui++;
    }
}
usort($upcoming, function($a,$b){ return strcmp($a->date_travaux,$b->date_travaux); });
usort($past,     function($a,$b){ return strcmp($b->date_travaux,$a->date_travaux); });

$months_fr = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$mois_abr  = ['','janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];

$upcoming_by_month = [];
foreach ($upcoming as $w) {
    $ts  = strtotime($w->date_travaux);
    $key = date('Y-m',$ts);
    $upcoming_by_month[$key]['label']   = $months_fr[(int)date('m',$ts)].' '.date('Y',$ts);
    $upcoming_by_month[$key]['works'][] = $w;
}

$past_by_month = [];
foreach ($past as $w) {
    $ts  = strtotime($w->date_travaux);
    $key = date('Y-m',$ts);
    $past_by_month[$key]['label']   = $months_fr[(int)date('m',$ts)].' '.date('Y',$ts);
    $past_by_month[$key]['works'][] = $w;
}
ksort($past_by_month);
$past_by_month = array_reverse($past_by_month, true);

/* Types présents dans MES sessions, pour les chips de filtre */
$WorkTypeAll = $this->Admwork_model->_get('defs')['type']->_get('values');
$present_types = [];
foreach ($my_works as $w) { $present_types[$w->type] = true; }
$WorkType = array_intersect_key($WorkTypeAll, $present_types);

/* ---------------------------------------------------------------- HELPERS */
if (!function_exists('_v5_day')) {
    function _v5_day($d)       { return date('j', strtotime($d)); }
    function _v5_mon($d, $m)   { return $m[(int)date('n', strtotime($d))]; }
    function _v5_stripe($t)    { return 'aw-stripe-'.(in_array($t,['TRA','MEN','INF','GOU','LAV','DEC','URG'])?$t:'default'); }
    function _v5_badge_cls($t) { return 'aw-badge-'.(in_array($t,['TRA','MEN','INF','GOU','LAV','DEC','URG'])?$t:'default'); }
    function _v5_countdown($date) {
        $today = new DateTimeImmutable(date('Y-m-d'));
        $dt    = new DateTimeImmutable($date);
        $diff  = (int)$today->diff($dt)->format('%R%a');
        $d     = abs($diff);
        if ($diff < 0) {
            if ($d==1)  return ['hier',            'is-past'];
            if ($d<30)  return ['il y a '.$d.' j', 'is-past'];
            if ($d<60)  return ['il y a ~1 mois',  'is-past'];
            return ['il y a '.floor($d/30).' mois','is-past'];
        }
        if ($diff==0) return ["aujourd'hui",'is-soon'];
        if ($diff<=7) return ['dans '.$d.' j','is-soon'];
        return ['dans '.$d.' j',''];
    }
}
?>

<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
<div class="nicdark_space30"></div>

<!-- EN-TÊTE -->
<div class="grid grid_12">
    <div class="aw-page-head">
        <div>
            <h1 class="subtitle greydark"><?php echo $this->lang->line('REF_MY_SESSIONS_TITLE'); ?></h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey"><?php echo $this->lang->line('REF_MY_SESSIONS_SUBTITLE'); ?></h3>
        </div>
        <!-- Toggle vue -->
        <div class="aw-view-toggle">
            <button class="aw-toggle-btn active" id="btn-cards" onclick="awSetView('cards')" title="Vue cartes">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="1" width="6" height="6" rx="1" fill="currentColor"/><rect x="9" y="1" width="6" height="6" rx="1" fill="currentColor"/><rect x="1" y="9" width="6" height="6" rx="1" fill="currentColor"/><rect x="9" y="9" width="6" height="6" rx="1" fill="currentColor"/></svg>
                Cartes
            </button>
            <button class="aw-toggle-btn" id="btn-list" onclick="awSetView('list')" title="Vue liste">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="5" y="2" width="10" height="2" rx="1" fill="currentColor"/><rect x="1" y="2" width="2" height="2" rx="0.5" fill="currentColor"/><rect x="5" y="7" width="10" height="2" rx="1" fill="currentColor"/><rect x="1" y="7" width="2" height="2" rx="0.5" fill="currentColor"/><rect x="5" y="12" width="10" height="2" rx="1" fill="currentColor"/><rect x="1" y="12" width="2" height="2" rx="0.5" fill="currentColor"/></svg>
                Liste
            </button>
        </div>
    </div>
    <div class="nicdark_space16"></div>
    <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
    <div class="nicdark_space20"></div>
</div>

<div class="grid grid_12"><div class="aw-root">

<?php if (empty($my_works)): ?>

    <p class="aw-empty"><?php echo $this->lang->line('REF_NO_SESSIONS'); ?></p>

<?php else: ?>

    <!-- MÉTRIQUES -->
    <div class="aw-stats">
        <div class="aw-stat"><div class="st-label">À venir</div><div class="st-value"><?php echo count($upcoming); ?></div></div>
        <div class="aw-stat"><div class="st-label">Aujourd'hui</div><div class="st-value"><?php echo $nb_aujourdhui; ?></div></div>
        <div class="aw-stat"><div class="st-label">Passées</div><div class="st-value"><?php echo count($past); ?></div></div>
        <div class="aw-stat"><div class="st-label">Total</div><div class="st-value"><?php echo count($my_works); ?></div></div>
    </div>

    <!-- FILTRES (communs aux deux vues) -->
    <div class="aw-filters-bar">
        <div class="aw-search-wrap">
            <i class="icon-search"></i>
            <input type="text" class="aw-search" placeholder="Rechercher…" oninput="awSearch(this.value)">
        </div>
        <a href="#" class="aw-chip is-active" onclick="awFilter(this,'*');return false;">Tous</a>
        <?php foreach ($WorkType as $key => $value): ?>
            <a href="#" class="aw-chip" onclick="awFilter(this,'type:<?php echo $key; ?>');return false;"><?php echo $value; ?></a>
        <?php endforeach; ?>
    </div>

    <!-- ================================================================
         SECTION À VENIR
         ================================================================ -->
    <div class="aw-section-head upcoming">
        <span class="sh-dot"></span>
        <h2>À venir</h2>
        <span class="sh-count"><?php echo count($upcoming); ?></span>
        <span class="sh-line"></span>
    </div>

    <?php if (empty($upcoming)): ?>
        <p class="aw-empty">Aucune session à animer pour le moment.</p>
    <?php else: ?>

    <!-- ---- VUE CARTES ---- -->
    <div id="aw-view-cards">
    <div class="aw-cards-grid">
    <?php foreach ($upcoming as $w):
        $design  = $this->render_object->GetDesign($w->type);
        list($cd,$cd_cls) = _v5_countdown($w->date_travaux);
        $bl      = isset($design->title) ? $design->title : $w->type;
        $is_open = (strtotime($w->date_travaux) <= strtotime('today'));  // jour J ou après
    ?>
        <div class="aw-card"
             data-type="<?php echo $w->type; ?>"
             data-title="<?php echo htmlspecialchars(strtolower($w->titre)); ?>"
             data-ecole="<?php echo htmlspecialchars(strtolower($w->ecole)); ?>">
            <div class="aw-card-inner <?php echo _v5_stripe($w->type); ?>">
                <div class="aw-card-body">
                    <div class="aw-card-dateline">
                        <span class="aw-card-date"><?php
                            $jours = ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'];
                            $ts = strtotime($w->date_travaux);
                            echo $jours[(int)date('w',$ts)].' '.date('j',$ts).' '.$mois_abr[(int)date('n',$ts)];
                        ?></span>
                        <span class="aw-countdown <?php echo $cd_cls; ?>"><?php echo $cd; ?></span>
                    </div>
                    <p class="aw-card-title"><?php echo htmlspecialchars($w->titre); ?></p>
                    <span class="aw-type-badge <?php echo _v5_badge_cls($w->type); ?>"><?php echo $bl; ?></span>
                    <div class="aw-card-meta">
                        <div class="aw-meta-row"><i class="icon-pin-outline"></i><?php echo $this->render_object->RenderElement('ecole',$w->ecole); ?></div>
                        <?php if ($w->type_session==1): ?>
                        <div class="aw-meta-row"><i class="icon-clock-1"></i><?php echo $w->heure_deb_trav; ?> – <?php echo $w->heure_fin_trav; ?></div>
                        <?php endif; ?>
                        <div class="aw-meta-row"><i class="icon-info-outline"></i><?php echo $this->render_object->RenderElement('type_session',$w->type_session); ?></div>
                    </div>
                </div>
                <div class="aw-card-footer">
                    <span class="aw-slots">
                        <strong><?php echo (int)(isset($w->participant)?$w->participant:0); ?></strong> / <?php echo (int)$w->nb_inscrits_max; ?>
                    </span>
                    <div class="aw-footer-actions">
                        <?php if ($is_open): ?>
                            <a href="<?php echo base_url('Admwork_controller/validate_one/'.$w->id); ?>" class="aw-btn validate">
                                <?php echo $this->lang->line('REF_VALIDATE_ACTION'); ?>
                            </a>
                        <?php else: ?>
                            <a href="<?php echo base_url('Admwork_controller/validate_one/'.$w->id); ?>" class="aw-btn">
                                <?php echo $this->lang->line('REF_PREVIEW_ACTION'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    </div><!-- /.aw-cards-grid -->
    </div><!-- /#aw-view-cards -->

    <!-- ---- VUE LISTE ---- -->
    <div id="aw-view-list" style="display:none;">
    <div class="aw-list">
    <?php foreach ($upcoming_by_month as $mkey => $mdata): ?>
        <div class="aw-list-sep" data-month="<?php echo $mkey; ?>">
            <span class="aw-list-sep-label"><?php echo $mdata['label']; ?></span>
            <span class="aw-list-sep-count"><?php echo count($mdata['works']); ?> session<?php echo count($mdata['works'])>1?'s':''; ?></span>
        </div>
        <?php foreach ($mdata['works'] as $w):
            $design  = $this->render_object->GetDesign($w->type);
            list($cd,$cd_cls) = _v5_countdown($w->date_travaux);
            $bl      = isset($design->title) ? $design->title : $w->type;
            $is_open = (strtotime($w->date_travaux) <= strtotime('today'));
        ?>
        <div class="aw-list-row"
             data-type="<?php echo $w->type; ?>"
             data-title="<?php echo htmlspecialchars(strtolower($w->titre)); ?>"
             data-ecole="<?php echo htmlspecialchars(strtolower($w->ecole)); ?>">
            <div class="aw-list-date">
                <span class="aw-list-day"><?php echo _v5_day($w->date_travaux); ?></span>
                <span class="aw-list-mon"><?php echo _v5_mon($w->date_travaux,$mois_abr); ?></span>
            </div>
            <div class="aw-list-stripe <?php echo _v5_stripe($w->type); ?>"></div>
            <div class="aw-list-info">
                <div class="aw-list-title"><?php echo htmlspecialchars($w->titre); ?></div>
                <div class="aw-list-meta">
                    <span><?php echo $this->render_object->RenderElement('ecole',$w->ecole); ?></span>
                    <?php if ($w->type_session==1): ?>
                        <span class="aw-list-sep-dot">·</span><span><?php echo $w->heure_deb_trav; ?>–<?php echo $w->heure_fin_trav; ?></span>
                    <?php endif; ?>
                    <span class="aw-list-sep-dot">·</span>
                    <span class="aw-list-badge <?php echo _v5_badge_cls($w->type); ?>"><?php echo $bl; ?></span>
                </div>
            </div>
            <div class="aw-list-slots">
                <span class="aw-slots-num"><strong><?php echo (int)(isset($w->participant)?$w->participant:0); ?></strong>/<?php echo (int)$w->nb_inscrits_max; ?></span>
            </div>
            <div class="aw-list-cd <?php echo $cd_cls; ?>"><?php echo $cd; ?></div>
            <div class="aw-list-action">
                <?php if ($is_open): ?>
                    <a href="<?php echo base_url('Admwork_controller/validate_one/'.$w->id); ?>" class="aw-btn validate">
                        <?php echo $this->lang->line('REF_VALIDATE_ACTION'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo base_url('Admwork_controller/validate_one/'.$w->id); ?>" class="aw-btn">
                        <?php echo $this->lang->line('REF_PREVIEW_ACTION'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    </div><!-- /.aw-list -->
    </div><!-- /#aw-view-list -->

    <?php endif; /* end if empty upcoming */ ?>

    <!-- MARQUEUR -->
    <?php if (!empty($upcoming) && !empty($past)): ?>
    <div class="aw-today-marker">
        <span class="tm-line"></span>
        <i class="icon-calendar"></i>&nbsp;Aujourd'hui · <?php echo date('d/m/Y'); ?>
        <span class="tm-line"></span>
    </div>
    <?php endif; ?>

    <!-- ================================================================
         PASSÉES — accordéons par mois
         ================================================================ -->
    <div class="aw-section-head past">
        <span class="sh-dot"></span>
        <h2>Sessions passées</h2>
        <span class="sh-count"><?php echo count($past); ?></span>
        <span class="sh-line"></span>
    </div>

    <?php if (empty($past_by_month)): ?>
        <p class="aw-empty">Aucune session passée.</p>
    <?php else: ?>
    <div class="aw-accordion-past">
    <?php $midx=0; foreach ($past_by_month as $mkey => $mdata): $first=($midx===0); ?>

        <div class="aw-month-header <?php echo $first?'is-open':''; ?>" onclick="awToggleMonth('month-<?php echo $mkey; ?>',this)">
            <div class="aw-month-title">
                <i class="icon-calendar"></i>
                <?php echo $mdata['label']; ?>
                <span class="aw-month-badge"><?php echo count($mdata['works']); ?> session<?php echo count($mdata['works'])>1?'s':''; ?></span>
            </div>
            <i class="aw-month-chevron icon-down-open"></i>
        </div>

        <div class="aw-month-body <?php echo $first?'is-open':''; ?>" id="month-<?php echo $mkey; ?>">
        <?php foreach ($mdata['works'] as $w):
            $design = $this->render_object->GetDesign($w->type);
            list($cd,$cd_cls) = _v5_countdown($w->date_travaux);
            $bl     = isset($design->title)?$design->title:$w->type;
        ?>
            <div class="aw-list-row is-past"
                 data-type="<?php echo $w->type; ?>"
                 data-title="<?php echo htmlspecialchars(strtolower($w->titre)); ?>"
                 data-ecole="<?php echo htmlspecialchars(strtolower($w->ecole)); ?>">
                <div class="aw-list-date">
                    <span class="aw-list-day"><?php echo _v5_day($w->date_travaux); ?></span>
                    <span class="aw-list-mon"><?php echo _v5_mon($w->date_travaux,$mois_abr); ?></span>
                </div>
                <div class="aw-list-stripe <?php echo _v5_stripe($w->type); ?>"></div>
                <div class="aw-list-info">
                    <div class="aw-list-title"><?php echo htmlspecialchars($w->titre); ?></div>
                    <div class="aw-list-meta">
                        <span><?php echo $this->render_object->RenderElement('ecole',$w->ecole); ?></span>
                        <?php if ($w->type_session==1): ?>
                            <span class="aw-list-sep-dot">·</span><span><?php echo $w->heure_deb_trav; ?>–<?php echo $w->heure_fin_trav; ?></span>
                        <?php endif; ?>
                        <span class="aw-list-sep-dot">·</span>
                        <span class="aw-list-badge <?php echo _v5_badge_cls($w->type); ?>"><?php echo $bl; ?></span>
                    </div>
                </div>
                <div class="aw-list-slots">
                    <span class="aw-slots-num"><strong><?php echo (int)(isset($w->participant)?$w->participant:0); ?></strong>/<?php echo (int)$w->nb_inscrits_max; ?></span>
                </div>
                <div class="aw-list-cd is-past"><?php echo $cd; ?></div>
                <div class="aw-list-action">
                    <a href="<?php echo base_url('Admwork_controller/validate_one/'.$w->id); ?>" class="aw-btn validate">
                        <?php echo $this->lang->line('REF_VALIDATE_ACTION'); ?>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    <?php $midx++; endforeach; ?>
    </div><!-- /.aw-accordion-past -->
    <?php endif; ?>

<?php endif; /* end if empty my_works */ ?>

</div></div><!-- /.aw-root /.grid -->
<div class="nicdark_space50"></div>
</div><!-- /.nicdark_container -->
</section>
