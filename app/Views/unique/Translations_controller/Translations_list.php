<?php
/**
 * Vue d'accueil de l'éditeur de traductions.
 *
 * Variables disponibles :
 *   $available_languages : array de strings (idiomes existants)
 *   $current_idiom       : idiome cible courant
 *   $ref_idiom           : idiome de référence (ex: 'fr')
 *   $files               : liste des fichiers _lang.php (sans extension)
 *   $coverage            : [file => ['total_ref','present','missing','extra','exists']]
 */

$flash_success = session()->getFlashdata('flash_success');
$flash_error   = session()->getFlashdata('flash_error');

$ctrl = 'Translations_controller';
?>
<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
    <div class="nicdark_space30"></div>

    <div class="grid grid_12">
        <h1 class="subtitle greydark">
            <?php echo tr('GESTION_Translations_controller'); ?>
        </h1>
        <div class="nicdark_space20"></div>
        <h3 class="subtitle grey">
            <?php echo tr('Translations_controller_subtitle'); ?>
        </h3>
        <div class="nicdark_space20"></div>
        <div class="nicdark_divider left big">
            <span class="nicdark_bg_violet nicdark_radius"></span>
        </div>
        <div class="nicdark_space10"></div>
    </div>

    <?php if ($flash_success) { ?>
        <div class="alert alert-success"><?php echo $flash_success; ?></div>
    <?php } ?>
    <?php if ($flash_error) { ?>
        <div class="alert alert-danger"><?php echo $flash_error; ?></div>
    <?php } ?>

    <!-- ============================================================
         BLOC 1 : Sélection de la langue cible
         ============================================================ -->
    <div class="card">
        <div class="card-header">
            <span class="oi oi-globe" aria-hidden="true"></span>
            &nbsp;<?php echo tr('TRANSLATIONS_PICK_LANGUAGE'); ?>
        </div>
        <div class="card-body">
            <form method="get" action="<?php echo base_url($ctrl.'/list'); ?>" class="form-inline">
                <label class="mr-2" for="idiom-select">
                    <?php echo tr('TRANSLATIONS_LANGUAGE'); ?> :
                </label>
                <select id="idiom-select" name="idiom" class="form-control mr-2"
                        onchange="this.form.submit()">
                    <?php foreach ($available_languages as $lng) { ?>
                        <option value="<?php echo htmlspecialchars($lng, ENT_QUOTES, 'UTF-8'); ?>"
                                <?php echo ($lng === $current_idiom) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lng, ENT_QUOTES, 'UTF-8');
                                  echo ($lng === $ref_idiom)
                                       ? ' ('.tr('TRANSLATIONS_REFERENCE').')'
                                       : ''; ?>
                        </option>
                    <?php } ?>
                </select>
                <noscript>
                    <button type="submit" class="btn btn-primary btn-sm">OK</button>
                </noscript>
            </form>

            <div class="nicdark_space10"></div>
            <small class="text-muted">
                <?php echo tr('TRANSLATIONS_PICK_LANGUAGE_HELP'); ?>
            </small>
        </div>
    </div>

    <div class="nicdark_space20"></div>

    <!-- ============================================================
         BLOC 2 : Création d'une nouvelle langue
         ============================================================ -->
    <div class="card">
        <div class="card-header">
            <span class="oi oi-plus" aria-hidden="true"></span>
            &nbsp;<?php echo tr('TRANSLATIONS_ADD_LANGUAGE'); ?>
        </div>
        <div class="card-body">
            <form method="post" action="<?php echo base_url($ctrl.'/add_language'); ?>"
                  class="form-inline" onsubmit="return confirm('<?php
                      echo tr('TRANSLATIONS_ADD_LANGUAGE_CONFIRM'); ?>');">
                <label class="mr-2">
                    <?php echo tr('TRANSLATIONS_NEW_LANGUAGE_NAME'); ?> :
                </label>
                <input type="text" name="idiom" pattern="[a-z][a-z0-9_\-]*"
                       placeholder="english, german, italian..."
                       class="form-control mr-2" required maxlength="32">
                <button type="submit" class="btn btn-success">
                    <span class="oi oi-plus"></span>
                    <?php echo tr('TRANSLATIONS_CREATE'); ?>
                </button>
            </form>
            <div class="nicdark_space10"></div>
            <small class="text-muted">
                <?php echo tr('TRANSLATIONS_ADD_LANGUAGE_HELP'); ?>
            </small>
        </div>
    </div>

    <div class="nicdark_space20"></div>

    <!-- ============================================================
         BLOC 3 : Liste des fichiers de langue avec couverture
         ============================================================ -->
    <div class="card">
        <div class="card-header">
            <span class="oi oi-file" aria-hidden="true"></span>
            &nbsp;<?php echo sprintf(
                tr('TRANSLATIONS_FILES_FOR'),
                '<strong>'.htmlspecialchars($current_idiom, ENT_QUOTES, 'UTF-8').'</strong>'
            ); ?>
        </div>
        <div class="card-body">

            <?php if (empty($files)) { ?>
                <p class="text-muted">
                    <?php echo tr('TRANSLATIONS_NO_FILES'); ?>
                </p>
            <?php } else { ?>

                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th><?php echo tr('TRANSLATIONS_FILE'); ?></th>
                            <th class="text-center">
                                <?php echo tr('TRANSLATIONS_COVERAGE'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo tr('TRANSLATIONS_KEYS_REF'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo tr('TRANSLATIONS_KEYS_PRESENT'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo tr('TRANSLATIONS_KEYS_MISSING'); ?>
                            </th>
                            <th class="text-center">
                                <?php echo tr('TRANSLATIONS_KEYS_EXTRA'); ?>
                            </th>
                            <th class="text-right">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($files as $f) {
                        $cov = isset($coverage[$f]) ? $coverage[$f] : null;
                        $is_ref = ($current_idiom === $ref_idiom);
                        $pct = ($cov && $cov['total_ref'] > 0)
                             ? round(100 * $cov['present'] / $cov['total_ref'])
                             : 0;
                        $pct_class = $pct >= 100 ? 'success'
                                   : ($pct >= 70 ? 'warning' : 'danger');
                    ?>
                        <tr>
                            <td>
                                <code><?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?>.php</code>
                                <?php if ($cov && !$cov['exists']) { ?>
                                    <span class="badge badge-warning ml-2">
                                        <?php echo tr('TRANSLATIONS_FILE_MISSING'); ?>
                                    </span>
                                <?php } ?>
                            </td>
                            <td class="text-center" style="min-width:140px;">
                                <?php if ($is_ref) { ?>
                                    <span class="badge badge-info">
                                        <?php echo tr('TRANSLATIONS_REFERENCE'); ?>
                                    </span>
                                <?php } else { ?>
                                    <div class="progress" style="height:18px;">
                                        <div class="progress-bar bg-<?php echo $pct_class; ?>"
                                             role="progressbar"
                                             style="width:<?php echo $pct; ?>%;"
                                             aria-valuenow="<?php echo $pct; ?>"
                                             aria-valuemin="0" aria-valuemax="100">
                                            <?php echo $pct; ?>%
                                        </div>
                                    </div>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <?php echo $cov ? (int) $cov['total_ref'] : '-'; ?>
                            </td>
                            <td class="text-center text-success">
                                <?php echo $cov ? (int) $cov['present'] : '-'; ?>
                            </td>
                            <td class="text-center <?php
                                  echo ($cov && $cov['missing'] > 0) ? 'text-danger font-weight-bold' : ''; ?>">
                                <?php echo $cov ? (int) $cov['missing'] : '-'; ?>
                            </td>
                            <td class="text-center <?php
                                  echo ($cov && $cov['extra'] > 0) ? 'text-warning' : ''; ?>">
                                <?php echo $cov ? (int) $cov['extra'] : '-'; ?>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo base_url($ctrl.'/edit/'.urlencode($current_idiom).'/'.urlencode($f)); ?>"
                                   class="btn btn-warning btn-sm">
                                    <span class="oi oi-pencil"></span>
                                    <?php echo tr('TRANSLATIONS_EDIT'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>

            <?php } ?>

        </div>
    </div>

    <div class="nicdark_space20"></div>
</div>
</section>
