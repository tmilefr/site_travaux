/**
 * assets/js/admwork_register.js  — v2
 * Gestion vue sessions de travaux :
 *   · Filtres chips (sans Isotope sur les passées qui sont dans des accordéons)
 *   · Recherche live
 *   · Accordéons mois
 *   · Bascule Cartes / Calendrier
 *   · FullCalendar init + tooltip
 */

(function ($) {
    "use strict";

    var currentFilter  = '*';
    var currentSearch  = '';
    var calInitialized = false;

    /* ============================================================
       1. FILTRAGE  (remplace Isotope — plus adapté à la structure flex)
       ============================================================ */

    function applyFilters() {
        var f = currentFilter;
        var q = currentSearch.toLowerCase().trim();

        $('.aw-card').each(function () {
            var $card    = $(this);
            var matchF   = (f === '*') || $card.hasClass(f.replace('.', ''));
            var matchQ   = !q || $card.find('.aw-card-title').text().toLowerCase().indexOf(q) !== -1
                              || $card.find('.aw-meta-row').text().toLowerCase().indexOf(q) !== -1;
            $card.toggle(matchF && matchQ);
        });

        /* Masquer les accordéons vides après filtrage */
        $('.aw-month-body').each(function () {
            var $body    = $(this);
            var visible  = $body.find('.aw-card:visible').length;
            var $header  = $('.aw-month-header[data-target="' + $body.attr('id') + '"]');
            $header.toggle(visible > 0);
            if (visible === 0 && $body.hasClass('is-open')) {
                $body.hide().removeClass('is-open');
                $header.removeClass('is-open');
            }
        });
    }

    window.awFilter = function (el, filter) {
        currentFilter = filter;
        $('.aw-chip').removeClass('is-active');
        $(el).addClass('is-active');
        applyFilters();
    };

    window.awSearch = function (val) {
        currentSearch = val;
        applyFilters();
    };

    /* ============================================================
       2. ACCORDÉONS MOIS
       ============================================================ */

    $(document).on('click', '.aw-month-header', function () {
        var $header  = $(this);
        var targetId = $header.data('target');
        var $body    = $('#' + targetId);
        var isOpen   = $header.hasClass('is-open');

        /* Ferme les autres */
        $('.aw-month-header.is-open').not($header).removeClass('is-open');
        $('.aw-month-body.is-open').not($body).slideUp(200, function () {
            $(this).removeClass('is-open');
        });

        if (isOpen) {
            $header.removeClass('is-open');
            $body.slideUp(200, function () { $body.removeClass('is-open'); });
        } else {
            $header.addClass('is-open');
            $body.slideDown(250, function () {
                $body.addClass('is-open');
                applyFilters(); /* réapplique le filtre actif dans la nouvelle section ouverte */
            });
        }
    });

    /* ============================================================
       3. BASCULE CARTES / CALENDRIER
       ============================================================ */

    window.awSwitchView = function (view) {
        if (view === 'calendar') {
            $('#view-cards').hide();
            $('#view-calendar').show();
            $('#btn-view-cards').removeClass('active');
            $('#btn-view-calendar').addClass('active');
            initCalendar();
        } else {
            $('#view-calendar').hide();
            $('#view-cards').show();
            $('#btn-view-calendar').removeClass('active');
            $('#btn-view-cards').addClass('active');
        }
    };

    /* ============================================================
       4. FULLCALENDAR
       ============================================================ */

    function initCalendar() {
        if (calInitialized) return;
        if (typeof FullCalendar === 'undefined') { setTimeout(initCalendar, 300); return; }
        calInitialized = true;

        var calEl = document.getElementById('aw-fullcalendar');
        if (!calEl) return;

        var calendar = new FullCalendar.Calendar(calEl, {
            locale: 'fr',
            initialView: 'dayGridMonth',
            firstDay: 1,
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,listMonth'
            },
            buttonText: { today: "Aujourd'hui", month: 'Mois', list: 'Liste' },
            events: (typeof awCalendarEvents !== 'undefined') ? awCalendarEvents : [],
            height: 'auto',

            eventDidMount: function (info) {
                if (info.event.extendedProps.is_past) info.el.classList.add('ev-past');

                /* Tooltip */
                var ep  = info.event.extendedProps;
                var h   = (ep.heure_deb && ep.heure_fin) ? ep.heure_deb + ' → ' + ep.heure_fin : '';
                var tip = $('<div class="aw-cal-tooltip"></div>').html(
                    '<strong>' + info.event.title + '</strong><br>' +
                    (ep.ecole ? ep.ecole + '<br>' : '') +
                    (ep.type_lbl ? ep.type_lbl + '<br>' : '') +
                    (h ? '&#128336; ' + h + '<br>' : '') +
                    ep.inscrits + '/' + ep.max + ' inscrits'
                );
                $(info.el)
                    .on('mouseenter', function (e) { $('body').append(tip); positionTip(tip, e); })
                    .on('mousemove',  function (e) { positionTip(tip, e); })
                    .on('mouseleave', function ()  { tip.remove(); });
            },

            eventClick: function (info) {
                info.jsEvent.preventDefault();
                var url = info.event.extendedProps.url;
                if (url) window.location.href = url;
            }
        });

        calendar.render();
    }

    function positionTip($tip, e) {
        $tip.css({ top: e.clientY - $tip.outerHeight() - 14, left: e.clientX - 14 });
    }

    /* ============================================================
       5. INIT
       ============================================================ */

    $(document).ready(function () {
        applyFilters(); /* filtre initial = Tous */
    });

})(jQuery);
