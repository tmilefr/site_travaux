<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

/*
 * --------------------------------------------------------------------
 * Application Events
 * --------------------------------------------------------------------
 * Events allow you to tap into the execution of the program without
 * modifying or extending core files. This file provides a central
 * location to define your events, though they can always be added
 * at run-time, also, if needed.
 *
 * You create code that can execute by subscribing to events with
 * the 'on()' method. This accepts any form of callable, including
 * Closures, that will be executed when the event is triggered.
 *
 * Example:
 *      Events::on('create', [$myInstance, 'myMethod']);
 */

Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        $value = ini_get('zlib.output_compression');

        if (filter_var($value, FILTER_VALIDATE_BOOLEAN) || (int) $value > 0) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});

/*
 * Controle d'acces (ACL) : equivalent du hook CI3 "post_controller_constructor"
 * (ancien Loginchecker). Execute une fois le controleur instancie.
 */
Events::on('post_controller_constructor', static function (): void {
    $ci = \App\Libraries\Compat\Ci3::get();
    if ($ci !== null && isset($ci->acl)) {
        $ci->acl->Route();
    }
});

/*
 * Tolerance aux avertissements PHP (E_WARNING, E_NOTICE ...).
 *
 * L'application CI3 tournait avec error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED ...)
 * et un avertissement (cle de tableau ou propriete absente) n'interrompait pas la page.
 * CI4 transforme tout avertissement en exception : on conserve le comportement
 * historique en journalisant simplement ces avertissements.
 */
Events::on('pre_system', static function (): void {
    $previous = set_error_handler(null);
    set_error_handler(static function (int $severity, string $message, ?string $file = null, ?int $line = null) use ($previous) {
        $lenient = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE;
        if (($severity & $lenient) !== 0 && (error_reporting() & $severity) !== 0) {
            log_message('warning', '[{severity}] {message} in {file}:{line}', [
                'severity' => $severity,
                'message'  => $message,
                'file'     => $file,
                'line'     => $line,
            ]);

            return true;
        }

        return $previous !== null ? $previous($severity, $message, $file, $line) : false;
    });
});
