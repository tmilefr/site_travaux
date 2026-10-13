<?php

namespace Tests\Unit;

use App\Libraries\JeuDeTest;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Découpage et préparation des scripts SQL du jeu de test (sans base de données).
 *
 * @internal
 */
final class JeuDeTestTest extends CIUnitTestCase
{
    public function testSplitIgnoresSemicolonsInStringsAndComments(): void
    {
        $sql = "-- commentaire ; piégé\nINSERT INTO t VALUES ('a;b', \"c;d\", 'it''s', 'x\\';y'); /* ; */ DELETE FROM `t;x`; # fin ;\nSELECT 1";

        $this->assertSame([
            "INSERT INTO t VALUES ('a;b', \"c;d\", 'it''s', 'x\\';y')",
            'DELETE FROM `t;x`',
            'SELECT 1',
        ], JeuDeTest::split($sql));
    }

    public function testPrepareDropsPasswordCheckYearAndRecap(): void
    {
        $script = <<<'SQL'
            SET @CY = '2025-2026';
            SET @CY_PREV = '2024-2025';
            DROP PROCEDURE IF EXISTS `_check_pwd_vars`;
            DELIMITER $$
            CREATE PROCEDURE `_check_pwd_vars`()
            BEGIN
              IF @PWD_BCRYPT IS NULL THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'x'; END IF;
            END$$
            DELIMITER ;
            CALL `_check_pwd_vars`();
            DROP PROCEDURE `_check_pwd_vars`;
            SET @NOW = NOW();
            DELETE FROM `infos` WHERE `id` BETWEEN 9000 AND 9999;
            INSERT INTO t (a) SELECT 1;
            SELECT 'récap' AS `x`;
            SQL;

        $this->assertSame([
            'SET @NOW = NOW()',
            'DELETE FROM `infos` WHERE `id` BETWEEN 9000 AND 9999',
            'INSERT INTO t (a) SELECT 1',
        ], JeuDeTest::prepare($script));
    }

    public function testRealScriptsOnlyTouchReservedRange(): void
    {
        $dir = ROOTPATH . 'database/sql/jeu_de_test/';

        foreach (['10_jeu_de_test.sql', '90_purge_jeu_de_test.sql'] as $file) {
            $statements = JeuDeTest::prepare(file_get_contents($dir . $file));
            $this->assertNotEmpty($statements, $file);

            foreach ($statements as $sql) {
                $this->assertDoesNotMatchRegularExpression('/^(DROP|TRUNCATE|ALTER|CREATE|DELIMITER)\b/i', $sql, $file);
                if (preg_match('/^DELETE\b/i', $sql)) {
                    $this->assertMatchesRegularExpression('/BETWEEN 9000 AND 9999|LIKE \'ZZIMPORT%\'|IN \(SELECT/i', $sql, $file);
                }
            }
        }
    }

    public function testFlagIsOffByDefault(): void
    {
        $this->assertFalse((new \Config\Travaux())->allowTestData);
    }
}
