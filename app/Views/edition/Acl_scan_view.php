<?php
/**
 * Vue de prévisualisation pour Acl_controllers_controller::scan()
 *
 * Affiche le diff entre :
 *   - les contrôleurs/actions trouvés dans application/controllers/*.php
 *   - les enregistrements présents en BDD
 *
 * L'utilisateur coche ce qu'il souhaite réellement créer puis valide.
 *
 * Variables fournies par le contrôleur :
 *   - $scan_diff      : { new_ctrls, new_actions, ok_ctrls, obsolete_ctrls, obsolete_actions }
 *   - $scan_summary   : { files_scanned, new_ctrls, new_actions, obsolete_ctrls, obsolete_acts }
 *   - $title
 */

$diff      = isset($scan_diff)    ? $scan_diff    : array();
$summary   = isset($scan_summary) ? $scan_summary : array();

$ctrl_route = 'Acl_controllers_controller';
$base_list  = base_url($ctrl_route . '/list');

$has_changes = (
    !empty($diff['new_ctrls']) ||
    !empty($diff['new_actions'])
);
?>

<section class="nicdark_section acl-scan">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <?php /* ============================================================
                EN-TETE
           ============================================================ */ ?>
        <div class="grid grid_12">
            <div class="acl-scan__header">
                <div>
                    <span class="badge badge-info acl-scan__mode">
                        <span class="oi oi-magnifying-glass" aria-hidden="true"></span>
                        <?php echo $this->lang->line('Acl_scan_label') ?: 'Scan ACL'; ?>
                    </span>
                    <h1 class="subtitle greydark">
                        <span class="oi oi-loop-circular" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                    </h1>
                </div>
                <a href="<?php echo $base_list; ?>" class="btn btn-light">
                    <span class="oi oi-arrow-left" aria-hidden="true"></span>
                    <?php echo $this->lang->line('LIST') ?: 'Retour à la liste'; ?>
                </a>
            </div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_red nicdark_radius"></span>
            </div>
        </div>

        <?php /* ============================================================
                FLASH MESSAGES (succès après application)
           ============================================================ */ ?>
        <?php if ($flash = $this->session->flashdata('bulk_success')) { ?>
            <div class="grid grid_12">
                <div class="alert alert-success">
                    <span class="oi oi-check" aria-hidden="true"></span>
                    <?php echo htmlspecialchars($flash, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            </div>
        <?php } ?>

        <?php /* ============================================================
                RESUME
           ============================================================ */ ?>
        <div class="grid grid_12 acl-scan__summary">
            <div class="acl-summary-pill">
                <span class="oi oi-file" aria-hidden="true"></span>
                <strong><?php echo (int) $summary['files_scanned']; ?></strong>
                <?php echo $this->lang->line('Acl_scan_files') ?: 'fichier(s) analysé(s)'; ?>
            </div>
            <div class="acl-summary-pill acl-summary-pill--success">
                <span class="oi oi-plus" aria-hidden="true"></span>
                <strong><?php echo (int) $summary['new_ctrls']; ?></strong>
                <?php echo $this->lang->line('Acl_scan_new_ctrls') ?: 'nouveau(x) contrôleur(s)'; ?>
            </div>
            <div class="acl-summary-pill acl-summary-pill--info">
                <span class="oi oi-bolt" aria-hidden="true"></span>
                <strong><?php echo (int) $summary['new_actions']; ?></strong>
                <?php echo $this->lang->line('Acl_scan_new_actions') ?: 'nouvelle(s) action(s)'; ?>
            </div>
            <div class="acl-summary-pill acl-summary-pill--warning">
                <span class="oi oi-warning" aria-hidden="true"></span>
                <strong><?php echo (int) ($summary['obsolete_ctrls'] + $summary['obsolete_acts']); ?></strong>
                <?php echo $this->lang->line('Acl_scan_obsolete') ?: 'élément(s) obsolète(s)'; ?>
            </div>
        </div>

        <?php /* ============================================================
                AUCUN CHANGEMENT DETECTE
           ============================================================ */ ?>
        <?php if (!$has_changes && empty($diff['obsolete_ctrls']) && empty($diff['obsolete_actions'])) { ?>
            <div class="grid grid_12">
                <div class="alert alert-success acl-scan__ok">
                    <span class="oi oi-check" aria-hidden="true"></span>
                    <strong><?php echo $this->lang->line('Acl_scan_in_sync') ?: 'La base ACL est synchronisée avec le code.'; ?></strong>
                </div>
            </div>
        <?php } ?>

        <?php /* ============================================================
                FORMULAIRE DE CONFIRMATION (ne s'affiche que s'il y a
                quelque chose à ajouter)
           ============================================================ */ ?>
        <?php if ($has_changes) { ?>

            <?php echo form_open($ctrl_route . '/scan', array('id' => 'acl-scan-form')); ?>
            <input type="hidden" name="confirm" value="1"/>

            <?php /* ---- Nouveaux contrôleurs ------------------------------ */ ?>
            <?php if (!empty($diff['new_ctrls'])) { ?>
                <div class="grid grid_12">
                    <div class="card shadow-sm acl-scan__card acl-scan__card--new-ctrls">
                        <div class="card-header acl-scan__card-header">
                            <span class="oi oi-plus" aria-hidden="true"></span>
                            <?php echo $this->lang->line('Acl_scan_new_ctrls_title')
                                ?: 'Contrôleurs absents de la base'; ?>
                            <span class="badge badge-success ml-2">
                                <?php echo count($diff['new_ctrls']); ?>
                            </span>
                            <button type="button"
                                    class="btn btn-sm btn-link acl-scan__select-all"
                                    data-target="new-ctrls">
                                <?php echo $this->lang->line('Acl_scan_select_all') ?: 'Tout cocher'; ?>
                            </button>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($diff['new_ctrls'] as $class => $methods) { ?>
                                <li class="list-group-item">
                                    <label class="acl-scan__row mb-0">
                                        <input type="checkbox"
                                               name="add_ctrl[]"
                                               value="<?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>"
                                               data-group="new-ctrls"
                                               data-class="<?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>"
                                               class="acl-scan__cb-ctrl"
                                               checked/>
                                        <span class="acl-scan__row-name">
                                            <span class="oi oi-layers" aria-hidden="true"></span>
                                            <strong><?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </span>
                                        <span class="acl-scan__row-meta text-muted">
                                            <?php echo count($methods); ?>
                                            <?php echo $this->lang->line('Acl_scan_actions_short') ?: 'action(s)'; ?>
                                        </span>
                                    </label>

                                    <?php if (!empty($methods)) { ?>
                                        <div class="acl-scan__methods">
                                            <?php foreach ($methods as $m) {
                                                $val = $class . '::' . $m; ?>
                                                <label class="acl-scan__method-pill">
                                                    <input type="checkbox"
                                                           name="add_action[]"
                                                           value="<?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>"
                                                           data-class="<?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?>"
                                                           class="acl-scan__cb-action"
                                                           checked/>
                                                    <span><?php echo htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?></span>
                                                </label>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>

            <?php /* ---- Actions manquantes sur contrôleurs existants ----- */ ?>
            <?php if (!empty($diff['new_actions'])) { ?>
                <div class="grid grid_12">
                    <div class="card shadow-sm acl-scan__card acl-scan__card--new-actions">
                        <div class="card-header acl-scan__card-header">
                            <span class="oi oi-bolt" aria-hidden="true"></span>
                            <?php echo $this->lang->line('Acl_scan_new_actions_title')
                                ?: 'Actions manquantes sur des contrôleurs existants'; ?>
                            <span class="badge badge-info ml-2">
                                <?php echo array_sum(array_map(function($x){ return count($x['actions']); }, $diff['new_actions'])); ?>
                            </span>
                            <button type="button"
                                    class="btn btn-sm btn-link acl-scan__select-all"
                                    data-target="new-actions">
                                <?php echo $this->lang->line('Acl_scan_select_all') ?: 'Tout cocher'; ?>
                            </button>
                        </div>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($diff['new_actions'] as $class => $info) { ?>
                                <li class="list-group-item">
                                    <div class="acl-scan__row-name">
                                        <span class="oi oi-layers" aria-hidden="true"></span>
                                        <strong><?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small class="text-muted">
                                            (id <?php echo (int) $info['id_ctrl']; ?>)
                                        </small>
                                    </div>
                                    <div class="acl-scan__methods">
                                        <?php foreach ($info['actions'] as $m) {
                                            $val = $class . '::' . $m; ?>
                                            <label class="acl-scan__method-pill">
                                                <input type="checkbox"
                                                       name="add_action[]"
                                                       value="<?php echo htmlspecialchars($val, ENT_QUOTES, 'UTF-8'); ?>"
                                                       data-group="new-actions"
                                                       class="acl-scan__cb-action"
                                                       checked/>
                                                <span><?php echo htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?></span>
                                            </label>
                                        <?php } ?>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>

            <?php /* ---- Actions de validation ---------------------------- */ ?>
            <div class="grid grid_12">
                <div class="acl-scan__actions">
                    <button type="submit" class="btn btn-primary">
                        <span class="oi oi-check" aria-hidden="true"></span>
                        <?php echo $this->lang->line('Acl_scan_apply') ?: 'Appliquer la sélection'; ?>
                    </button>
                    <a href="<?php echo $base_list; ?>" class="btn btn-link text-muted">
                        <?php echo $this->lang->line('CANCEL') ?: 'Annuler'; ?>
                    </a>
                </div>
            </div>

            <?php echo form_close(); ?>

        <?php } ?>

        <?php /* ============================================================
                ELEMENTS OBSOLETES (lecture seule, pour info)
           ============================================================ */ ?>
        <?php if (!empty($diff['obsolete_ctrls']) || !empty($diff['obsolete_actions'])) { ?>
            <div class="grid grid_12">
                <div class="card shadow-sm acl-scan__card acl-scan__card--obsolete">
                    <div class="card-header acl-scan__card-header acl-scan__card-header--warn">
                        <span class="oi oi-warning" aria-hidden="true"></span>
                        <?php echo $this->lang->line('Acl_scan_obsolete_title')
                            ?: 'Éléments en base sans équivalent dans le code'; ?>
                        <small class="text-muted ml-2">
                            <?php echo $this->lang->line('Acl_scan_obsolete_note')
                                ?: '(suppression manuelle pour ne casser aucune règle ACL)'; ?>
                        </small>
                    </div>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($diff['obsolete_ctrls'] as $class => $row) { ?>
                            <li class="list-group-item">
                                <span class="oi oi-layers text-muted" aria-hidden="true"></span>
                                <strong><?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <small class="text-muted">
                                    (id <?php echo (int) $row->id; ?> —
                                    <?php echo $this->lang->line('Acl_scan_no_php_file') ?: 'aucun fichier PHP correspondant'; ?>)
                                </small>
                                <a href="<?php echo base_url($ctrl_route . '/edit/' . (int) $row->id); ?>"
                                   class="btn btn-link btn-sm">
                                    <span class="oi oi-pencil"></span>
                                    <?php echo $this->lang->line('edit') ?: 'éditer'; ?>
                                </a>
                            </li>
                        <?php } ?>
                        <?php foreach ($diff['obsolete_actions'] as $class => $info) { ?>
                            <li class="list-group-item">
                                <span class="oi oi-layers text-muted" aria-hidden="true"></span>
                                <strong><?php echo htmlspecialchars($class, ENT_QUOTES, 'UTF-8'); ?></strong>
                                <small class="text-muted">
                                    — <?php echo $this->lang->line('Acl_scan_obsolete_methods') ?: 'méthodes absentes du code :'; ?>
                                </small>
                                <div class="acl-scan__methods">
                                    <?php foreach ($info['actions'] as $m) { ?>
                                        <span class="badge badge-warning">
                                            <?php echo htmlspecialchars($m, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php } ?>
                                </div>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>

    </div>
</section>

<script>
// Petit comportement "Tout cocher / décocher" par groupe
document.addEventListener('click', function(ev) {
    var btn = ev.target.closest('.acl-scan__select-all');
    if (!btn) return;
    var group = btn.getAttribute('data-target');
    var card = btn.closest('.card');
    if (!card) return;
    var boxes = card.querySelectorAll('input[type="checkbox"]');
    // Si tout est déjà coché → on décoche, sinon on coche tout.
    var allChecked = Array.prototype.every.call(boxes, function(b){ return b.checked; });
    Array.prototype.forEach.call(boxes, function(b){ b.checked = !allChecked; });
});

// Cohérence : décocher un contrôleur => décocher ses actions
document.addEventListener('change', function(ev) {
    var cb = ev.target;
    if (!cb.classList || !cb.classList.contains('acl-scan__cb-ctrl')) return;
    var cls = cb.getAttribute('data-class');
    if (!cls) return;
    var actions = document.querySelectorAll(
        '.acl-scan__cb-action[data-class="' + CSS.escape(cls) + '"]'
    );
    Array.prototype.forEach.call(actions, function(a){
        a.checked = cb.checked;
        a.disabled = !cb.checked;
    });
});
</script>

<style>
.acl-scan__header {
    display: flex; align-items: flex-start; justify-content: space-between;
    flex-wrap: wrap; gap: 1rem;
}
.acl-scan__mode {
    font-size: .85rem; padding: .4em .7em;
    display: inline-flex; align-items: center; gap: .35rem;
    margin-bottom: .4rem;
}

.acl-scan__summary {
    display: flex; flex-wrap: wrap; gap: .5rem;
    margin-bottom: 1rem;
}
.acl-summary-pill {
    background: #fff; padding: .5rem .85rem; border-radius: 999px;
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
    display: inline-flex; align-items: center; gap: .4rem;
    border-left: 3px solid #6c757d;
    font-size: .9rem;
}
.acl-summary-pill strong { font-size: 1.1rem; }
.acl-summary-pill--success { border-left-color: #28a745; }
.acl-summary-pill--info    { border-left-color: #17a2b8; }
.acl-summary-pill--warning { border-left-color: #ffc107; }

.acl-scan__card { margin-bottom: 1rem; }
.acl-scan__card-header {
    display: flex; align-items: center; gap: .5rem;
    background: #f8f9fa; font-weight: 600;
}
.acl-scan__card-header--warn { background: #fff3cd; }
.acl-scan__select-all { margin-left: auto; padding: 0; }

.acl-scan__row {
    display: flex; align-items: center; gap: .75rem; cursor: pointer;
    width: 100%;
}
.acl-scan__row input[type="checkbox"] { transform: scale(1.1); }
.acl-scan__row-name { flex: 1; display: inline-flex; align-items: center; gap: .4rem; }
.acl-scan__row-meta { font-size: .85rem; }

.acl-scan__methods {
    display: flex; flex-wrap: wrap; gap: .35rem;
    margin-top: .5rem; padding-left: 2rem;
}
.acl-scan__method-pill {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .25rem .6rem; border-radius: 999px;
    background: #e9ecef; font-size: .85rem; cursor: pointer;
    margin-bottom: 0;
}
.acl-scan__method-pill input { margin: 0; }
.acl-scan__method-pill:has(input:checked) {
    background: #d1ecf1; color: #0c5460;
}

.acl-scan__actions {
    display: flex; align-items: center; gap: .75rem;
    padding: 1rem 0;
    position: sticky; bottom: 0;
    background: linear-gradient(180deg, transparent 0%, rgba(255,255,255,.95) 25%);
    z-index: 10;
}

.acl-scan__ok { padding: 1rem 1.2rem; font-size: 1.05rem; }
</style>
