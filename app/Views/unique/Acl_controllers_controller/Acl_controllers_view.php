<?php
/**
 * Vue "carte" pour la liste des contrôleurs ACL.
 * -----------------------------------------------
 * Cette vue est rendue UNE FOIS PAR LIGNE par list_view.php
 * (cf. MY_Controller::list() + render_view()).
 *
 * Variables disponibles fournies par le moteur :
 *   - $render_object : la ligne courante est posée via _set('dba_data')
 *   - $bootstrap_tools, tr()
 *
 * Ergonomie (refonte étape 1) :
 *   - Header : nom du contrôleur + 2 badges (nb actions, nb rôles utilisateurs)
 *   - Body  : actions séparées en 2 lignes "Standard" et "Métier"
 *             - Standard = CRUD reconnu, pills discrets (lecture rapide)
 *             - Métier   = actions custom, pills mis en avant (focus visuel)
 *   - Footer : mini-stats de couverture ACL + menu d'actions
 */

// --- Récupération du contrôleur courant et de ses actions --------------------
$dba       = $render_object->_get('dba_data');
$ctrl_name = isset($dba->controller) ? $dba->controller : '';
$ctrl_id   = isset($dba->id) ? (int) $dba->id : 0;

$actions = array();
if ($ctrl_id && true) {
    model('Acl_actions_model')->_set('filter', array('id_ctrl' => $ctrl_id));
    model('Acl_actions_model')->_set('order', 'action');
    model('Acl_actions_model')->_set('direction', 'asc');
    $actions = model('Acl_actions_model')->get_all();
}
$nb_actions = is_array($actions) ? count($actions) : 0;

// --- Mapping des actions CRUD standard ---------------------------------------
// Toute action présente dans cette map est considérée "standard".
$crud_map = array(
    'list'      => array('icon' => 'oi-list',   'class' => 'badge-secondary'),
    'view'      => array('icon' => 'oi-eye',    'class' => 'badge-secondary'),
    'add'       => array('icon' => 'oi-plus',   'class' => 'badge-primary'),
    'edit'      => array('icon' => 'oi-pencil', 'class' => 'badge-primary'),
    'delete'    => array('icon' => 'oi-trash',  'class' => 'badge-danger'),
    'set_rules' => array('icon' => 'oi-key',    'class' => 'badge-warning'),
);
$default_pill = array('icon' => 'oi-cog', 'class' => 'badge-success');

// --- Tri des actions en 2 groupes : standard / métier ------------------------
$std_actions    = array();
$custom_actions = array();
if (is_array($actions)) {
    foreach ($actions as $a) {
        $name = isset($a->action) ? (string) $a->action : '';
        $key  = strtolower($name);
        if (isset($crud_map[$key])) {
            $std_actions[] = array('obj' => $a, 'name' => $name, 'cfg' => $crud_map[$key]);
        } else {
            $custom_actions[] = array('obj' => $a, 'name' => $name, 'cfg' => $default_pill);
        }
    }
}
$nb_std    = count($std_actions);
$nb_custom = count($custom_actions);

// --- Bandeau couleur (cohérent avec _bg_color du controller) -----------------
$header_color = 'nicdark_bg_red';

// --- Libellés ----------------------------------------------------------------
$lbl_actions  = tr('actions') ?: 'actions';
$lbl_standard = tr('ACL_LBL_STANDARD') ?: 'Standard';
$lbl_business = tr('ACL_LBL_BUSINESS') ?: 'Métier';
$lbl_no_action = tr('NO_ACTION_DEFINED')
    ?: 'Aucune action définie pour ce contrôleur.';
$lbl_no_custom = tr('ACL_LBL_NO_CUSTOM')
    ?: 'Aucune action métier — uniquement du CRUD standard.';
