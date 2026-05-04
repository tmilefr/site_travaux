<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Formulaire d'édition / création d'un contrôleur ACL.
 * ----------------------------------------------------
 * Variables disponibles (cf. MY_Controller + Render_object) :
 *   - $id              : identifiant courant (vide en création)
 *   - $required_field  : tableau des champs requis (pour form_error)
 *   - $this->render_object, $this->bootstrap_tools, $this->lang
 *
 * Améliorations ergonomiques :
 *   - En-tête contextualisé (mode + nom du contrôleur édité)
 *   - Synthèse des erreurs en haut + erreurs sous chaque champ
 *   - Colonne latérale "Métadonnées" en mode édition
 *   - Palette d'actions standards en aide à la saisie
 *   - Footer d'actions clair (Enregistrer + Annuler)
 */

// --- Contexte ---------------------------------------------------------------
$form_mod   = $this->render_object->_get('form_mod'); // 'add' ou 'edit'
$is_edit    = ($form_mod === 'edit');
$ctrl_route = 'Acl_controllers_controller';
$base_list  = base_url($ctrl_route . '/list');

// Données existantes en mode édition (nom, dates, etc.)
$dba_data = $this->render_object->_get('dba_data');
$ctrl_name_current = ($is_edit && isset($dba_data->controller))
    ? $dba_data->controller
    : '';
$created_at = ($is_edit && isset($dba_data->created)) ? $dba_data->created : null;
$updated_at = ($is_edit && isset($dba_data->updated)) ? $dba_data->updated : null;

// Nombre d'actions actuellement liées (utile en édition)
$nb_actions = 0;
if ($is_edit && $id && isset($this->Acl_actions_model)) {
    $this->Acl_actions_model->_set('filter', array('id_ctrl' => (int) $id));
    $nb_actions = count($this->Acl_actions_model->get_all());
}

// Libellés
$page_title = $this->lang->line($ctrl_route . '_' . $form_mod);
$btn_label  = $this->render_object->_get('_ui_rules')[$form_mod]->name;

// Détection erreurs de validation
$has_errors = false;
foreach ($required_field as $f) {
    if (form_error($f) !== '') { $has_errors = true; break; }
}
?>

