/*
 * assets/js/admwork_register.js — v5
 * · Toggle Cartes / Liste avec mémorisation localStorage
 * · Filtrage et recherche appliqués aux deux vues simultanément
 * · Accordéons passées
 */
(function () {
    'use strict';

    var LS_KEY       = 'aw_view_pref';   // clé localStorage
    var currentView  = 'cards';          // valeur par défaut
    var currentFilter= '*';
    var currentSearch= '';

    /* ============================================================
       1. TOGGLE VUE CARTES / LISTE
       ============================================================ */

    window.awSetView = function (view) {
        currentView = view;

        var elCards  = document.getElementById('aw-view-cards');
        var elList   = document.getElementById('aw-view-list');
        var btnCards = document.getElementById('btn-cards');
        var btnList  = document.getElementById('btn-list');
        if (!elCards || !elList) return;

        if (view === 'list') {
            elCards.style.display = 'none';
            elList.style.display  = 'block';
            btnCards.classList.remove('active');
            btnList.classList.add('active');
        } else {
            elList.style.display  = 'none';
            elCards.style.display = 'block';
            btnList.classList.remove('active');
            btnCards.classList.add('active');
        }

        /* Mémorise le choix */
        try { localStorage.setItem(LS_KEY, view); } catch(e) {}

        /* Réapplique le filtre dans la nouvelle vue */
        applyFilters();
    };

    /* ============================================================
       2. FILTRAGE + RECHERCHE
          Agit sur :
          · .aw-card        (vue cartes, à venir)
          · .aw-list-row    (vue liste à venir + accordéons passées)
          · .aw-list-sep    (séparateurs de mois liste)
       ============================================================ */

    function matchesFilter(el) {
        var f = currentFilter;
        var q = currentSearch;
        var ok = true;

        if (f !== '*') {
            switch (f) {
                case 'dispo': ok = el.classList.contains('dispo'); break;
                case 'mine':  ok = el.classList.contains('mine');  break;
                default:
                    if (f.indexOf('type:') === 0) {
                        ok = el.dataset.type === f.slice(5);
                    }
            }
        }
        if (ok && q) {
            var t = el.dataset.title || '';
            var e = el.dataset.ecole || '';
            ok = t.indexOf(q) !== -1 || e.indexOf(q) !== -1;
        }
        return ok;
    }

    function applyFilters() {
        /* Cartes à venir */
        document.querySelectorAll('.aw-cards-grid .aw-card').forEach(function (c) {
            c.style.display = matchesFilter(c) ? '' : 'none';
        });

        /* Lignes liste à venir */
        document.querySelectorAll('#aw-view-list .aw-list-row').forEach(function (r) {
            r.style.display = matchesFilter(r) ? '' : 'none';
        });

        /* Séparateurs de mois : masquer si toutes leurs lignes sont cachées */
        document.querySelectorAll('.aw-list-sep').forEach(function (sep) {
            var sib = sep.nextElementSibling;
            var vis = false;
            while (sib && !sib.classList.contains('aw-list-sep')) {
                if (sib.classList.contains('aw-list-row') && sib.style.display !== 'none') {
                    vis = true; break;
                }
                sib = sib.nextElementSibling;
            }
            sep.style.display = vis ? '' : 'none';
        });

        /* Lignes passées dans accordéons */
        document.querySelectorAll('.aw-month-body .aw-list-row').forEach(function (r) {
            r.style.display = matchesFilter(r) ? '' : 'none';
        });

        /* Masque les accordéons entièrement vides */
        document.querySelectorAll('.aw-month-body').forEach(function (body) {
            var vis = Array.from(body.querySelectorAll('.aw-list-row'))
                .some(function (r) { return r.style.display !== 'none'; });
            var hdr = body.previousElementSibling;
            if (hdr && hdr.classList.contains('aw-month-header')) {
                hdr.style.display = vis ? '' : 'none';
                if (!vis && body.classList.contains('is-open')) {
                    body.style.display = 'none';
                    body.classList.remove('is-open');
                    hdr.classList.remove('is-open');
                }
            }
        });
    }

    window.awFilter = function (el, filter) {
        currentFilter = filter;
        document.querySelectorAll('.aw-chip').forEach(function (c) { c.classList.remove('is-active'); });
        el.classList.add('is-active');
        applyFilters();
    };

    window.awSearch = function (val) {
        currentSearch = val.toLowerCase().trim();
        applyFilters();
    };

    /* ============================================================
       3. ACCORDÉONS PASSÉES
       ============================================================ */

    window.awToggleMonth = function (targetId, headerEl) {
        var body   = document.getElementById(targetId);
        var isOpen = headerEl.classList.contains('is-open');

        document.querySelectorAll('.aw-month-header.is-open').forEach(function (h) {
            if (h === headerEl) return;
            h.classList.remove('is-open');
            var b = h.nextElementSibling;
            if (b && b.classList.contains('aw-month-body')) {
                b.style.display = 'none';
                b.classList.remove('is-open');
            }
        });

        if (isOpen) {
            headerEl.classList.remove('is-open');
            body.style.display = 'none';
            body.classList.remove('is-open');
        } else {
            headerEl.classList.add('is-open');
            body.style.display = 'block';
            body.classList.add('is-open');
            applyFilters();
        }
    };

    /* ============================================================
       4. INIT — restaure la préférence sauvegardée
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        var saved = 'cards';
        try { saved = localStorage.getItem(LS_KEY) || 'cards'; } catch(e) {}
        awSetView(saved);   /* positionne les boutons + applique les filtres */
    });

})();
