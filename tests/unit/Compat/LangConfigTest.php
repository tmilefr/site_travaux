<?php

namespace Tests\Unit\Compat;

use App\Libraries\Compat\Config;
use App\Libraries\Compat\Lang;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LangConfigTest extends CIUnitTestCase
{
    public function testLangLoadsLegacyFilesAndFlagsMissingKeys(): void
    {
        $lang = new Lang();
        $lang->load('menu');

        $this->assertSame('<i>CLE_INCONNUE_XYZ</i>', $lang->line('CLE_INCONNUE_XYZ'));
        $this->assertNotSame('', $lang->line('Home'));
        $this->assertTrue($lang->load('menu'), 'second chargement idempotent');
    }

    public function testConfigLoadsLegacyAppFile(): void
    {
        $config = new Config();
        $this->assertTrue($config->load('app'));
        $this->assertSame(2, $config->item('role_famille'));
        $this->assertSame('french', $config->item('language'));
        $this->assertNull($config->item('cle_absente'));
        $this->assertFalse($config->load('fichier_inexistant', false, true));
    }
}