<section class="nicdark_section acl-form">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space30"></div>

        <?php /* ============================================================
                EN-TETE CONTEXTUALISE
           ============================================================ */ ?>
        <div class="grid grid_12">
            <div class="acl-form__header">
                <div>
                    <span class="badge <?php echo $is_edit ? 'badge-warning' : 'badge-success'; ?> acl-form__mode-badge">
                        <span class="oi <?php echo $is_edit ? 'oi-pencil' : 'oi-plus'; ?>" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <h1 class="subtitle greydark acl-form__title">
                        <span class="oi oi-layers" aria-hidden="true"></span>
                        <?php
                        if ($is_edit && $ctrl_name_current !== '') {
                            echo htmlspecialchars($ctrl_name_current, ENT_QUOTES, 'UTF-8');
                        } else {
                            echo $this->lang->line($ctrl_route . '_subtitle');
                        }
                        ?>
                    </h1>
                </div>
                <a href="<?php echo $base_list; ?>"
                   class="btn btn-light acl-form__back"
                   title="<?php echo htmlspecialchars($this->lang->line('LIST') ?: 'Retour à la liste', ENT_QUOTES, 'UTF-8'); ?>">
                    <span class="oi oi-arrow-left" aria-hidden="true"></span>
                    <?php echo $this->lang->line('LIST_'.$ctrl_route) ?: 'Retour à la liste'; ?>
                </a>
            </div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_red nicdark_radius"></span>
            </div>
            <div class="nicdark_space10"></div>
        </div>

        <?php /* ============================================================
                SYNTHESE DES ERREURS (si validation a échoué)
           ============================================================ */ ?>
        <?php if ($has_errors) { ?>
            <div class="grid grid_12">
                <div class="alert alert-danger acl-form__errors">
                    <strong>
                        <span class="oi oi-warning" aria-hidden="true"></span>
                        <?php echo $this->lang->line('FORM_ERRORS_TITLE') ?: 'Veuillez corriger les erreurs ci-dessous :'; ?>
                    </strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($required_field as $f) {
                            $err = form_error($f);
                            if ($err !== '') {
                                $label = $this->lang->line($f) ?: $f;
                                echo '<li><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong> : '
                                    . strip_tags($err) . '</li>';
                            }
                        } ?>
                    </ul>
                </div>
            </div>
        <?php } ?>

        <?php /* ============================================================
                FORMULAIRE
           ============================================================ */ ?>
        <?php
        echo form_open(
            $ctrl_route . '/' . $form_mod,
            array('class' => 'acl-form__form', 'id' => 'edit'),
            array('form_mod' => $form_mod, 'id' => $id)
        );
        ?>

        <div class="grid grid_<?php echo $is_edit ? '8' : '12'; ?>">
            <div class="card shadow-sm">
                <div class="card-header acl-form__card-header">
                    <span class="oi oi-cog" aria-hidden="true"></span>
                    <?php echo $this->lang->line('FORM_MAIN_INFO') ?: 'Informations'; ?>
                </div>
                <div class="card-body">

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <?php echo $this->bootstrap_tools->label('controller'); ?>
                            <small class="form-text text-muted mb-2">
                                <?php echo $this->lang->line('controller_help')
                                    ?: 'Nom exact de la classe PHP (ex. <code>Acl_users_controller</code>).'; ?>
                            </small>
                            <?php echo $this->render_object->RenderFormElement('controller'); ?>
                            <?php echo form_error('controller', '<div class="invalid-feedback d-block mt-1">', '</div>'); ?>
                        </div>
                    </div>

                    <hr/>

                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <?php echo $this->bootstrap_tools->label('actions'); ?>
                            <small class="form-text text-muted mb-2">
                                <?php echo $this->lang->line('actions_help')
                                    ?: 'Une action = une méthode publique du contrôleur (ex. list, add, edit, delete).'; ?>
                            </small>

                            <?php /* Palette d'actions standard — purement informatif/aide à la saisie */ ?>
                            <div class="acl-form__std-actions" aria-hidden="true">
                                <span class="text-muted small mr-2">
                                    <?php echo $this->lang->line('STD_ACTIONS') ?: 'Actions standard :'; ?>
                                </span>
                                <?php foreach (array(
                                    'list'   => array('icon' => 'oi-list',   'class' => 'badge-secondary'),
                                    'view'   => array('icon' => 'oi-eye',    'class' => 'badge-secondary'),
                                    'add'    => array('icon' => 'oi-plus',   'class' => 'badge-primary'),
                                    'edit'   => array('icon' => 'oi-pencil', 'class' => 'badge-primary'),
                                    'delete' => array('icon' => 'oi-trash',  'class' => 'badge-danger'),
                                ) as $name => $cfg) { ?>
                                    <span class="badge <?php echo $cfg['class']; ?> acl-form__std-pill">
                                        <span class="oi <?php echo $cfg['icon']; ?>" aria-hidden="true"></span>
                                        <?php echo $name; ?>
                                    </span>
                                <?php } ?>
                            </div>

                            <?php echo $this->render_object->RenderFormElement('actions'); ?>
                            <?php echo form_error('actions', '<div class="invalid-feedback d-block mt-1">', '</div>'); ?>
                        </div>
                    </div>

                </div>

                <?php /* Footer d'actions */ ?>
                <div class="card-footer acl-form__footer">
                    <a href="<?php echo $base_list; ?>" class="btn btn-link text-muted">
                        <?php echo $this->lang->line('CANCEL') ?: 'Annuler'; ?>
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <span class="oi oi-check" aria-hidden="true"></span>
                        <?php echo htmlspecialchars($btn_label, ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </div>
            </div>
        </div>

        <?php /* ============================================================
                COLONNE LATERALE — METADONNEES (mode edit uniquement)
           ============================================================ */ ?>
        <?php if ($is_edit) { ?>
            <div class="grid grid_4">
                <div class="card shadow-sm acl-form__meta">
                    <div class="card-header">
                        <span class="oi oi-info" aria-hidden="true"></span>
                        <?php echo $this->lang->line('FORM_META') ?: 'Informations système'; ?>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <?php echo $this->lang->line('id') ?: 'ID'; ?>
                            </span>
                            <code><?php echo (int) $id; ?></code>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="text-muted">
                                <?php echo $this->lang->line('actions') ?: 'Actions'; ?>
                            </span>
                            <span class="badge badge-info badge-pill">
                                <?php echo $nb_actions; ?>
                            </span>
                        </li>
                        <?php if ($created_at) { ?>
                            <li class="list-group-item">
                                <small class="text-muted d-block">
                                    <?php echo $this->lang->line('created') ?: 'Créé le'; ?>
                                </small>
                                <span><?php echo htmlspecialchars($created_at, ENT_QUOTES, 'UTF-8'); ?></span>
                            </li>
                        <?php } ?>
                        <?php if ($updated_at && $updated_at !== '0000-00-00 00:00:00') { ?>
                            <li class="list-group-item">
                                <small class="text-muted d-block">
                                    <?php echo $this->lang->line('updated') ?: 'Modifié le'; ?>
                                </small>
                                <span><?php echo htmlspecialchars($updated_at, ENT_QUOTES, 'UTF-8'); ?></span>
                            </li>
                        <?php } ?>
                    </ul>

                    <?php /* Raccourci utile : voir les rôles ACL pour assigner ce contrôleur */ ?>
                    <div class="card-body">
                        <a href="<?php echo base_url('Acl_roles_controller/list'); ?>"
                           class="btn btn-outline-info btn-block btn-sm">
                            <span class="oi oi-key" aria-hidden="true"></span>
                            <?php echo $this->lang->line('GO_TO_ROLES') ?: 'Gérer les rôles'; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php } ?>

        <?php
        // Champs cachés (created/updated) — préserver le comportement existant
        echo $this->render_object->RenderFormElement('created');
        echo $this->render_object->RenderFormElement('updated');
        echo form_close();
        ?>

    </div>
</section>

<style>
/* Habillage local — déplaçable dans un .css dédié si besoin. */
.acl-form__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
}
.acl-form__mode-badge {
    font-size: 0.85rem;
    padding: 0.4em 0.7em;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin-bottom: 0.4rem;
}
.acl-form__title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
}
.acl-form__back {
    align-self: flex-start;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.acl-form__errors ul { padding-left: 1.2rem; }
.acl-form__card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #f8f9fa;
    font-weight: 600;
}
.acl-form__std-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.35rem;
    padding: 0.5rem 0.75rem;
    background: #f8f9fa;
    border: 1px dashed #dee2e6;
    border-radius: 6px;
    margin-bottom: 0.75rem;
}
.acl-form__std-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.35em 0.6em;
    font-weight: 500;
    text-transform: lowercase;
    border-radius: 999px;
}
.acl-form__footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
}
.acl-form__meta .list-group-item code { background: transparent; }
@media (max-width: 768px) {
    .acl-form__header { flex-direction: column; align-items: stretch; }
    .acl-form__back   { align-self: stretch; justify-content: center; }
    .acl-form__footer { flex-direction: column; gap: 0.5rem; }
    .acl-form__footer .btn { width: 100%; }
}
</style>
