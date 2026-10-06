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

    public function testPaginationLinksHiddenWhenOnePage(): void
    {
        $this->assertSame('', pagination_links('http://x/list/page', 10, 15, 1));
        $this->assertSame('', pagination_links('http://x/list/page', 0, 15, 1));
    }

    public function testPaginationLinksMarkCurrentPage(): void
    {
        $html = pagination_links('http://x/list/page', 100, 15, 3);

        $this->assertStringContainsString('class="page-item active"><span class="page-link">3', $html);
        $this->assertStringContainsString('href="http://x/list/page/4"', $html);
        $this->assertStringContainsString('href="http://x/list/page/2"', $html);
        $this->assertStringContainsString('href="http://x/list/page/7"', $html, 'lien vers la dernière page');
    }

    public function testOpenFormCastsHiddenValues(): void
    {
        helper('form');
        $html = open_form('http://x/save', ['id' => 'f'], ['id' => 12, 'archived' => 0, 'name' => null]);

        $this->assertStringContainsString('name="id" value="12"', $html);
        $this->assertStringContainsString('name="archived" value="0"', $html);
    }
}
