<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Envoie aux référents le lien de validation de leur session à venir
 *
 * Usage : php spark cron:ref-validation [jours]
 */
class RefValidationMails extends BaseCommand
{
    protected $group       = 'Travaux';
    protected $name        = 'cron:ref-validation';
    protected $description = 'Envoie aux référents le lien de validation de leur session à venir';
    protected $usage       = 'cron:ref-validation [jours]';
    protected $arguments   = ['jours' => 'Nombre de jours avant la session (défaut 7)'];

    public function run(array $params)
    {
        $arg = (int) ($params[0] ?? 7);
        $ok  = service('cronJobs')
            ->setOutput(static fn (string $line) => CLI::write($line))
            ->locked(static fn ($jobs) => $jobs->sendRefValidationMails($arg));

        return $ok ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
