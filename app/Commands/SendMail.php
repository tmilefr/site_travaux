<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Envoie les e-mails en attente dans la file d'envoi
 *
 * Usage : php spark cron:sendmail [taille]
 */
class SendMail extends BaseCommand
{
    protected $group       = 'Travaux';
    protected $name        = 'cron:sendmail';
    protected $description = 'Envoie les e-mails en attente dans la file d\'envoi';
    protected $usage       = 'cron:sendmail [taille]';
    protected $arguments   = ['taille' => "Nombre maximal d'e-mails à envoyer (défaut 10)"];

    public function run(array $params)
    {
        $arg = (int) ($params[0] ?? 10);
        $ok  = service('cronJobs')
            ->setOutput(static fn (string $line) => CLI::write($line))
            ->locked(static fn ($jobs) => $jobs->sendmail($arg));

        return $ok ? EXIT_SUCCESS : EXIT_ERROR;
    }
}
