/* =====================================================================
 * set_rules.js
 * Comportement client de la vue Acl_roles_controller/set_rules.
 *
 * Fonctionnalités :
 *   - Recherche live : masque les contrôleurs / actions ne matchant pas.
 *   - Master switch ZONE :
 *       * coche/décoche toutes les actions de la zone
 *       * son état (checked/unchecked/indéterminé) est recalculé chaque
 *         fois qu'une action ou un master contrôleur change.
 *   - Master switch CONTRÔLEUR :
 *       * coche/décoche toutes les actions du contrôleur
 *       * état recalculé sur changement d'une action.
 *   - Boutons globaux : tout cocher / tout décocher / tout déplier / replier.
 *   - Compteurs (zone + global) recalculés à chaque changement.
 *
 * Aucune dépendance hors jQuery (déjà chargé pour Bootstrap 4).
 * ===================================================================== */
(function ($) {
    'use strict';

    if (typeof $ === 'undefined') {
        console.warn('set_rules.js : jQuery est requis.');
        return;
    }

    $(function () {

        var $form = $('.acl-rules-form');
        if (!$form.length) { return; }

        // ----- Helpers ----------------------------------------------------

        /**
         * Met à jour le compteur "actifs / total" d'une zone donnée.
         * Recalcule aussi l'état du master switch zone (coché si tout est
         * coché, indéterminé si partiel).
         */
        function refreshZoneCounter($zone) {
            var $cbs = $zone.find('.js-action-cb:not(:disabled)');
            // On exclut explicitement les actions masquées par la recherche ?
            // Non : les compteurs reflètent l'ÉTAT (donc tout, visible ou non).
            var total  = $cbs.length;
            var active = $cbs.filter(':checked').length;

            $zone.find('.js-zone-active').text(active);
            $zone.find('.js-zone-total').text(total);

            var $master = $zone.find('.js-zone-toggle');
            if (total === 0) {
                $master.prop('checked', false).prop('indeterminate', false);
            } else if (active === 0) {
                $master.prop('checked', false).prop('indeterminate', false);
            } else if (active === total) {
                $master.prop('checked', true).prop('indeterminate', false);
            } else {
                $master.prop('checked', false).prop('indeterminate', true);
            }
        }

        /**
         * Met à jour le compteur d'un contrôleur (ligne) + l'état de son
         * master switch (checked / indéterminé / décoché).
         */
        function refreshCtrlCounter($row) {
            var $cbs = $row.find('.js-action-cb');
            var total  = $cbs.length;
            var active = $cbs.filter(':checked').length;

            $row.find('.js-ctrl-active').text(active);
            $row.find('.js-ctrl-total').text(total);

            var $master = $row.find('.js-ctrl-toggle');
            if (!$master.length) { return; }

            if (total === 0 || active === 0) {
                $master.prop('checked', false).prop('indeterminate', false);
            } else if (active === total) {
                $master.prop('checked', true).prop('indeterminate', false);
            } else {
                $master.prop('checked', false).prop('indeterminate', true);
            }
        }

        /**
         * Recompte le nombre total d'actions cochées sur la page entière
         * et met à jour les badges "globaux" (toolbar + footer).
         */
        function refreshGlobalCounter() {
            var $cbs = $form.find('.js-action-cb');
            var total  = $cbs.length;
            var active = $cbs.filter(':checked').length;

            $form.find('.js-acl-count-active').text(active);
            $form.find('.js-acl-count-total').text(total);
        }

        /**
         * Recalcule tous les compteurs de la page. À appeler après un
         * "tout cocher" ou "tout décocher" global.
         */
        function refreshAll() {
            $form.find('.acl-zone-card').each(function () {
                var $zone = $(this);
                $zone.find('.acl-rules-row').each(function () {
                    refreshCtrlCounter($(this));
                });
                refreshZoneCounter($zone);
            });
            refreshGlobalCounter();
        }


        // ----- Événements : action individuelle ---------------------------
        $form.on('change', '.js-action-cb', function () {
            var $cb   = $(this);
            var $row  = $cb.closest('.acl-rules-row');
            var $zone = $cb.closest('.acl-zone-card');

            refreshCtrlCounter($row);
            refreshZoneCounter($zone);
            refreshGlobalCounter();
        });


        // ----- Événements : master switch contrôleur ----------------------
        $form.on('change', '.js-ctrl-toggle', function () {
            var $cb     = $(this);
            var checked = $cb.prop('checked');
            var $row    = $cb.closest('.acl-rules-row');

            // Coche / décoche toutes les actions du contrôleur (mais pas
            // celles qui sont masquées par la recherche : on respecte le
            // filtre actif si l'utilisateur l'a posé).
            $row.find('.acl-action-item:not(.is-hidden) .js-action-cb')
                .prop('checked', checked);

            refreshCtrlCounter($row);
            refreshZoneCounter($row.closest('.acl-zone-card'));
            refreshGlobalCounter();
        });


        // ----- Événements : master switch zone ----------------------------
        $form.on('change', '.js-zone-toggle', function () {
            var $cb     = $(this);
            var checked = $cb.prop('checked');
            var $zone   = $cb.closest('.acl-zone-card');

            // Idem : on respecte le filtre de recherche.
            $zone.find('.acl-rules-row:not(.is-hidden) .acl-action-item:not(.is-hidden) .js-action-cb')
                .prop('checked', checked);

            $zone.find('.acl-rules-row').each(function () {
                refreshCtrlCounter($(this));
            });
            refreshZoneCounter($zone);
            refreshGlobalCounter();
        });


        // ----- Boutons globaux : Tout cocher / Tout décocher --------------
        $form.find('.js-acl-all-on').on('click', function () {
            $form.find('.acl-rules-row:not(.is-hidden) .acl-action-item:not(.is-hidden) .js-action-cb')
                .prop('checked', true);
            refreshAll();
        });

        $form.find('.js-acl-all-off').on('click', function () {
            $form.find('.acl-rules-row:not(.is-hidden) .acl-action-item:not(.is-hidden) .js-action-cb')
                .prop('checked', false);
            refreshAll();
        });


        // ----- Boutons globaux : Tout déplier / replier -------------------
        $form.find('.js-acl-expand').on('click', function () {
            $form.find('.acl-zone-card .collapse').collapse('show');
        });

        $form.find('.js-acl-collapse').on('click', function () {
            $form.find('.acl-zone-card .collapse').collapse('hide');
        });


        // ----- Recherche live ---------------------------------------------
        var searchTimer = null;

        function applySearch(rawQuery) {
            var q = $.trim(rawQuery).toLowerCase();

            if (q === '') {
                // Réafficher tout
                $form.find('.acl-action-item.is-hidden').removeClass('is-hidden');
                $form.find('.acl-rules-row.is-hidden').removeClass('is-hidden');
                $form.find('.acl-zone-card').show();
                return;
            }

            // Sur chaque ligne contrôleur :
            //   - si le nom du contrôleur matche -> on garde toute la ligne
            //   - sinon on filtre les actions une à une, et si AUCUNE action
            //     ne matche, on masque toute la ligne.
            $form.find('.acl-zone-card').each(function () {
                var $zone = $(this);
                var anyRowVisible = false;

                $zone.find('.acl-rules-row').each(function () {
                    var $row = $(this);
                    var ctrlName = $row.attr('data-ctrl-name') || '';

                    if (ctrlName.indexOf(q) !== -1) {
                        // Match nom du contrôleur : on garde tout
                        $row.removeClass('is-hidden');
                        $row.find('.acl-action-item.is-hidden').removeClass('is-hidden');
                        anyRowVisible = true;
                        return; // continue
                    }

                    // Sinon : on filtre les actions
                    var anyActionVisible = false;
                    $row.find('.acl-action-item').each(function () {
                        var $item = $(this);
                        var actName = $item.attr('data-action-name') || '';
                        if (actName.indexOf(q) !== -1) {
                            $item.removeClass('is-hidden');
                            anyActionVisible = true;
                        } else {
                            $item.addClass('is-hidden');
                        }
                    });

                    if (anyActionVisible) {
                        $row.removeClass('is-hidden');
                        anyRowVisible = true;
                    } else {
                        $row.addClass('is-hidden');
                    }
                });

                // Si plus aucune ligne dans la zone -> masquer la zone
                if (anyRowVisible) {
                    $zone.show();
                } else {
                    $zone.hide();
                }
            });
        }

        $('#aclRulesSearch').on('input', function () {
            var val = this.value;
            // Léger debounce pour éviter de saccader la frappe
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function () {
                applySearch(val);
            }, 120);
        });


        // ----- Initialisation des compteurs au chargement -----------------
        refreshAll();
    });

})(window.jQuery);
