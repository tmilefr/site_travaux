<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Vue : Orgchart_controller/organisation
 * --------------------------------------
 * Page publique "Notre association" qui affiche, dans cet ordre :
 *   1. un en-tête héroïque avec le groupe mis en avant ($featured)
 *   2. un encart "actions du featured" + liste des PV de CA téléchargeables ($pvca)
 *   3. le trombinoscope des organisations ($organisations)
 *   4. l'agenda (réunions de bureau / CA — $reubur, $reuca)
 *
 * Refonte ergonomie + mobile :
 *   - Toutes les classes spécifiques sont préfixées `org-` pour scoper le CSS.
 *   - Les cartes d'acteurs sont disposées dans une CSS Grid responsive
 *     (4 col desktop / 3 tablette / 2 mobile / 1 très petit écran), ce qui
 *     remplace le `grid grid_3` flottant d'origine qui ne tenait pas en mobile.
 *   - Les deux colonnes "actions" / "PV de CA" et "réunions de bureau" /
 *     "réunions CA" passent en pile verticale en dessous de 768 px.
 *   - Les tableaux PV de CA n'utilisent plus `overflow_scroll` : ils
 *     s'adaptent à la largeur du conteneur.
 *
 * Feuille de style associée : assets/css/orgchart_organisation.css
 * (chargée par le contrôleur via $this->bootstrap_tools->_SetHead).
 */
$colors = $this->render_object->GetColors($featured->color);
?>

<!-- ==================================================================
     1. HÉRO : groupe mis en avant
     ================================================================== -->
<section id="nicdark_parallax_title"
         class="nicdark_section nicdark_imgparallax nicdark_parallaxx_img7 org-hero">
    <div class="nicdark_filter greydark">
        <div class="nicdark_container nicdark_clearfix">
            <div class="grid grid_12 org-hero__inner">
                <h1 class="white subtitle org-hero__title">
                    <?php echo $this->render_object->RenderElement('title', $featured->title, null, 'Orgchart_model'); ?>
                </h1>
                <div class="nicdark_space10"></div>
                <h3 class="subtitle white org-hero__intro">
                    <?php echo $this->render_object->RenderElement('intro', $featured->intro, null, 'Orgchart_model'); ?>
                </h3>
                <div class="nicdark_space20"></div>
                <div class="nicdark_divider left big">
                    <span class="nicdark_bg_white nicdark_radius"></span>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ==================================================================
     2. ACTIONS DU FEATURED + DOCUMENTS DU CA (PV)
     ================================================================== -->