?>
<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
<div class="card acl-ctrl-card mb-3 shadow-sm"
     data-ctrl-name="<?php echo htmlspecialchars(strtolower($ctrl_name), ENT_QUOTES, 'UTF-8'); ?>"
     data-nb-actions="<?php echo $nb_actions; ?>"
     data-nb-custom="<?php echo $nb_custom; ?>">

    <div class="card-header acl-ctrl-card__header <?php echo $header_color; ?>">
        <div class="acl-ctrl-card__title">
            <span class="oi oi-layers" aria-hidden="true"></span>
            <span class="acl-ctrl-card__name">
                <?php echo htmlspecialchars($ctrl_name, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
        <div class="acl-ctrl-card__meta">
            <span class="badge badge-light acl-ctrl-card__count"
                  title="<?php echo htmlspecialchars($lbl_actions . ' : ' . $nb_actions, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="oi oi-bolt" aria-hidden="true"></span>
                <?php echo $nb_actions; ?>
            </span>
        </div>
    </div>

    <div class="card-body acl-ctrl-card__body">

        <?php if ($nb_actions === 0) { ?>

            <p class="text-muted font-italic mb-0">
                <span class="oi oi-info" aria-hidden="true"></span>
                <?php echo $lbl_no_action; ?>
            </p>

        <?php } else { ?>

            <?php /* ------------ Ligne STANDARD ------------------------- */ ?>
            <?php if ($nb_std > 0) { ?>
            <div class="acl-ctrl-card__row">
                <span class="acl-ctrl-card__row-label"><?php echo $lbl_standard; ?></span>
                <div class="acl-ctrl-card__pills acl-ctrl-card__pills--std">
                    <?php foreach ($std_actions as $entry) { ?>
                        <span class="badge acl-pill acl-pill--std <?php echo $entry['cfg']['class']; ?>"
                              title="<?php echo htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="oi <?php echo $entry['cfg']['icon']; ?>" aria-hidden="true"></span>
                            <?php echo htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <?php /* ------------ Ligne MÉTIER --------------------------- */ ?>
            <div class="acl-ctrl-card__row">
                <span class="acl-ctrl-card__row-label"><?php echo $lbl_business; ?></span>
                <div class="acl-ctrl-card__pills acl-ctrl-card__pills--custom">
                    <?php if ($nb_custom > 0) { ?>
                        <?php foreach ($custom_actions as $entry) { ?>
                            <span class="badge acl-pill acl-pill--custom <?php echo $entry['cfg']['class']; ?>"
                                  title="<?php echo htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <span class="oi <?php echo $entry['cfg']['icon']; ?>" aria-hidden="true"></span>
                                <?php echo htmlspecialchars($entry['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                        <?php } ?>
                    <?php } else { ?>
                        <span class="acl-ctrl-card__empty-custom">
                            <span class="oi oi-minus" aria-hidden="true"></span>
                            <?php echo $lbl_no_custom; ?>
                        </span>
                    <?php } ?>
                </div>
            </div>

        <?php } ?>

    </div>

    <div class="card-footer acl-ctrl-card__footer">
        <div class="acl-ctrl-card__stats">
            <span class="acl-ctrl-card__stat" title="<?php echo $lbl_standard; ?>">
                <span class="oi oi-cog" aria-hidden="true"></span>
                <?php echo $nb_std; ?>&nbsp;<?php echo $lbl_standard; ?>
            </span>
            <span class="acl-ctrl-card__stat acl-ctrl-card__stat--accent"
                  title="<?php echo $lbl_business; ?>">
                <span class="oi oi-bolt" aria-hidden="true"></span>
                <?php echo $nb_custom; ?>&nbsp;<?php echo $lbl_business; ?>
            </span>
        </div>
        <div class="acl-ctrl-card__menu">
            <?php echo $render_object->render_element_menu(); ?>
        </div>
    </div>

</div>
</div>
</section>

<style>
/* Habillage local — peut être déplacé dans assets/css/acl_controllers.css */
.acl-ctrl-card { border-radius: 6px; overflow: hidden; }

/* ---------- Header ---------- */
.acl-ctrl-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
    gap: 0.75rem;
}
.acl-ctrl-card__title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    min-width: 0; /* permet l'ellipsis si nom long */
}
.acl-ctrl-card__name {
    font-size: 1.05rem;
    letter-spacing: 0.2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.acl-ctrl-card__meta {
    display: flex;
    gap: 0.4rem;
    flex-shrink: 0;
}
.acl-ctrl-card__count {
    font-size: 0.85rem;
    padding: 0.35em 0.6em;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}

/* ---------- Body : 2 lignes Standard / Métier ---------- */
.acl-ctrl-card__body { padding-top: 0.85rem; padding-bottom: 0.85rem; }
.acl-ctrl-card__row {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}
.acl-ctrl-card__row + .acl-ctrl-card__row { margin-top: 0.55rem; }
.acl-ctrl-card__row-label {
    flex: 0 0 70px;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #8a8a8a;
    font-weight: 600;
    padding-top: 0.45em;
}
.acl-ctrl-card__pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    flex: 1 1 auto;
    min-width: 0;
}
.acl-ctrl-card__empty-custom {
    color: #b0b0b0;
    font-size: 0.85rem;
    font-style: italic;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding-top: 0.35em;
}

/* ---------- Pills ---------- */
.acl-pill {
    font-size: 0.85rem;
    padding: 0.45em 0.7em;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-weight: 500;
    text-transform: lowercase;
    border-radius: 999px;
}
/* Pills standard : visuellement plus discrets (pour ne pas voler la vedette au métier) */
.acl-pill--std { opacity: 0.85; }
.acl-pill--std:hover { opacity: 1; }
/* Pills métier : légèrement plus marqués */
.acl-pill--custom { box-shadow: 0 0 0 1px rgba(0,0,0,0.04); }

/* ---------- Footer ---------- */
.acl-ctrl-card__footer {
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.5rem 1rem;
}
.acl-ctrl-card__stats {
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
    color: #6c757d;
}
.acl-ctrl-card__stat {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.acl-ctrl-card__stat--accent { color: #28a745; font-weight: 500; }

/* ---------- Responsive ---------- */
@media (max-width: 600px) {
    .acl-ctrl-card__row { flex-direction: column; gap: 0.3rem; }
    .acl-ctrl-card__row-label { padding-top: 0; }
    .acl-ctrl-card__footer { flex-direction: column; align-items: flex-start; }
}
</style>
