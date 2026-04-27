/* =========================================================================
 * mobile-menu.js  —  Drawer mobile moderne pour le site travaux
 * ---------------------------------------------------------------------------
 * Remplace l'ancienne approche tinynav (<select>) par un vrai menu tiroir :
 *   - Bouton hamburger fixe en haut à droite
 *   - Panneau latéral qui glisse depuis la droite avec overlay sombre
 *   - Liens clonés depuis .nicdark_menu (menu principal) + .navbar-nav (barre utilisateur)
 *   - Fermeture : overlay, croix, tap sur lien, touche Échap, bouton retour Android
 *   - Sous-menus repliables (accordéon) pour les entrées .sub-menu
 *   - Barre de recherche intégrée si présente sur la page
 *
 * Dépendances : jQuery (déjà chargé par Bootstrap_tools)
 * Activation  : breakpoint CSS (<= 767px), driven by mobile-override.css
 * ========================================================================= */
(function ($) {
    'use strict';

    // Breakpoint mobile (doit rester synchro avec mobile-override.css)
    var MOBILE_BP = 767;

    function isMobile() {
        return window.innerWidth <= MOBILE_BP;
    }

    // ---------------------------------------------------------------------
    // Construction du DOM : hamburger, overlay, drawer
    // ---------------------------------------------------------------------
    function buildUi() {
        if ($('.mobile-menu-toggle').length) return; // déjà construit

        // Bouton hamburger (fixé en haut à droite via CSS)
        var $toggle = $(
            '<button type="button" class="mobile-menu-toggle" ' +
                'aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobileDrawer">' +
                '<span class="bar"></span>' +
                '<span class="bar"></span>' +
                '<span class="bar"></span>' +
            '</button>'
        );

        // Overlay semi-transparent derrière le drawer
        var $overlay = $('<div class="mobile-menu-overlay" aria-hidden="true"></div>');

        // Drawer lui-même
        var $drawer = $(
            '<aside id="mobileDrawer" class="mobile-menu-drawer" ' +
                'role="dialog" aria-modal="true" aria-label="Menu principal" aria-hidden="true">' +
                '<header class="mmd-header">' +
                    '<span class="mmd-title">Menu</span>' +
                    '<button type="button" class="mmd-close" aria-label="Fermer le menu">&times;</button>' +
                '</header>' +
                '<div class="mmd-body"></div>' +
            '</aside>'
        );

        $('body').append($toggle).append($overlay).append($drawer);
    }

    // ---------------------------------------------------------------------
    // Remplissage du drawer à partir des menus existants
    // ---------------------------------------------------------------------
    function populateDrawer() {
        var $body = $('#mobileDrawer .mmd-body');
        if (!$body.length) return;

        $body.empty();

        // --- 1) Formulaire de recherche (si présent) -------------------
        var $searchForm = $('.nicdark_displaynone_responsive form.form-inline').first();
        if ($searchForm.length) {
            var $section = $('<div class="mmd-section mmd-search"></div>');
            var $formClone = $searchForm.clone(true, true);
            // On retire les classes float et largeurs Bootstrap qui cassent le layout mobile
            $formClone.removeClass('form-inline').addClass('mmd-search-form');
            $section.append($formClone);
            $body.append($section);
        }

        // --- 2) Menu principal (nicdark_menu) --------------------------
        var $mainMenu = $('ul.nicdark_menu').first();
        if ($mainMenu.length) {
            var $section = $('<nav class="mmd-section mmd-nav" aria-label="Navigation principale"></nav>');
            var $list = $('<ul class="mmd-list"></ul>');
            $mainMenu.children('li').each(function () {
                $list.append(buildItem($(this)));
            });
            $section.append($list);
            $body.append($section);
        }

        // --- 3) Menu utilisateur (bar du haut : compte, login/logout, langue…)
        var $userNavItems = $('.nicdark_displaynone_responsive ul.navbar-nav').first().children('li');
        if ($userNavItems.length) {
            var $section = $('<nav class="mmd-section mmd-user" aria-label="Menu utilisateur"></nav>');
            var $list = $('<ul class="mmd-list"></ul>');
            $userNavItems.each(function () {
                var $li = $(this);
                // On ignore le <li> qui ne contient que le formulaire de recherche (déjà traité)
                if ($li.find('form').length && $li.text().replace(/\s/g, '') === '') return;
                if ($li.find('> form').length && !$li.find('a').length) return;
                $list.append(buildItem($li));
            });
            if ($list.children().length) {
                $section.append($list);
                $body.append($section);
            }
        }
    }

    /**
     * Transforme un <li> d'un menu existant en item du drawer.
     * Gère les sous-menus (.sub-menu ou .dropdown-menu) en accordéon.
     */
    function buildItem($sourceLi) {
        var $subMenu = $sourceLi.children('ul.sub-menu, .dropdown-menu').first();
        var $anchor  = $sourceLi.children('a').first();

        // Certains <li> Bootstrap ont le lien et le dropdown côte à côte
        if (!$anchor.length) {
            $anchor = $sourceLi.find('> a, .nav-link, .dropdown-toggle').first();
        }
        if (!$subMenu.length) {
            $subMenu = $sourceLi.find('.dropdown-menu').first();
        }

        var $item = $('<li class="mmd-item"></li>');

        if ($subMenu.length) {
            // Entrée avec sous-menu : on ajoute un bouton d'accordéon
            var $row = $('<div class="mmd-row mmd-row--has-sub"></div>');

            var href = ($anchor.attr('href') || '#').trim();
            var label = ($anchor.text() || '').trim() || 'Menu';

            if (href && href !== '#') {
                $row.append(
                    $('<a class="mmd-link"></a>').attr('href', href).text(label)
                );
            } else {
                $row.append($('<span class="mmd-link mmd-link--static"></span>').text(label));
            }

            $row.append(
                $('<button type="button" class="mmd-sub-toggle" aria-expanded="false" aria-label="Afficher le sous-menu">' +
                    '<span class="mmd-chevron" aria-hidden="true"></span>' +
                '</button>')
            );

            $item.append($row);

            // Liste du sous-menu
            var $subList = $('<ul class="mmd-sublist" hidden></ul>');
            $subMenu.children('li, a').each(function () {
                var $el = $(this);
                if ($el.is('a')) {
                    // Cas Bootstrap dropdown-item : <a> directement enfant
                    $subList.append(buildSubItem($el));
                } else {
                    var $subAnchor = $el.children('a').first();
                    if (!$subAnchor.length) return;
                    $subList.append(buildSubItem($subAnchor));
                }
            });
            $item.append($subList);
        } else {
            // Entrée simple
            var href  = ($anchor.attr('href') || '#').trim();
            var label = ($anchor.text() || '').trim();
            // Certains <a> contiennent uniquement des icônes ; on garde le HTML interne
            var $link = $('<a class="mmd-link"></a>').attr('href', href || '#');
            if (label) {
                $link.text(label);
            } else {
                // Fallback : on récupère le HTML interne (icône+label)
                $link.html($anchor.html() || '&nbsp;');
            }
            $item.append($('<div class="mmd-row"></div>').append($link));
        }

        return $item;
    }

    function buildSubItem($a) {
        var href  = ($a.attr('href') || '#').trim();
        var label = ($a.text() || '').trim() || href;
        return $('<li class="mmd-subitem"></li>').append(
            $('<a class="mmd-sublink"></a>').attr('href', href).text(label)
        );
    }

    // ---------------------------------------------------------------------
    // Ouverture / fermeture
    // ---------------------------------------------------------------------
    var lastFocusedBeforeOpen = null;

    function openDrawer() {
        var $toggle  = $('.mobile-menu-toggle');
        var $overlay = $('.mobile-menu-overlay');
        var $drawer  = $('#mobileDrawer');

        lastFocusedBeforeOpen = document.activeElement;

        $('body').addClass('mobile-menu-open');
        $toggle.addClass('is-open').attr('aria-expanded', 'true').attr('aria-label', 'Fermer le menu');
        $overlay.addClass('is-visible').attr('aria-hidden', 'false');
        $drawer.addClass('is-open').attr('aria-hidden', 'false');

        // État d'historique pour que le bouton retour Android ferme le menu
        try {
            if (!(history.state && history.state.mobileMenuOpen)) {
                history.pushState({ mobileMenuOpen: true }, '');
            }
        } catch (e) { /* pas bloquant */ }

        // Focus sur la croix pour l'accessibilité clavier
        setTimeout(function () { $drawer.find('.mmd-close').focus(); }, 50);
    }

    function closeDrawer(popped) {
        var $toggle  = $('.mobile-menu-toggle');
        var $overlay = $('.mobile-menu-overlay');
        var $drawer  = $('#mobileDrawer');

        $('body').removeClass('mobile-menu-open');
        $toggle.removeClass('is-open').attr('aria-expanded', 'false').attr('aria-label', 'Ouvrir le menu');
        $overlay.removeClass('is-visible').attr('aria-hidden', 'true');
        $drawer.removeClass('is-open').attr('aria-hidden', 'true');

        // Si on ferme via notre UI (pas via popstate), on "consomme" l'état
        if (!popped) {
            try {
                if (history.state && history.state.mobileMenuOpen) {
                    history.back();
                }
            } catch (e) { /* pas bloquant */ }
        }

        if (lastFocusedBeforeOpen && typeof lastFocusedBeforeOpen.focus === 'function') {
            try { lastFocusedBeforeOpen.focus(); } catch (e) {}
        }
    }

    function toggleDrawer() {
        if ($('#mobileDrawer').hasClass('is-open')) {
            closeDrawer();
        } else {
            openDrawer();
        }
    }

    // ---------------------------------------------------------------------
    // Branchement des événements
    // ---------------------------------------------------------------------
    function bindEvents() {
        var $body = $('body');

        // Ouvrir / fermer via le bouton hamburger
        $body.on('click.mmDrawer', '.mobile-menu-toggle', function (e) {
            e.preventDefault();
            toggleDrawer();
        });

        // Fermer via la croix
        $body.on('click.mmDrawer', '.mmd-close', function (e) {
            e.preventDefault();
            closeDrawer();
        });

        // Fermer en tapant sur l'overlay
        $body.on('click.mmDrawer', '.mobile-menu-overlay', function () {
            closeDrawer();
        });

        // Fermer au clic sur un lien réel (on laisse la navigation se faire)
        $body.on('click.mmDrawer', '#mobileDrawer .mmd-link[href], #mobileDrawer .mmd-sublink[href]', function () {
            var href = ($(this).attr('href') || '').trim();
            if (href && href !== '#') {
                closeDrawer();
            }
        });

        // Accordéon des sous-menus
        $body.on('click.mmDrawer', '.mmd-sub-toggle', function (e) {
            e.preventDefault();
            var $btn     = $(this);
            var expanded = $btn.attr('aria-expanded') === 'true';
            var $sublist = $btn.closest('.mmd-item').children('.mmd-sublist');

            $btn.attr('aria-expanded', expanded ? 'false' : 'true');
            if (expanded) {
                $sublist.attr('hidden', '');
            } else {
                $sublist.removeAttr('hidden');
            }
        });

        // Touche Échap
        $(document).on('keydown.mmDrawer', function (e) {
            if (e.key === 'Escape' && $('#mobileDrawer').hasClass('is-open')) {
                closeDrawer();
            }
        });

        // Bouton retour Android / historique
        $(window).on('popstate.mmDrawer', function () {
            if ($('#mobileDrawer').hasClass('is-open')) {
                closeDrawer(true);
            }
        });

        // Redimensionnement : si on repasse en desktop, on ferme le drawer
        var resizeTimer;
        $(window).on('resize.mmDrawer', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function () {
                if (!isMobile() && $('#mobileDrawer').hasClass('is-open')) {
                    closeDrawer();
                }
            }, 150);
        });
    }

    // ---------------------------------------------------------------------
    // Initialisation
    // ---------------------------------------------------------------------
    $(function () {
        buildUi();
        populateDrawer();
        bindEvents();

        // Si tinynav a déjà créé un <select>, on le masque via CSS (cf. mobile-override.css)
        // mais on s'assure qu'il ne capture plus aucune interaction tactile.
        $('select.tinynav').attr('tabindex', '-1').attr('aria-hidden', 'true');
    });

})(jQuery);
