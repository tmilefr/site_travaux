<?php

namespace Tests\Database;

use Tests\Support\Database\MariaDbTestCase;

/**
 * Vérifie que « schéma de production + mig_prod_vers_ci4.sql » donne la base attendue par le code CI4.
 *
 * @internal
 */
final class MigrationSchemaTest extends MariaDbTestCase
{
    /** @return list<string> */
    private function columns(string $table): array
    {
        return array_map('strtolower', $this->mdb->getFieldNames($table));
    }

    public function testMigrationCreatesNewTables(): void
    {
        foreach (['validation_tokens', 'famille_alerts', 'parameters', 'family_import', 'cantine_config', 'cantine_inscriptions', 'cantine_generation'] as $table) {
            $this->assertTrue($this->mdb->tableExists($table), "Table {$table} absente après migration");
        }
    }

    public function testMigrationAddsColumns(): void
    {
        $expected = [
            'famille'   => ['code_famille_abcm', 'to_deactivate', 'alert_types', 'type_session'],
            'members'   => ['code_membre_abcm', 'classe'],
            'travaux'   => ['ref_mail_sent_at', 'alert_sent_at'],
            'infos'     => ['civil_year'],
        ];
        foreach ($expected as $table => $cols) {
            foreach ($cols as $col) {
                $this->assertContains($col, $this->columns($table), "{$table}.{$col} manquante");
            }
        }
    }

    public function testBcryptHashFitsInFamilyPassword(): void
    {
        $field = null;
        foreach ($this->mdb->getFieldData('famille') as $f) {
            if ($f->name === 'password') {
                $field = $f;
            }
        }
        $this->assertNotNull($field);
        $this->assertGreaterThanOrEqual(60, (int) $field->max_length);
    }

    public function testSessionsTableMatchesCi4(): void
    {
        $this->assertSame(['id', 'ip_address', 'timestamp', 'data'], $this->columns('ci_sessions'));
    }

    public function testReferenceDataIsLoaded(): void
    {
        $this->assertGreaterThan(0, $this->mdb->table('acl_roles')->countAllResults());
        $this->assertGreaterThan(0, $this->mdb->table('acl_actions')->countAllResults());
        $this->assertGreaterThan(0, $this->mdb->table('options')->where('filter', 'type')->countAllResults());
    }

    public function testNoPersonalDataInTestDatabase(): void
    {
        foreach (['famille', 'members', 'groupes_member', 'candidatures', 'infos', 'acl_users', 'login'] as $table) {
            $this->assertSame(0, $this->mdb->table($table)->countAllResults(), "{$table} doit être vide dans la base de test");
        }
    }

    public function testRollbackKeepsDatabaseClean(): void
    {
        $this->mdb->table('options')->insert(['cle' => 'tmp_test', 'value' => 'x', 'filter' => 'tmp', 'created' => '2026-01-01', 'updated' => '2026-01-01']);
        $this->assertSame(1, $this->mdb->table('options')->where('cle', 'tmp_test')->countAllResults());
    }

    public function testPreviousRollbackLeftNoTrace(): void
    {
        $this->assertSame(0, $this->mdb->table('options')->where('cle', 'tmp_test')->countAllResults());
    }
}
