/**
 * assets/js/admwork_register.js — v3
 *
 * CORRECTIONS v3 :
 *  · Filtrage via data-attributes (pas Isotope) → compatible flex/inline-block
 *  · awToggleMonth() exposé globalement pour onclick HTML
 *  · FullCalendar : init différée robuste (attend que la lib soit prête)
 *  · Tooltip en position fixed (pas absolute) → pas de scroll-offset bug
 */

(function () {
    "use strict";

    /* ============================================================
       STATE
       ============================================================ */
    var currentFilter = '*';
    var currentSearch = '';
    var calInitialized = false;

    /* ============================================================
       1. FILTRAGE + RECHERCHE
       ============================================================ */

    function applyFilters() {
        var f = currentFilter;  // ex: '*', 'dispo', 'mine', 'archived', 'TRA'
        var q = currentSearch;

        document.querySelectorAll('.aw-card').forEach(function (card) {
            var show = true;

            /* Filtre par catégorie */
            if (f !== '*') {
                switch (f) {
                    case 'dispo':    show = card.classList.contains('dispo');    break;
                    case 'mine':     show = card.classList.contains('mine');     break;
                    case 'archived': show = card.classList.contains('archived'); break;
                    default:         show = (card.dataset.type === f);           break;
                }
            }

            /* Filtre par recherche texte */
            if (show && q) {
                var title = (card.dataset.title || '');
                var ecole = (card.dataset.ecole || '');
                show = title.indexOf(q) !== -1 || ecole.indexOf(q) !== -1;
            }

            card.style.display = show ? '' : 'none';
        });

        /* Masque les accordéons vides */
        document.querySelectorAll('.aw-month-body').forEach(function (body) {
            var visible = body.querySelectorAll('.aw-card:not([style*="display: none"])').length;
            var id = body.id;
            var header = document.querySelector('[onclick*="' + id + '"]');
            if (header) header.style.display = (visible > 0) ? '' : 'none';
            if (visible === 0 && body.classList.contains('is-open')) {
                body.style.display = 'none';
                body.classList.remove('is-open');
                if (header) header.classList.remove('is-open');
            }
        });
    }

    /* Exposés globalement pour les onclick inline */
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
       2. ACCORDÉONS MOIS
       ============================================================ */

    window.awToggleMonth = function (targetId, headerEl) {
        var body   = document.getElementById(targetId);
        var isOpen = headerEl.classList.contains('is-open');

        /* Ferme tous les autres */
        document.querySelectorAll('.aw-month-header.is-open').forEach(function (h) {
            if (h !== headerEl) {
                h.classList.remove('is-open');
                var otherId = h.getAttribute('onclick').match(/'([^']+)'/)[1];
                var other = document.getElementById(otherId);
                if (other) { other.style.display = 'none'; other.classList.remove('is-open'); }
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
            applyFilters(); /* réapplique le filtre dans la section qui vient d'ouvrir */
        }
    };

    /* ============================================================
       3. BASCULE CARTES / CALENDRIER
       ============================================================ */

    window.awSwitchView = function (view) {
        var cards = document.getElementById('view-cards');
        var cal   = document.getElementById('view-calendar');
        var btnC  = document.getElementById('btn-view-cards');
        var btnCal= document.getElementById('btn-view-calendar');

        if (view === 'calendar') {
            cards.style.display = 'none';
            cal.style.display   = 'block';
            btnC.classList.remove('active');
            btnCal.classList.add('active');
            initCalendar();
        } else {
            cal.style.display   = 'none';
            cards.style.display = 'block';
            btnCal.classList.remove('active');
            btnC.classList.add('active');
        }
    };

    /* ============================================================
       4. FULLCALENDAR — init robuste
          FullCalendar est chargé via le controller en <head>,
          donc il est disponible dès que le DOM est prêt.
          Si pour une raison quelconque il n'est pas encore parsé,
          on réessaie toutes les 200ms (max 15s).
       ============================================================ */

    var calRetries = 0;

    function initCalendar() {
        if (calInitialized) {
            /* Déjà initialisé : force un redimensionnement */
            if (window._awCalendar) window._awCalendar.updateSize();
            return;
        }

        if (typeof FullCalendar === 'undefined') {
            if (calRetries < 75) {
                calRetries++;
                setTimeout(initCalendar, 200);
            } else {
                document.getElementById('aw-fullcalendar').innerHTML =
                    '<p style="color:#e16c6c;padding:20px;">FullCalendar non chargé. Vérifiez la connexion.</p>';
            }
            return;
        }

        calInitialized = true;

        var calEl = document.getElementById('aw-fullcalendar');
        if (!calEl) return;

        var cal = new FullCalendar.Calendar(calEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            firstDay: 1,
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,listMonth'
            },
            buttonText: {
                today: "Aujourd'hui",
                month: 'Mois',
                list:  'Liste'
            },
            events: window.awCalendarEvents || [],
            height: 'auto',

            eventDidMount: function (info) {
                if (info.event.extendedProps.is_past) {
                    info.el.classList.add('ev-past');
                }

                /* Tooltip */
                var ep  = info.event.extendedProps;
                var tip = document.createElement('div');
                tip.className = 'aw-cal-tooltip';
                var h = (ep.heure_deb && ep.heure_fin) ? ep.heure_deb + ' → ' + ep.heure_fin : '';
                tip.innerHTML =
                    '<strong>' + info.event.title + '</strong><br>' +
                    (ep.ecole    ? ep.ecole + '<br>' : '') +
                    (ep.type_lbl ? ep.type_lbl + '<br>' : '') +
                    (h           ? '&#128336; ' + h + '<br>' : '') +
                    ep.inscrits + ' / ' + ep.max + ' inscrits';

                info.el.addEventListener('mouseenter', function (e) {
                    document.body.appendChild(tip);
                    moveTip(tip, e);
                });
                info.el.addEventListener('mousemove',  function (e) { moveTip(tip, e); });
                info.el.addEventListener('mouseleave', function ()  {
                    if (tip.parentNode) tip.parentNode.removeChild(tip);
                });
            },

            eventClick: function (info) {
                info.jsEvent.preventDefault();
                var url = info.event.extendedProps.url;
                if (url) window.location.href = url;
            }
        });

        cal.render();
        window._awCalendar = cal;
    }

    function moveTip(tip, e) {
        var h = tip.offsetHeight || 80;
        tip.style.top  = (e.clientY - h - 14) + 'px';
        tip.style.left = (e.clientX - 14) + 'px';
    }

    /* ============================================================
       5. INIT
       ============================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        applyFilters(); /* état initial = Tous */
    });

})();
