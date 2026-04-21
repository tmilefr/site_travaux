/* mobile-menu.js
 * Active tinynav sur le menu principal nicdark
 * (le plugin tinynav.min.js est déjà chargé par Bootstrap_tools.php)
 */
(function ($) {
    $(function () {
        if (typeof $.fn.tinyNav === 'function') {
            $('.nicdark_menu').tinyNav({
                active: 'selected',
                header: 'Menu',
                indent: '— '
            });
        }
    });
})(jQuery);