<section class="nicdark_section org-section">
    <div class="nicdark_container nicdark_clearfix">
        <div class="nicdark_space40"></div>

        <div class="org-twocol">
            <!-- Colonne 1 : actions du groupe en avant -->
            <div class="org-twocol__col org-actions">
                <h4 class="org-block-title">
                    <i class="icon-megaphone-1 org-block-title__icon"></i>
                    <?php echo Lang('COM_TTILE_ACTIONS') ?: 'Nos actions'; ?>
                </h4>
                <div class="org-actions__body">
                    <p>
                        <?php echo $this->render_object->RenderElement('actions', $featured->actions, null, 'Orgchart_model'); ?>
                    </p>
                </div>
            </div>

            <!-- Colonne 2 : documents PV de CA -->
            <div class="org-twocol__col org-docs">
                <h4 class="org-block-title">
                    <i class="icon-folder org-block-title__icon"></i>
                    <?php echo LANG('CA_DOCUMENTS'); ?>
                </h4>
                <ul class="org-docs__list">
                    <?php if (is_array($pvca) && count($pvca)) {
                        foreach ($pvca AS $file) { ?>
                            <li class="org-docs__item">
                                <span class="org-docs__name">
                                    <?php echo $this->render_object->RenderElement('memo', $file->name, null, 'Files_model'); ?>
                                </span>
                                <a class="org-docs__link"
                                   target="_new"
                                   href="<?php echo $this->render_object->RenderElement('path', $file->path, null, 'Files_model'); ?>">
                                    <i class="icon-download-outline"></i>
                                    <span class="org-docs__link-label">
                                        <?php echo LANG('CA_DOWNLOAD'); ?>
                                    </span>
                                </a>
                            </li>
                        <?php }
                    } else { ?>
                        <li class="org-docs__empty">
                            <em><?php echo Lang('NO_DATA') ?: 'Aucun document disponible.'; ?></em>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>

        <div class="nicdark_space40"></div>
    </div>
</section>


<!-- ==================================================================
     3. TROMBINOSCOPE
     ================================================================== -->
<section class="nicdark_section org-section org-trombi">
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space40"></div>

        <!-- Titre principal -->
        <div class="grid grid_12 org-trombi__heading">
            <h1 class="subtitle greydark">Trombinoscope</h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey">Les acteurs du conseil d'administration</h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_orange nicdark_radius"></span>
            </div>
        </div>

        <!-- Une "section organisation" par entrée -->
        <?php foreach ($organisations AS $organisation) {
            $colors = $this->render_object->GetColors($organisation->color); ?>

            <div class="org-trombi__group">

                <!-- En-tête de l'organisation -->
                <div class="org-trombi__group-head">
                    <h2 class="subtitle greydark org-trombi__group-title">
                        <?php echo $this->render_object->RenderElement('title', $organisation->title, null, 'Orgchart_model'); ?>
                    </h2>
                    <h4 class="subtitle grey org-trombi__group-mission">
                        <?php echo $this->render_object->RenderElement('mission', $organisation->mission, null, 'Orgchart_model'); ?>
                    </h4>
                    <div class="nicdark_space10"></div>
                    <div class="nicdark_divider left big">
                        <span class="<?php echo $colors->color; ?> nicdark_radius"></span>
                    </div>
                </div>

                <!-- Grille d'acteurs -->
                <?php if (count($organisation->acteurs)) { ?>
                    <div class="org-actors-grid">
                        <?php foreach ($organisation->acteurs as $key => $acteur) { ?>
                            <article class="org-actor nicdark_radius nicdark_shadow">
                                <!-- Bandeau nom + prénom -->
                                <header class="org-actor__head nicdark_bg_greydark nicdark_radius_top">
                                    <h4 class="white">
                                        <?php echo $this->render_object->RenderElement('surname', $acteur->details->surname, null, 'GroupesMembers_model'); ?>
                                        <?php echo $this->render_object->RenderElement('name',    $acteur->details->name,    null, 'GroupesMembers_model'); ?>
                                    </h4>
                                </header>

                                <!-- Photo : on garde RenderElement (gère absence de photo) -->
                                <div class="org-actor__picture">
                                    <?php echo $this->render_object->RenderElement(
                                        'picture',
                                        $acteur->details->picture,
                                        null,
                                        'GroupesMembers_model',
                                        'org-actor__img'
                                    ); ?>
                                </div>

                                <!-- Bandeau classification -->
                                <div class="org-actor__role <?php echo $colors->color; ?>">
                                    <h5 class="white">
                                        <?php echo $this->render_object->RenderElement('classif', $acteur->classif, null, 'Trombi_model'); ?>
                                    </h5>
                                    <i class="icon-brush org-actor__role-icon <?php echo $colors->icon; ?>"></i>
                                </div>

                                <!-- Liens vers les autres commissions où l'acteur est présent -->
                                <div class="org-actor__groups">
                                    <?php
                                    $has_groups = false;
                                    if (!empty($acteur->groups) && count($acteur->groups)) {
                                        foreach ($acteur->groups AS $group) {
                                            if ($group->short != $organisation->short) {
                                                $has_groups = true;
                                                echo '<a href="' . base_url('Orgchart_controller/view_one/' . $group->id) . '"
                                                          class="org-actor__group-link"
                                                          title="' . htmlspecialchars($group->title, ENT_QUOTES, 'UTF-8') . '">
                                                          <i class="icon-doc-text-1"></i>
                                                          <span>' . $group->title . '</span>
                                                       </a>';
                                            }
                                        }
                                    }
                                    if (!$has_groups) {
                                        echo '<span class="org-actor__no-groups">&nbsp;</span>';
                                    }
                                    ?>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <p class="org-trombi__empty">
                        <em><?php echo Lang('NO_DATA') ?: 'Aucun membre référencé pour cette organisation.'; ?></em>
                    </p>
                <?php } ?>

            </div><!-- /.org-trombi__group -->

        <?php } ?>

        <div class="nicdark_space40"></div>
    </div>
</section>


<!-- ==================================================================
     4. AGENDA (réunions de bureau / réunions CA)
     ================================================================== -->
<?php if ((is_array($reubur) && count($reubur)) || (is_array($reuca) && count($reuca))) { ?>
<section class="nicdark_section org-section org-agenda">
    <div class="nicdark_container nicdark_clearfix">

        <div class="nicdark_space40"></div>

        <div class="grid grid_12 org-agenda__heading">
            <h1 class="subtitle greydark">AGENDA</h1>
            <div class="nicdark_space10"></div>
            <h3 class="subtitle grey">L'agenda de nos activités</h3>
            <div class="nicdark_space20"></div>
            <div class="nicdark_divider left big">
                <span class="nicdark_bg_blue nicdark_radius"></span>
            </div>
        </div>

        <div class="org-agenda__cols">

            <!-- Colonne réunions de bureau -->
            <div class="org-agenda__col">
                <h4 class="org-block-title">
                    <i class="icon-calendar org-block-title__icon"></i>
                    <?php echo Lang('reubur') ?: 'Réunions de bureau'; ?>
                </h4>
                <?php if (is_array($reubur) && count($reubur)) { ?>
                    <ul class="org-agenda__list">
                        <?php foreach ($reubur AS $event) { ?>
                            <li class="org-agenda__item">
                                <span class="org-agenda__title">
                                    <?php echo $this->render_object->RenderElement('title', $event->title, null, 'Event_model'); ?>
                                </span>
                                <span class="org-agenda__date <?php echo $event->color; ?>">
                                    <i class="icon-clock"></i>
                                    <?php echo $this->render_object->RenderElement('date', $event->date, null, 'Event_model'); ?>
                                    <?php echo $this->render_object->RenderElement('date', $event->time, null, 'Event_model'); ?>
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="org-agenda__empty"><em>Aucune réunion programmée.</em></p>
                <?php } ?>
            </div>

            <!-- Colonne réunions CA -->
            <div class="org-agenda__col">
                <h4 class="org-block-title">
                    <i class="icon-calendar org-block-title__icon"></i>
                    <?php echo Lang('reuca') ?: 'Réunions du conseil d\'administration'; ?>
                </h4>
                <?php if (is_array($reuca) && count($reuca)) { ?>
                    <ul class="org-agenda__list">
                        <?php foreach ($reuca AS $event) { ?>
                            <li class="org-agenda__item">
                                <span class="org-agenda__title">
                                    <?php echo $this->render_object->RenderElement('title', $event->title, null, 'Event_model'); ?>
                                </span>
                                <span class="org-agenda__date <?php echo $event->color; ?>">
                                    <i class="icon-clock"></i>
                                    <?php echo $this->render_object->RenderElement('date', $event->date, null, 'Event_model'); ?>
                                    <?php echo $this->render_object->RenderElement('date', $event->time, null, 'Event_model'); ?>
                                </span>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="org-agenda__empty"><em>Aucune réunion programmée.</em></p>
                <?php } ?>
            </div>

        </div>

        <div class="nicdark_space40"></div>
    </div>
</section>
<?php } ?>
