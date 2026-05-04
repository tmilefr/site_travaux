<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Vue "carte" pour la liste des contrôleurs ACL.
 * -----------------------------------------------
 * Cette vue est rendue UNE FOIS PAR LIGNE par list_view.php
 * (cf. MY_Controller::list() + render_view()).
 *
 * Variables disponibles fournies par le moteur :
 *   - $this->render_object : le ligne courante est posée via _set('dba_data')
 *   - $this->bootstrap_tools, $this->lang
 *
 * Améliorations apportées :
 *   - En-tête lisible : icône + nom du contrôleur + compteur d'actions
 *   - Actions affichées en "pills" avec icône (CRUD reconnu, custom mis en avant)
 *   - État vide explicite si le contrôleur n'a aucune action
 *   - Menu d'actions (édition, suppression, etc.) conservé en pied de carte
 */

// --- Récupération du contrôleur courant et de ses actions --------------------
$dba = $this->render_object->_get('dba_data');
$ctrl_name  = isset($dba->controller) ? $dba->controller : '';
$ctrl_id    = isset($dba->id) ? (int) $dba->id : 0;

$actions = array();
if ($ctrl_id && isset($this->Acl_actions_model)) {
    $this->Acl_actions_model->_set('filter', array('id_ctrl' => $ctrl_id));
    $this->Acl_actions_model->_set('order', 'action');
    $this->Acl_actions_model->_set('direction', 'asc');
    $actions = $this->Acl_actions_model->get_all();
}
$nb_actions = is_array($actions) ? count($actions) : 0;

// --- Mapping action -> icône / couleur ---------------------------------------
// CRUD standard reconnu : badge gris/bleu. Tout le reste = badge "vert" (action métier).
$crud_map = array(
    'list'      => array('icon' => 'oi-list',         'class' => 'badge-secondary'),
    'view'      => array('icon' => 'oi-eye',          'class' => 'badge-secondary'),
    'add'       => array('icon' => 'oi-plus',         'class' => 'badge-primary'),
    'edit'      => array('icon' => 'oi-pencil',       'class' => 'badge-primary'),
    'delete'    => array('icon' => 'oi-trash',        'class' => 'badge-danger'),
    'set_rules' => array('icon' => 'oi-key',          'class' => 'badge-warning'),
);
$default_pill = array('icon' => 'oi-cog', 'class' => 'badge-success');

// --- Bandeau couleur (cohérent avec _bg_color du controller) -----------------
$header_color = 'nicdark_bg_red';
?>
<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
<div class="card acl-ctrl-card mb-3 shadow-sm">

    <div class="card-header acl-ctrl-card__header <?php echo $header_color; ?>">
        <div class="acl-ctrl-card__title">
            <span class="oi oi-layers" aria-hidden="true"></span>
            <span class="acl-ctrl-card__name">
                <?php echo htmlspecialchars($ctrl_name, ENT_QUOTES, 'UTF-8'); ?>
            </span>
        </div>
        <span class="badge badge-light acl-ctrl-card__count" title="<?php
            echo htmlspecialchars(
                $this->lang->line('actions') . ' : ' . $nb_actions,
                ENT_QUOTES, 'UTF-8'
            );
        ?>">
            <span class="oi oi-bolt" aria-hidden="true"></span>
            <?php echo $nb_actions; ?>
        </span>
    </div>

    <div class="card-body acl-ctrl-card__body">

        <?php if ($nb_actions === 0) { ?>

            <p class="text-muted font-italic mb-2">
                <span class="oi oi-info" aria-hidden="true"></span>
                <?php echo $this->lang->line('NO_ACTION_DEFINED')
                    ?: 'Aucune action définie pour ce contrôleur.'; ?>
            </p>

        <?php } else { ?>

            <div class="acl-ctrl-card__pills">
                <?php foreach ($actions as $a) {
                    $name = isset($a->action) ? (string) $a->action : '';
                    $key  = strtolower($name);
                    $cfg  = isset($crud_map[$key]) ? $crud_map[$key] : $default_pill;
                ?>
                    <span class="badge acl-pill <?php echo $cfg['class']; ?>"
                          title="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="oi <?php echo $cfg['icon']; ?>" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                <?php } ?>
            </div>

        <?php } ?>

    </div>
    <div class="card-footer acl-ctrl-card__footer text-right">
        <?php echo $this->render_object->render_element_menu(); ?>
    </div>

</div>
</div>
</section>

<style>
/* Petit habillage local — peut être déplacé dans un .css dédié si besoin. */
.acl-ctrl-card { border-radius: 6px; overflow: hidden; }
.acl-ctrl-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
}
.acl-ctrl-card__title { display: flex; align-items: center; gap: 0.5rem; font-weight: 600; }
.acl-ctrl-card__name  { font-size: 1.05rem; letter-spacing: 0.2px; }
.acl-ctrl-card__count {
    font-size: 0.85rem;
    padding: 0.35em 0.6em;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.acl-ctrl-card__pills {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
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
.acl-ctrl-card__footer { background: #f8f9fa; }
</style>
