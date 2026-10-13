<?php

namespace Tests\Support\Database;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Base des tests fonctionnels sur MariaDB (groupe de connexion « mariadb »).
 *
 * La base doit déjà contenir le schéma de production puis la migration CI4 :
 *   tests/_support/Database/load_mariadb.sh
 * Sans base joignable, les tests sont ignorés (et non en échec) : en local, un simple
 * `phpunit` reste utilisable. Sur GitHub Actions, MARIADB_REQUIRED=1 les rend obligatoires.
 *
 * @internal
 */
abstract class MariaDbTestCase extends CIUnitTestCase
{
    protected BaseConnection $mdb;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->mdb = Database::connect('mariadb', false);
            $this->mdb->initialize();
        } catch (\Throwable $e) {
            if (getenv('MARIADB_REQUIRED') === '1') {
                $this->fail('Base MariaDB de test injoignable : ' . $e->getMessage());
            }
            $this->markTestSkipped('Base MariaDB de test injoignable : ' . $e->getMessage());
        }

        $this->mdb->transStart();
    }

    protected function tearDown(): void
    {
        if (isset($this->mdb)) {
            // chaque test est annulé : la base reste identique d'un test à l'autre
            $this->mdb->transRollback();
            $this->mdb->transComplete();
        }
        parent::tearDown();
    }
}
