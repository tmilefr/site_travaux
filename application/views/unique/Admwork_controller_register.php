<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
 * application/views/unique/Admwork_controller_register.php — v3
 *
 * CORRECTIONS v3 :
 *  · Code métier intégré (RenderElement sur champs bruts, pas de fonction statique)
 *  · Largeur des cartes uniforme (inline-block + width calc)
 *  · FullCalendar chargé en <head> (via controller) et non en bas de page
 *  · Pas de conflit float — wrapper .aw-root
 */

/* -----------------------------------------------------------------------
   PRÉPARATION DES DONNÉES
   ----------------------------------------------------------------------- */
$upcoming         = [];
$past             = [];
$nb_dispo_total   = 0;
$nb_my_registered = 0;

foreach ($works as $w) {
    if ($w->delay > 0) {
        $past[] = $w;
    } else {
        $upcoming[] = $w;
        if ($w->register) {
            $nb_dispo_total += max(0, (int)$w->nb_inscrits_max - (int)$w->participant);
        }
    }
    if (!empty($w->already_registred)) $nb_my_registered++;
}
usort($upcoming, function($a, $b){ return strcmp($a->date_travaux, $b->date_travaux); });
usort($past,     function($a, $b){ return strcmp($b->date_travaux, $a->date_travaux); });

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

/* JSON pour FullCalendar */
$nicdark_hex = [
    'nicdark_bg_green'    => '#6fc191',
    'nicdark_bg_blue'     => '#74cee4',
    'nicdark_bg_violet'   => '#c389ce',
    'nicdark_bg_orange'   => '#ec774b',
    'nicdark_bg_red'      => '#e16c6c',
    'nicdark_bg_yellow'   => '#edbf47',
    'nicdark_bg_greendark'=> '#6ab78a',
    'nicdark_bg_greydark' => '#495052',
];
$cal_events = [];
foreach ($works as $w) {
    $d   = $this->render_object->GetDesign($w->type);
    $hex = isset($nicdark_hex[$d->color]) ? $nicdark_hex[$d->color] : '#74cee4';
    $cal_events[] = [
        'id'    => (int)$w->id,
        'title' => $w->titre,
        'start' => $w->date_travaux,
        'color' => $hex,
        'extendedProps' => [
            'ecole'    => $w->ecole,
            'type_lbl' => isset($d->title) ? $d->title : $w->type,
            'heure_deb'=> ($w->type_session == 1) ? $w->heure_deb_trav : null,
            'heure_fin'=> ($w->type_session == 1) ? $w->heure_fin_trav  : null,
            'inscrits' => (int)$w->participant,
            'max'      => (int)$w->nb_inscrits_max,
            'is_past'  => ($w->delay > 0),
            'url'      => base_url('Admwork_controller/register_one/'.$w->id),
            'url_adm'  => ($this->acl->getType() === 'sys')
                            ? base_url('Admwork_controller/managed_one/'.$w->id) : null,
        ],
    ];
}

/* Helpers locaux (fermés dans cette vue, pas de conflit) */
$jours_fr  = ['Dim.','Lun.','Mar.','Mer.','Jeu.','Ven.','Sam.'];
$mois_abr  = ['','janv.','févr.','mars','avr.','mai','juin','juil.','août','sept.','oct.','nov.','déc.'];

