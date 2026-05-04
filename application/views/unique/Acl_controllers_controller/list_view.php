<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Vue de liste spécifique à Acl_controllers_controller.
 *
 * Chemin attendu (résolution automatique par MY_Controller::render_view) :
 *   application/views/unique/Acl_controllers_controller/list_view.php
 *
 * Affiche un bandeau de tableau de bord ACL (KPIs + alertes) PUIS
 * délègue le rendu à la vue de liste générique sans la dupliquer.
 *
 * Variables ajoutées par Acl_controllers_controller::list() :
 *   - $acl_kpis     : tableau d'indicateurs
 *   - $acl_warnings : tableau d'alertes (objets {type, severity, message, items})
 *
 * Toutes les autres variables sont celles passées par MY_Controller::list().
 */

$acl_kpis     = isset($acl_kpis)     ? $acl_kpis     : array();
$acl_warnings = isset($acl_warnings) ? $acl_warnings : array();
?>

<section class="nicdark_section acl-dashboard">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <?php /* ============================================================
                BANDEAU KPIs
           ============================================================ */ ?>
        <div class="grid grid_12 acl-dashboard__kpis">

            <div class="acl-kpi acl-kpi--primary">
                <div class="acl-kpi__icon"><span class="oi oi-layers" aria-hidden="true"></span></div>
                <div class="acl-kpi__body">
                    <div class="acl-kpi__value"><?php echo (int) $acl_kpis['total_ctrls']; ?></div>
                    <div class="acl-kpi__label">
                        <?php echo $this->lang->line('ACL_KPI_CONTROLLERS') ?: 'Contrôleurs'; ?>
                    </div>
                </div>
            </div>

            <div class="acl-kpi acl-kpi--info">
                <div class="acl-kpi__icon"><span class="oi oi-bolt" aria-hidden="true"></span></div>
                <div class="acl-kpi__body">
                    <div class="acl-kpi__value"><?php echo (int) $acl_kpis['total_actions']; ?></div>
                    <div class="acl-kpi__label">
                        <?php echo $this->lang->line('ACL_KPI_ACTIONS') ?: 'Actions'; ?>
                        <small class="text-muted">
                            (Ø <?php echo $acl_kpis['avg_actions']; ?>/<?php
                                echo $this->lang->line('ACL_KPI_CTRL_SHORT') ?: 'ctrl'; ?>)
                        </small>
                    </div>
                </div>
            </div>

            <div class="acl-kpi acl-kpi--success">
                <div class="acl-kpi__icon"><span class="oi oi-key" aria-hidden="true"></span></div>
                <div class="acl-kpi__body">
                    <div class="acl-kpi__value"><?php echo (int) $acl_kpis['total_rules']; ?></div>
                    <div class="acl-kpi__label">
                        <?php echo $this->lang->line('ACL_KPI_RULES') ?: 'Règles ACL'; ?>
                    </div>
                </div>
            </div>

            <div class="acl-kpi acl-kpi--warning">
                <div class="acl-kpi__icon"><span class="oi oi-people" aria-hidden="true"></span></div>
                <div class="acl-kpi__body">
                    <div class="acl-kpi__value"><?php echo (int) $acl_kpis['total_roles_using']; ?></div>
                    <div class="acl-kpi__label">
                        <?php echo $this->lang->line('ACL_KPI_ROLES_USING') ?: 'Rôles actifs'; ?>
                    </div>
                </div>
            </div>

        </div>

        <?php /* ============================================================
                ALERTES (orphelins, sans rôle, fichier manquant)
           ============================================================ */ ?>
        <?php if (!empty($acl_warnings)) { ?>
            <div class="grid grid_12 acl-dashboard__warnings">
                <?php foreach ($acl_warnings as $idx => $w) { ?>
                    <?php
                    $sev_icon = array(
                        'danger'  => 'oi-warning',
                        'warning' => 'oi-warning',
                        'info'    => 'oi-info',
                    );
                    $icon = isset($sev_icon[$w->severity]) ? $sev_icon[$w->severity] : 'oi-info';
                    $collapse_id = 'aclWarn' . (int) $idx;
                    $count = is_array($w->items) ? count($w->items) : 0;
                    ?>
                    <div class="alert alert-<?php echo htmlspecialchars($w->severity, ENT_QUOTES, 'UTF-8'); ?> acl-warning">
                        <div class="acl-warning__head">
                            <strong>
                                <span class="oi <?php echo $icon; ?>" aria-hidden="true"></span>
                                <?php echo htmlspecialchars($w->message, ENT_QUOTES, 'UTF-8'); ?>
                                <span class="badge badge-light ml-1"><?php echo $count; ?></span>
                            </strong>
                            <button class="btn btn-sm btn-link acl-warning__toggle"
                                    type="button"
                                    data-toggle="collapse"
                                    data-target="#<?php echo $collapse_id; ?>"
                                    aria-expanded="false"
                                    aria-controls="<?php echo $collapse_id; ?>">
                                <?php echo $this->lang->line('ACL_WARN_DETAILS') ?: 'Voir'; ?>
                                <span class="oi oi-chevron-bottom" aria-hidden="true"></span>
                            </button>
                        </div>
                        <div class="collapse acl-warning__items" id="<?php echo $collapse_id; ?>">
                            <ul class="list-unstyled mb-0 mt-2">
                                <?php foreach ($w->items as $it) {
                                    $name  = isset($it->controller) ? $it->controller : '';
                                    $idval = isset($it->id) ? (int) $it->id : 0;
                                    $url   = base_url('Acl_controllers_controller/edit/' . $idval);
                                    ?>
                                    <li class="acl-warning__item">
                                        <a href="<?php echo $url; ?>" class="acl-warning__link">
                                            <span class="oi oi-pencil" aria-hidden="true"></span>
                                            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if ($this->acl->hasAccess('Acl_controllers_controller/bulk_add_action')){ ?>
        <a href="<?php echo site_url('Acl_controllers_controller/bulk_add_action'); ?>" class="btn btn-warning">
            <i class="oi oi-plus"></i>
            <?php echo $this->lang->line('Acl_controllers_controller_bulk_add_action'); ?>
        </a>
        <?php } ?>
    </div>
</section>

<?php
// ===========================================================================
// On délègue ensuite à la vue de liste générique : on ne réécrit pas
// le tri, les filtres, la pagination, etc. — tout reste au même endroit.
// ===========================================================================
$this->load->view('unique/list_view', isset($data_view) ? $data_view : array());
?>

<style>
/* ============================================================
   Habillage local du tableau de bord ACL — déplaçable dans un
   .css dédié si besoin.
   ============================================================ */
.acl-dashboard__kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.acl-kpi {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 0.9rem 1rem;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    border-left: 4px solid #6c757d;
}
.acl-kpi--primary { border-left-color: #007bff; }
.acl-kpi--info    { border-left-color: #17a2b8; }
.acl-kpi--success { border-left-color: #28a745; }
.acl-kpi--warning { border-left-color: #ffc107; }

.acl-kpi__icon {
    font-size: 1.6rem;
    color: #6c757d;
    width: 2.4rem;
    height: 2.4rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
    flex-shrink: 0;
}
.acl-kpi--primary .acl-kpi__icon { color: #007bff; }
.acl-kpi--info    .acl-kpi__icon { color: #17a2b8; }
.acl-kpi--success .acl-kpi__icon { color: #28a745; }
.acl-kpi--warning .acl-kpi__icon { color: #c29200; }

.acl-kpi__value {
    font-size: 1.6rem;
    font-weight: 700;
    line-height: 1;
}
.acl-kpi__label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 0.2rem;
}
.acl-kpi__label small { font-size: 0.75rem; text-transform: none; letter-spacing: 0; }

.acl-warning { padding: 0.6rem 0.9rem; }
.acl-warning__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.acl-warning__toggle { padding: 0; text-decoration: none; }
.acl-warning__toggle:hover { text-decoration: underline; }
.acl-warning__items ul {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
.acl-warning__item .acl-warning__link {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.25rem 0.6rem;
    background: rgba(255,255,255,0.6);
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 999px;
    font-size: 0.85rem;
    color: #495057;
    text-decoration: none;
}
.acl-warning__item .acl-warning__link:hover {
    background: #fff;
    color: #000;
    text-decoration: none;
}
</style>
