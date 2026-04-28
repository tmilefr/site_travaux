/* =====================================================================
   units_valid.js — Refonte vue Units_controller/valid
   Filtres rapides côté client + compteurs + sticky submit
   ===================================================================== */
(function () {
    'use strict';

    // Petit utilitaire DOM
    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

    var LS_KEY = 'uv_filters_v1';

    // Mémoire interne des filtres
    var state = {
        q:       '',
        date:    '*',
        famille: '*',
        type:    '*'
    };

    // Restaure l'état si présent en localStorage (utile entre allers-retours)
    function loadState() {
        try {
            var raw = localStorage.getItem(LS_KEY);
            if (raw) {
                var saved = JSON.parse(raw);
                if (saved && typeof saved === 'object') {
                    state.q       = saved.q       || '';
                    state.date    = saved.date    || '*';
                    state.famille = saved.famille || '*';
                    state.type    = saved.type    || '*';
                }
            }
        } catch (e) { /* noop */ }
    }
    function saveState() {
        try { localStorage.setItem(LS_KEY, JSON.stringify(state)); } catch (e) {}
    }

    // Application des filtres
    function applyFilters() {
        var cards = $$('.uv-card');
        var visibleCards = 0;
        var visibleUnits = 0;
        var q = (state.q || '').toLowerCase().trim();

        cards.forEach(function (card) {
            // Filtre carte (date + type + recherche au niveau session)
            var okDate = (state.date === '*') || (card.dataset.date === state.date);
            var okType = (state.type === '*') || (card.dataset.type === state.type);
            var okSearchCard = !q || (card.dataset.search || '').indexOf(q) !== -1;

            // Filtre lignes (famille + recherche au niveau ligne)
            var rows = $$('.uv-row', card);
            var rowsVisible = 0;
            rows.forEach(function (row) {
                var okFam = (state.famille === '*') || (row.dataset.famille === state.famille);
                var okSearchRow = !q || (row.dataset.search || '').indexOf(q) !== -1
                                     || (card.dataset.search || '').indexOf(q) !== -1;
                var visible = okFam && okSearchRow;
                row.style.display = visible ? '' : 'none';
                // Si la ligne est masquée, on décoche pour éviter une validation cachée
                if (!visible) {
                    var cb = row.querySelector('.uv-row-check');
                    if (cb && cb.checked) cb.checked = false;
                }
                if (visible) rowsVisible++;
            });

            // La carte est visible si elle satisfait elle-même les filtres
            // ET qu'au moins une ligne est visible.
            var cardVisible = okDate && okType && okSearchCard && (rowsVisible > 0);
            card.style.display = cardVisible ? '' : 'none';

            if (cardVisible) {
                visibleCards++;
                visibleUnits += rowsVisible;
                // Mise à jour du compteur d'inscrits sur la carte
                var c = card.querySelector('.uv-card-count');
                if (c) {
                    if (rowsVisible !== rows.length) {
                        c.textContent = rowsVisible + ' / ' + rows.length;
                    } else {
                        c.textContent = rows.length;
                    }
                }
            }
        });

        // Compteurs globaux
        var elS = $('#uv-count-sessions');
        var elU = $('#uv-count-units');
        if (elS) elS.textContent = visibleCards;
        if (elU) elU.textContent = visibleUnits;

        // Empty state
        var noRes = $('#uv-no-result');
        if (noRes) noRes.style.display = (visibleCards === 0) ? '' : 'none';

        updateSelectionCount();
    }

    // Synchronise le compteur de sélection + état du bouton submit
    function updateSelectionCount() {
        var checked = $$('.uv-row-check:checked').filter(function (cb) {
            // Ignore les coches dans des lignes/cartes masquées
            var row  = cb.closest('.uv-row');
            var card = cb.closest('.uv-card');
            return row  && row.style.display  !== 'none'
                && card && card.style.display !== 'none';
        });
        var n = checked.length;

        var elSel = $('#uv-count-selected');
        var elBar = $('#uv-actionbar-count');
        if (elSel) elSel.textContent = n;
        if (elBar) elBar.textContent = n;

        var actionbar = $('#uv-actionbar');
        if (actionbar) actionbar.classList.toggle('has-selection', n > 0);

        var submit = $('#uv-submit');
        if (submit) submit.disabled = (n === 0);

        // Surligne la ligne sélectionnée
        $$('.uv-row').forEach(function (row) {
            var cb = row.querySelector('.uv-row-check');
            row.classList.toggle('is-checked', !!(cb && cb.checked));
        });

        // Met à jour l'état des "tout cocher" par carte (indéterminé / coché / non coché)
        $$('.uv-card').forEach(function (card) {
            var rows = $$('.uv-row', card).filter(function (r) { return r.style.display !== 'none'; });
            var total = rows.length;
            var checkedN = rows.filter(function (r) {
                var cb = r.querySelector('.uv-row-check');
                return cb && cb.checked;
            }).length;
            var head = card.querySelector('.uv-checkall-input');
            if (head) {
                head.checked = (total > 0 && checkedN === total);
                head.indeterminate = (checkedN > 0 && checkedN < total);
            }
        });
    }

    // Bind UI
    function bind() {
        var search = $('#uv-search');
        if (search) {
            search.value = state.q;
            search.addEventListener('input', function () {
                state.q = search.value;
                saveState();
                applyFilters();
            });
        }

        bindSelect('#uv-filter-date',    'date');
        bindSelect('#uv-filter-famille', 'famille');
        bindSelect('#uv-filter-type',    'type');

        // Reset
        var reset = $('#uv-reset');
        if (reset) {
            reset.addEventListener('click', function () {
                state.q = ''; state.date = '*'; state.famille = '*'; state.type = '*';
                if (search) search.value = '';
                ['#uv-filter-date', '#uv-filter-famille', '#uv-filter-type'].forEach(function (id) {
                    var el = $(id); if (el) el.value = '*';
                });
                saveState();
                applyFilters();
            });
        }

        // Tout cocher par carte
        $$('.uv-checkall-input').forEach(function (master) {
            master.addEventListener('change', function () {
                var card = master.closest('.uv-card');
                if (!card) return;
                $$('.uv-row', card).forEach(function (row) {
                    if (row.style.display === 'none') return;
                    var cb = row.querySelector('.uv-row-check');
                    if (cb) cb.checked = master.checked;
                });
                updateSelectionCount();
            });
        });

        // Suivi des cases individuelles
        $$('.uv-row-check').forEach(function (cb) {
            cb.addEventListener('change', updateSelectionCount);
        });

        // Confirmation soft si formulaire vide (sécurité)
        var form = $('#uv-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                var n = $$('.uv-row-check:checked').length;
                if (n === 0) {
                    e.preventDefault();
                }
            });
        }
    }

    function bindSelect(sel, key) {
        var el = $(sel);
        if (!el) return;
        if (state[key] && el.querySelector('option[value="' + cssEscape(state[key]) + '"]')) {
            el.value = state[key];
        }
        el.addEventListener('change', function () {
            state[key] = el.value;
            saveState();
            applyFilters();
        });
    }

    // Très basique, suffisant pour des id numériques / dates
    function cssEscape(v) { return String(v).replace(/"/g, '\\"'); }

    // Boot
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        if (!$('#uv-form')) return; // pas sur la bonne page
        loadState();
        bind();
        applyFilters();
    }
})();
