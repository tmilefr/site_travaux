<?php
/**
 * Vue d'édition d'un fichier de langue.
 *
 * Variables disponibles :
 *   $idiom              : langue cible (ex: 'en')
 *   $file               : fichier sans .php (ex: 'Menu')
 *   $target_entries     : [clé => valeur cible] (déjà décapée)
 *   $ref_entries        : [clé => valeur de référence (français)]
 *   $ref_idiom          : 'fr'
 *   $file_exists        : bool
 *   $available_languages: array
 */

$ctrl  = 'Translations_controller';
$is_ref_lang = ($idiom === $ref_idiom);

// Construit la liste de toutes les clés à afficher : union ref + cible.
// Ordre : on suit l'ordre de référence pour rester cohérent avec le
// fichier source (et placer en fin les clés "extra" qui n'existent que
// dans la langue cible).
$all_keys = array();
foreach ($ref_entries as $k => $v)    { $all_keys[$k] = true; }
foreach ($target_entries as $k => $v) { $all_keys[$k] = true; }
$all_keys = array_keys($all_keys);

$flash_success = session()->getFlashdata('flash_success');
$flash_error   = session()->getFlashdata('flash_error');
?>
<section class="nicdark_section">
<div class="nicdark_container nicdark_clearfix">
    <div class="nicdark_space30"></div>

    <div class="grid grid_12">
        <h1 class="subtitle greydark">
            <?php echo tr('TRANSLATIONS_EDITING'); ?>
            &nbsp;:&nbsp;
            <code><?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>.php</code>
        </h1>
        <div class="nicdark_space10"></div>
        <h3 class="subtitle grey">
            <?php echo tr('TRANSLATIONS_LANGUAGE'); ?> :
            <strong><?php echo htmlspecialchars($idiom, ENT_QUOTES, 'UTF-8'); ?></strong>
            <?php if ($is_ref_lang) { ?>
                <span class="badge badge-info ml-2">
                    <?php echo tr('TRANSLATIONS_REFERENCE'); ?>
                </span>
            <?php } ?>
        </h3>
        <div class="nicdark_space20"></div>
        <div class="nicdark_divider left big">
            <span class="nicdark_bg_violet nicdark_radius"></span>
        </div>
        <div class="nicdark_space10"></div>
    </div>

    <?php if (!$file_exists && !$is_ref_lang) { ?>
        <div class="alert alert-warning">
            <span class="oi oi-warning"></span>
            <?php echo tr('TRANSLATIONS_FILE_DOES_NOT_EXIST_YET'); ?>
        </div>
    <?php } ?>

    <?php if ($flash_success) { ?>
        <div class="alert alert-success"><?php echo $flash_success; ?></div>
    <?php } ?>
    <?php if ($flash_error) { ?>
        <div class="alert alert-danger"><?php echo $flash_error; ?></div>
    <?php } ?>

    <!-- Barre d'actions haut de page -->
    <div class="form-row mb-2">
        <div class="col-md-6">
            <a href="<?php echo base_url($ctrl.'/list?idiom='.urlencode($idiom)); ?>"
               class="btn btn-light btn-sm">
                <span class="oi oi-arrow-thick-left"></span>
                <?php echo tr('TRANSLATIONS_BACK_TO_LIST'); ?>
            </a>
        </div>
        <div class="col-md-6 text-right">
            <input type="text" id="trans-filter" class="form-control form-control-sm d-inline-block"
                   style="width:auto;"
                   placeholder="<?php echo tr('TRANSLATIONS_FILTER_PLACEHOLDER'); ?>">
            <label class="ml-3">
                <input type="checkbox" id="trans-only-missing">
                <?php echo tr('TRANSLATIONS_ONLY_MISSING'); ?>
            </label>
        </div>
    </div>

    <form method="post" action="<?php echo base_url($ctrl.'/save'); ?>" id="translations-form">
        <input type="hidden" name="idiom" value="<?php echo htmlspecialchars($idiom, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="file"  value="<?php echo htmlspecialchars($file, ENT_QUOTES, 'UTF-8'); ?>">

        <div class="card">
            <div class="card-header">
                <?php echo sprintf(
                    tr('TRANSLATIONS_KEYS_COUNT'),
                    count($all_keys)
                ); ?>
            </div>
            <div class="card-body">

                <table class="table table-sm trans-table">
                    <thead>
                        <tr>
                            <th style="width:24%;"><?php echo tr('TRANSLATIONS_KEY'); ?></th>
                            <?php if (!$is_ref_lang) { ?>
                                <th style="width:34%;">
                                    <span class="badge badge-info">
                                        <?php echo htmlspecialchars($ref_idiom, ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                    <small class="text-muted ml-1">
                                        (<?php echo tr('TRANSLATIONS_REFERENCE'); ?>)
                                    </small>
                                </th>
                                <th style="width:34%;">
                                    <strong><?php echo htmlspecialchars($idiom, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </th>
                            <?php } else { ?>
                                <th style="width:68%;">
                                    <strong><?php echo htmlspecialchars($idiom, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </th>
                            <?php } ?>
                            <th style="width:8%;" class="text-right">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($all_keys as $k):
                        $ref_val    = isset($ref_entries[$k])    ? $ref_entries[$k]    : null;
                        $target_val = isset($target_entries[$k]) ? $target_entries[$k] : null;

                        $is_missing = (!$is_ref_lang)
                            && array_key_exists($k, $ref_entries)
                            && !array_key_exists($k, $target_entries);
                        $is_extra   = (!$is_ref_lang)
                            && !array_key_exists($k, $ref_entries)
                            && array_key_exists($k, $target_entries);

                        $row_class = '';
                        if ($is_missing) $row_class .= ' trans-row-missing';
                        if ($is_extra)   $row_class .= ' trans-row-extra';

                        // Détecte la longueur pour décider input vs textarea
                        $check_val = ($target_val !== null) ? $target_val : ((string) $ref_val);
                        $multiline = (strlen($check_val) > 80) || (strpos($check_val, "\n") !== false);
                    ?>
                        <tr class="trans-row<?php echo $row_class; ?>"
                            data-key="<?php echo htmlspecialchars(strtolower($k), ENT_QUOTES, 'UTF-8'); ?>"
                            data-missing="<?php echo $is_missing ? '1' : '0'; ?>">
                            <td>
                                <code class="trans-key"><?php echo htmlspecialchars($k, ENT_QUOTES, 'UTF-8'); ?></code>
                                <?php if ($is_missing) { ?>
                                    <span class="badge badge-danger ml-1" style="font-size:0.7em;">
                                        <?php echo tr('TRANSLATIONS_MISSING'); ?>
                                    </span>
                                <?php } ?>
                                <?php if ($is_extra) { ?>
                                    <span class="badge badge-warning ml-1" style="font-size:0.7em;">
                                        <?php echo tr('TRANSLATIONS_EXTRA'); ?>
                                    </span>
                                <?php } ?>
                            </td>

                            <?php if (!$is_ref_lang) { ?>
                                <td class="trans-ref">
                                    <?php if ($ref_val !== null) { ?>
                                        <div class="trans-ref-box"
                                             title="<?php echo htmlspecialchars($ref_val, ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($ref_val, ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    <?php } else { ?>
                                        <em class="text-muted">
                                            <?php echo tr('TRANSLATIONS_NOT_IN_REF'); ?>
                                        </em>
                                    <?php } ?>
                                </td>
                            <?php } ?>

                            <td class="trans-target">
                                <?php
                                $name_attr  = 'values['.htmlspecialchars($k, ENT_QUOTES, 'UTF-8').']';
                                $value_attr = htmlspecialchars((string) $target_val, ENT_QUOTES, 'UTF-8');
                                if ($multiline) { ?>
                                    <textarea name="<?php echo $name_attr; ?>"
                                              class="form-control form-control-sm"
                                              rows="3"><?php echo $value_attr; ?></textarea>
                                <?php } else { ?>
                                    <input type="text"
                                           name="<?php echo $name_attr; ?>"
                                           class="form-control form-control-sm"
                                           value="<?php echo $value_attr; ?>">
                                <?php } ?>
                            </td>

                            <td class="text-right">
                                <?php if ($file_exists && array_key_exists($k, $target_entries)) { ?>
                                    <a href="<?php echo base_url($ctrl.'/delete_key/'
                                              .urlencode($idiom).'/'.urlencode($file).'/'
                                              .urlencode($k)); ?>"
                                       class="btn btn-link btn-sm text-danger confirmModalLink"
                                       title="<?php echo tr('TRANSLATIONS_DELETE_KEY'); ?>"
                                       onclick="return confirm('<?php
                                          echo tr('TRANSLATIONS_DELETE_KEY_CONFIRM'); ?>');">
                                        <span class="oi oi-trash"></span>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

            <!-- Ajout d'une nouvelle clé -->
            <div class="card-footer">
                <h5 class="mb-2">
                    <span class="oi oi-plus"></span>
                    <?php echo tr('TRANSLATIONS_ADD_NEW_KEY'); ?>
                </h5>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small text-muted">
                            <?php echo tr('TRANSLATIONS_KEY'); ?>
                        </label>
                        <input type="text" name="new_key"
                               class="form-control form-control-sm"
                               pattern="[A-Za-z0-9_\-\[\]]+"
                               placeholder="my_new_key">
                    </div>
                    <div class="form-group col-md-9">
                        <label class="small text-muted">
                            <?php echo tr('TRANSLATIONS_VALUE'); ?>
                        </label>
                        <input type="text" name="new_val"
                               class="form-control form-control-sm">
                    </div>
                </div>
                <small class="text-muted">
                    <?php echo tr('TRANSLATIONS_ADD_KEY_HELP'); ?>
                </small>
            </div>

            <!-- Footer du formulaire -->
            <div class="card-footer text-right bg-light">
                <a href="<?php echo base_url($ctrl.'/list?idiom='.urlencode($idiom)); ?>"
                   class="btn btn-link text-muted">
                    <?php echo tr('CANCEL'); ?>
                </a>
                <button type="submit" class="btn btn-success">
                    <span class="oi oi-check"></span>
                    <?php echo tr('TRANSLATIONS_SAVE'); ?>
                </button>
            </div>
        </div>
    </form>

    <div class="nicdark_space30"></div>
</div>
</section>

<style>
/* --- Habillage spécifique de l'éditeur de traductions ----------------- */
.trans-table td { vertical-align: top; padding: 0.4rem; }

.trans-key {
    font-size: 0.85em;
    word-break: break-all;
    color: #6c757d;
}

.trans-ref-box {
    background: #f8f9fa;
    border-left: 3px solid #17a2b8;
    padding: 0.4rem 0.6rem;
    font-size: 0.9em;
    color: #495057;
    white-space: pre-wrap;
    word-break: break-word;
    max-height: 6em;
    overflow-y: auto;
}

.trans-row-missing       { background-color: rgba(220,53,69, 0.05); }
.trans-row-missing td    { border-top-color: rgba(220,53,69, 0.2); }
.trans-row-extra         { background-color: rgba(255,193,7, 0.07); }

.trans-row.hidden-by-filter { display: none; }

.trans-target input,
.trans-target textarea { font-size: 0.9em; }
</style>

<script>
/* Filtre live + filtre "uniquement manquantes" */
(function(){
    var input  = document.getElementById('trans-filter');
    var onlyMx = document.getElementById('trans-only-missing');
    var rows   = document.querySelectorAll('.trans-row');

    function apply(){
        var q = (input.value || '').toLowerCase().trim();
        var onlyMissing = onlyMx.checked;

        rows.forEach(function(r){
            var key = r.getAttribute('data-key') || '';
            var isMissing = r.getAttribute('data-missing') === '1';

            // Recherche aussi dans les valeurs ref + cible (textContent global)
            var hay = (r.textContent || '').toLowerCase();

            var matchQ = !q || (key.indexOf(q) !== -1) || (hay.indexOf(q) !== -1);
            var matchM = !onlyMissing || isMissing;

            if (matchQ && matchM) {
                r.classList.remove('hidden-by-filter');
            } else {
                r.classList.add('hidden-by-filter');
            }
        });
    }

    if (input)  input.addEventListener('input', apply);
    if (onlyMx) onlyMx.addEventListener('change', apply);
})();
</script>