function _aw3_date_fr($date_travaux, $jours_fr, $mois_abr) {
    $ts = strtotime($date_travaux);
    return $jours_fr[(int)date('w',$ts)].' '.date('j',$ts).' '.$mois_abr[(int)date('n',$ts)];
}
function _aw3_countdown($delay) {
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
function _aw3_stripe($type) {
    return 'aw-stripe-'.(in_array($type,['TRA','MEN','INF','GOU','LAV','DEC','URG']) ? $type : 'default');
}
function _aw3_badge($type) {
    return 'aw-badge-'.(in_array($type,['TRA','MEN','INF','GOU','LAV','DEC','URG']) ? $type : 'default');
}
$acl_type = $this->acl->getType();
?>

<!--start section-->
<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
<div class="nicdark_space30"></div>

<!-- ===== EN-TÊTE + TOGGLE ===== -->
<div class="grid grid_12">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 class="subtitle greydark"><?php echo $this->lang->line('work_planned'); ?></h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey"><?php echo $this->lang->line('work_planned_subtitle'); ?></h3>
        </div>
        <div style="padding-top:4px;">
            <div class="view-toggle">
                <a href="#" id="btn-view-cards"    class="active" onclick="awSwitchView('cards');return false;"><i class="icon-layout"></i> <span>Cartes</span></a>
                <a href="#" id="btn-view-calendar"            onclick="awSwitchView('calendar');return false;"><i class="icon-calendar"></i> <span>Calendrier</span></a>
            </div>
        </div>
    </div>
    <div class="nicdark_space20"></div>
    <div class="nicdark_divider left big"><span class="nicdark_bg_green nicdark_radius"></span></div>
    <div class="nicdark_space20"></div>
</div>

<!-- ===== WRAPPER PRINCIPAL (neutralise les floats nicdark) ===== -->
<div class="grid grid_12">
<div class="aw-root">

    <!-- MÉTRIQUES -->
    <div class="aw-stats">
        <div class="aw-stat"><div class="st-label">À venir</div><div class="st-value"><?php echo count($upcoming); ?></div></div>
        <div class="aw-stat"><div class="st-label">Places dispo</div><div class="st-value"><?php echo $nb_dispo_total; ?></div></div>
        <div class="aw-stat"><div class="st-label">Mes inscriptions</div><div class="st-value"><?php echo $nb_my_registered; ?></div></div>
        <div class="aw-stat"><div class="st-label">Passées</div><div class="st-value"><?php echo count($past); ?></div></div>
    </div>

    <!-- ================================================================
         VUE CARTES
         ================================================================ -->
    <div id="view-cards">

        <!-- Filtres -->
        <div class="aw-filters-bar">
            <div class="aw-search-wrap">
                <i class="icon-search"></i>
                <input type="text" class="aw-search" placeholder="Rechercher…" oninput="awSearch(this.value)">
            </div>
            <a href="#" class="aw-chip is-active" onclick="awFilter(this,'*');return false;">Tous</a>
            <a href="#" class="aw-chip"            onclick="awFilter(this,'dispo');return false;">Disponibles</a>
            <a href="#" class="aw-chip"            onclick="awFilter(this,'mine');return false;">Mes inscriptions</a>
            <a href="#" class="aw-chip"            onclick="awFilter(this,'archived');return false;">Passées</a>
            <?php foreach ($WorkType as $key => $value): ?>
                <a href="#" class="aw-chip" onclick="awFilter(this,'<?php echo $key; ?>');return false;"><?php echo $value; ?></a>
            <?php endforeach; ?>
        </div>

        <!-- ---- À VENIR ---- -->
        <div class="aw-section-head upcoming">
            <span class="sh-dot"></span>
            <h2>À venir</h2>
            <span class="sh-count"><?php echo count($upcoming); ?></span>
            <span class="sh-line"></span>
        </div>

        <?php if (empty($upcoming)): ?>
            <p style="color:#aaa;font-style:italic;padding:16px 0;">Aucune session à venir pour le moment.</p>
        <?php else: ?>
            <div class="aw-cards-grid">
            <?php foreach ($upcoming as $w):
                $design = $this->render_object->GetDesign($w->type);
                list($cd, $cd_cls) = _aw3_countdown($w->delay);
                $mine = !empty($w->already_registred) ? ' mine' : '';
                $dispo = $w->register ? ' dispo' : '';
            ?>
                <div class="aw-card<?php echo $mine.$dispo; ?>" data-type="<?php echo $w->type; ?>" data-title="<?php echo htmlspecialchars(strtolower($w->titre)); ?>" data-ecole="<?php echo htmlspecialchars(strtolower($w->ecole)); ?>">
                    <div class="aw-card-inner <?php echo _aw3_stripe($w->type); ?>">
                        <div class="aw-card-body">
                            <div class="aw-card-dateline">
                                <span class="aw-card-date"><?php echo _aw3_date_fr($w->date_travaux, $jours_fr, $mois_abr); ?></span>
                                <span class="aw-countdown <?php echo $cd_cls; ?>"><?php echo $cd; ?></span>
                            </div>
                            <p class="aw-card-title"><?php echo htmlspecialchars($w->titre); ?></p>
                            <span class="aw-type-badge <?php echo _aw3_badge($w->type); ?>"><?php echo isset($design->title) ? $design->title : $w->type; ?></span>
                            <div class="aw-card-meta">
                                <div class="aw-meta-row"><i class="icon-pin-outline"></i><?php echo $this->render_object->RenderElement('ecole', $w->ecole); ?></div>
                                <?php if ($w->type_session == 1): ?>
                                <div class="aw-meta-row"><i class="icon-clock-1"></i><?php echo $w->heure_deb_trav; ?> – <?php echo $w->heure_fin_trav; ?></div>
                                <?php endif; ?>
                                <div class="aw-meta-row"><i class="icon-info-outline"></i><?php echo $this->render_object->RenderElement('type_session', $w->type_session); ?></div>
                            </div>
                        </div>
                        <div class="aw-card-footer">
                            <span class="aw-slots">
                                <strong><?php echo (int)$w->participant; ?></strong> / <?php echo (int)$w->nb_inscrits_max; ?>
                                <?php if (!empty($w->already_registred)): ?>&nbsp;<span class="aw-badge-inscrit">&#10003; Inscrit</span><?php endif; ?>
                            </span>
                            <div class="aw-footer-actions">
                                <?php if ($acl_type === 'sys'): ?>
                                    <a href="<?php echo base_url('Admwork_controller/managed_one/'.$w->id); ?>" class="aw-btn adm"><?php echo $this->lang->line('ADM_WORK'); ?></a>
                                <?php elseif ($w->register): ?>
                                    <a href="<?php echo base_url('Admwork_controller/register_one/'.$w->id); ?>" class="aw-btn<?php echo !empty($w->already_registred) ? ' inscrit' : ''; ?>">
                                        <?php echo !empty($w->already_registred) ? '&#10003; '.$this->lang->line('SEE_YOUR_REGISTRED_WORK') : $this->lang->line('REGISTER_WORK'); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="aw-btn complet">Complet</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div><!-- /.aw-cards-grid upcoming -->
        <?php endif; ?>

        <!-- ---- MARQUEUR AUJOURD'HUI ---- -->
        <?php if (!empty($upcoming) && !empty($past)): ?>
        <div class="aw-today-marker">
            <span class="tm-line"></span>
            <i class="icon-calendar"></i>&nbsp;Aujourd'hui · <?php echo date('d/m/Y'); ?>
            <span class="tm-line"></span>
        </div>
        <?php endif; ?>

        <!-- ---- PASSÉES : accordéons par mois ---- -->
        <div class="aw-section-head past">
            <span class="sh-dot"></span>
            <h2>Sessions passées</h2>
            <span class="sh-count"><?php echo count($past); ?></span>
            <span class="sh-line"></span>
        </div>

        <?php if (empty($past_by_month)): ?>
            <p style="color:#aaa;font-style:italic;padding:16px 0;">Aucune session passée.</p>
        <?php else: ?>
            <div class="aw-accordion-past">
            <?php $midx = 0; foreach ($past_by_month as $mkey => $mdata): $first = ($midx === 0); ?>

                <div class="aw-month-header <?php echo $first ? 'is-open' : ''; ?>" onclick="awToggleMonth('month-<?php echo $mkey; ?>',this)">
                    <div class="aw-month-title">
                        <i class="icon-calendar"></i>
                        <?php echo $mdata['label']; ?>
                        <span class="aw-month-badge"><?php echo count($mdata['works']); ?> session<?php echo count($mdata['works'])>1?'s':''; ?></span>
                    </div>
                    <i class="aw-month-chevron icon-down-open"></i>
                </div>

                <div class="aw-month-body <?php echo $first ? 'is-open' : ''; ?>" id="month-<?php echo $mkey; ?>">
                    <div class="aw-cards-grid">
                    <?php foreach ($mdata['works'] as $w):
                        $design = $this->render_object->GetDesign($w->type);
                        list($cd, $cd_cls) = _aw3_countdown($w->delay);
                        $mine = !empty($w->already_registred) ? ' mine' : '';
                    ?>
                        <div class="aw-card is-past archived<?php echo $mine; ?>" data-type="<?php echo $w->type; ?>" data-title="<?php echo htmlspecialchars(strtolower($w->titre)); ?>" data-ecole="<?php echo htmlspecialchars(strtolower($w->ecole)); ?>">
                            <div class="aw-card-inner <?php echo _aw3_stripe($w->type); ?>">
                                <div class="aw-card-body">
                                    <div class="aw-card-dateline">
                                        <span class="aw-card-date"><?php echo _aw3_date_fr($w->date_travaux, $jours_fr, $mois_abr); ?></span>
                                        <span class="aw-countdown is-past"><?php echo $cd; ?></span>
                                    </div>
                                    <p class="aw-card-title"><?php echo htmlspecialchars($w->titre); ?></p>
                                    <span class="aw-type-badge <?php echo _aw3_badge($w->type); ?>"><?php echo isset($design->title) ? $design->title : $w->type; ?></span>
                                    <div class="aw-card-meta">
                                        <div class="aw-meta-row"><i class="icon-pin-outline"></i><?php echo $this->render_object->RenderElement('ecole', $w->ecole); ?></div>
                                        <?php if ($w->type_session == 1): ?>
                                        <div class="aw-meta-row"><i class="icon-clock-1"></i><?php echo $w->heure_deb_trav; ?> – <?php echo $w->heure_fin_trav; ?></div>
                                        <?php endif; ?>
                                        <div class="aw-meta-row"><i class="icon-info-outline"></i><?php echo $this->render_object->RenderElement('type_session', $w->type_session); ?></div>
                                    </div>
                                </div>
                                <div class="aw-card-footer">
                                    <span class="aw-slots">
                                        <strong><?php echo (int)$w->participant; ?></strong> / <?php echo (int)$w->nb_inscrits_max; ?>
                                        <?php if (!empty($w->already_registred)): ?>&nbsp;<span class="aw-badge-inscrit">&#10003;</span><?php endif; ?>
                                        <?php if (empty($w->already_registred)): ?><span style="font-size:12px;color:#bbb;"> · Non inscrit</span><?php endif; ?>
                                    </span>
                                    <div class="aw-footer-actions">
                                        <?php if ($acl_type === 'sys'): ?>
                                            <a href="<?php echo base_url('Admwork_controller/managed_one/'.$w->id); ?>" class="aw-btn adm"><?php echo $this->lang->line('ADM_WORK'); ?></a>
                                        <?php else: ?>
                                            <a href="<?php echo base_url('Admwork_controller/register_one/'.$w->id); ?>" class="aw-btn details">Détails</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div><!-- /.aw-cards-grid past -->
                </div><!-- /.aw-month-body -->

            <?php $midx++; endforeach; ?>
            </div><!-- /.aw-accordion-past -->
        <?php endif; ?>

    </div><!-- /#view-cards -->

    <!-- ================================================================
         VUE CALENDRIER
         ================================================================ -->
    <div id="view-calendar">
        <div id="aw-fullcalendar" style="padding-top:16px;"></div>
        <div class="nicdark_space30"></div>
    </div>

</div><!-- /.aw-root -->
</div><!-- /.grid.grid_12 -->

<div class="nicdark_space50"></div>
</div><!-- /.nicdark_container -->
</section>

<!-- Données calendrier (inline, disponibles immédiatement) -->
<script>
window.awCalendarEvents = <?php echo json_encode(array_values($cal_events), JSON_UNESCAPED_UNICODE); ?>;
</script>
