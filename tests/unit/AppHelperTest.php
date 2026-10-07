<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AppHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('app');
    }

    public function testTrReturnsFlaggedKeyWhenMissing(): void
    {
        $this->assertSame('<i>CLE_INCONNUE_XYZ</i>', tr('CLE_INCONNUE_XYZ'));
        $this->assertSame('<i></i>', tr(null));
    }

    public function testTrFindsCommonAndMenuLabels(): void
    {
        $this->assertNotSame('', tr('YES'));
        $this->assertStringNotContainsString('<i>', tr('YES'));
    }

    public function testOpenFormCastsHiddenValues(): void
    {
        helper('form');
        $html = open_form('http://x/save', ['id' => 'f'], ['id' => 12, 'archived' => 0, 'name' => null]);

        $this->assertStringContainsString('name="id" value="12"', $html);
        $this->assertStringContainsString('name="archived" value="0"', $html);
    }
}
