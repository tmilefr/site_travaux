<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Alerte les familles abonnées des nouvelles sessions publiées
 *
 * Usage : php spark cron:session-alerts [jours]
 */
class NewSessionAlerts extends BaseCommand
{
    protected $group       = 'Travaux';
    protected $name        = 'cron:session-alerts';
    protected $description = 'Alerte les familles abonnées des nouvelles sessions publiées';
    protected $usage       = 'cron:session-alerts [jours]';
    protected $arguments   = ['jours' => 'Ne notifie que les sessions des N prochains jours (défaut 0 = sans borne)'];

    public function run(array $params)
    {
        $arg = (int) ($params[0] ?? 0);
        $ok  = service('cronJobs')
            ->setOutput(static fn (string $line) => CLI::write($line))
            ->locked(static fn ($jobs) => $jobs->sendNewSessionAlerts($arg));

        return $ok ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
