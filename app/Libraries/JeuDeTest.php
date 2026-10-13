<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * Rechargement du jeu de test de recette (database/sql/jeu_de_test/) depuis l'application.
 *
 * Équivalent PHP de charger_jeu_de_test.sh : calcule les hashes du mot de passe, fixe l'année
 * civile d'après Config\Travaux, puis rejoue 10_jeu_de_test.sql (qui purge ses propres lignes).
 *
 * Désactivé par défaut : il faut `travaux.allowTestData = true` dans le .env d'un environnement de
 * recette. Toutes les lignes touchées sont dans la plage d'identifiants réservée 9000–9999.
 */
class JeuDeTest
{
    public const ID_MIN = 9000;
    public const ID_MAX = 9999;

    /** Tables comptées par status(), colonne portant l'identifiant réservé. */
    private const TABLES = [
        'acl_users'            => 'id',
        'acl_roles'            => 'id',
        'acl_roles_controllers' => 'id_role',
        'famille'              => 'id',
        'members'              => 'id',
        'emails'               => 'id',
        'famille_alerts'       => 'id',
        'groupes'              => 'id',
        'groupes_member'       => 'id',
        'trombi'               => 'id',
        'travaux'              => 'id',
        'infos'                => 'id',
        'unites'               => 'id',
        'candidatures'         => 'id',
        'validation_tokens'    => 'id',
        'sendmail'             => 'id',
        'cantine_config'       => 'id',
        'cantine_inscriptions' => 'id',
    ];

    private BaseConnection $db;
    private string $dir;

    public function __construct(?BaseConnection $db = null, ?string $dir = null)
    {
        $this->db  = $db ?? db_connect();
        $this->dir = $dir ?? ROOTPATH . 'database/sql/jeu_de_test/';
    }

    /** Vrai si le drapeau `travaux.allowTestData` est activé dans le .env. */
    public function enabled(): bool
    {
        return (bool) config('Travaux')->allowTestData;
    }

    /**
     * Purge puis recharge le jeu de test.
     *
     * @return array{statements:int, counts:array<string,int|null>}
     */
    public function reload(string $password): array
    {
        if ($password === '') {
            throw new RuntimeException('Mot de passe vide.');
        }

        $civilYear = (string) config('Travaux')->civilYear;
        if (! preg_match('/^(\d{4})-(\d{4})$/', $civilYear, $m)) {
            throw new RuntimeException("Année civile invalide dans Config\\Travaux : « {$civilYear} ».");
        }
        $previous = ((int) $m[1] - 1) . '-' . ((int) $m[2] - 1);

        $statements = self::prepare($this->read('10_jeu_de_test.sql'));

        $this->db->transBegin();

        try {
            $this->run('SET @CY = ' . $this->db->escape($civilYear));
            $this->run('SET @CY_PREV = ' . $this->db->escape($previous));
            $this->run('SET @PWD_BCRYPT = ' . $this->db->escape(password_hash($password, PASSWORD_BCRYPT)));
            $this->run('SET @PWD_MD5 = ' . $this->db->escape(md5($password)));

            foreach ($statements as $sql) {
                $this->run($sql);
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw new RuntimeException('Chargement interrompu, rien n\'a été modifié : ' . $e->getMessage(), 0, $e);
        }

        $this->db->transCommit();

        return ['statements' => count($statements) + 4, 'counts' => $this->status()];
    }

    /**
     * Retire le jeu de test (90_purge_jeu_de_test.sql).
     *
     * @return array{statements:int, counts:array<string,int|null>}
     */
    public function purge(): array
    {
        $statements = self::prepare($this->read('90_purge_jeu_de_test.sql'));

        $this->db->transBegin();

        try {
            foreach ($statements as $sql) {
                $this->run($sql);
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw new RuntimeException('Purge interrompue, rien n\'a été modifié : ' . $e->getMessage(), 0, $e);
        }

        $this->db->transCommit();

        return ['statements' => count($statements), 'counts' => $this->status()];
    }

    /**
     * Nombre de lignes présentes dans la plage réservée, par table (null si la table n'existe pas).
     *
     * @return array<string,int|null>
     */
    public function status(): array
    {
        $counts = [];
        foreach (self::TABLES as $table => $column) {
            $counts[$table] = $this->db->tableExists($table)
                ? $this->db->table($table)->where("{$column} >=", self::ID_MIN)->where("{$column} <=", self::ID_MAX)->countAllResults()
                : null;
        }

        return $counts;
    }

    /**
     * Statements exécutables d'un script du jeu de test : sans la procédure de contrôle des mots de passe
     * (remplacée par un contrôle PHP), sans la fixation de @CY (prise dans la config) ni les SELECT de récapitulatif.
     *
     * @return list<string>
     */
    public static function prepare(string $script): array
    {
        $script = preg_replace('/DROP PROCEDURE IF EXISTS `_check_pwd_vars`;.*?DROP PROCEDURE `_check_pwd_vars`;/s', '', $script);

        return array_values(array_filter(
            self::split($script),
            static fn (string $sql): bool => ! preg_match('/^(SELECT\b|SET\s+@CY(_PREV)?\s*=)/i', $sql),
        ));
    }

    /**
     * Découpe un script SQL en statements, en ignorant les ; placés dans une chaîne, un identifiant ou un commentaire.
     *
     * @return list<string>
     */
    public static function split(string $sql): array
    {
        $statements = [];
        $current    = '';
        $length     = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $c    = $sql[$i];
            $next = $sql[$i + 1] ?? '';

            if (($c === '-' && $next === '-' && in_array($sql[$i + 2] ?? "\n", [' ', "\t", "\r", "\n"], true)) || $c === '#') {
                $end = strpos($sql, "\n", $i);
                $i   = $end === false ? $length : $end;
                $current .= ' ';
            } elseif ($c === '/' && $next === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i   = $end === false ? $length : $end + 1;
                $current .= ' ';
            } elseif ($c === "'" || $c === '"' || $c === '`') {
                $current .= $c;
                for ($i++; $i < $length; $i++) {
                    $current .= $sql[$i];
                    if ($sql[$i] === '\\' && $c !== '`') {
                        $current .= $sql[++$i] ?? '';
                    } elseif ($sql[$i] === $c) {
                        if (($sql[$i + 1] ?? '') === $c) { // quote doublée
                            $current .= $sql[++$i];

                            continue;
                        }

                        break;
                    }
                }
            } elseif ($c === ';') {
                if (trim($current) !== '') {
                    $statements[] = trim($current);
                }
                $current = '';
            } else {
                $current .= $c;
            }
        }
        if (trim($current) !== '') {
            $statements[] = trim($current);
        }

        return $statements;
    }

    private function read(string $file): string
    {
        $path = $this->dir . $file;
        $sql  = is_file($path) ? file_get_contents($path) : false;
        if ($sql === false) {
            throw new RuntimeException("Fichier introuvable : {$path}");
        }

        return $sql;
    }

    private function run(string $sql): void
    {
        if ($this->db->query($sql) === false) {
            $error = $this->db->error();

            throw new RuntimeException(($error['message'] ?? 'erreur SQL') . ' — ' . mb_substr($sql, 0, 120));
        }
    }
}
