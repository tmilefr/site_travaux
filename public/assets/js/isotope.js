/*
 * Remplacement de assets/js/isotope.js
 * Permet à Isotope de gérer PLUSIEURS conteneurs .nicdark_masonry_container
 * (un pour "À venir", un pour "Passées") avec les mêmes boutons de filtre.
 */
(function($) {
    "use strict";

    $(window).on('load', function () {

        // Initialisation Isotope sur CHAQUE conteneur présent
        var $containers = $('.nicdark_masonry_container').each(function () {
            $(this).isotope({
                itemSelector: '.nicdark_masonry_item',
                layoutMode: 'fitRows'    // plus prévisible que masonry pour des cartes de hauteur identique
            });
        });

        // Un seul set de boutons de filtre, appliqué à tous les conteneurs
        $('.nicdark_masonry_btns a').on('click', function (e) {
            e.preventDefault();
            var filterValue = $(this).attr('data-filter');

            // état visuel actif
            $('.nicdark_masonry_btns a').removeClass('is-active');
            $(this).addClass('is-active');

            $containers.isotope({ filter: filterValue });
        });

        // Déclenchement initial (filtre "Tous")
        $('.nicdark_simulate_click').trigger('click');
    });

})(jQuery);
