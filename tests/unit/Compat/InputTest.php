<?php

namespace Tests\Unit\Compat;

use App\Libraries\Compat\Input;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InputTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        $_POST = $_GET = [];
        parent::tearDown();
    }

    public function testPostReadsSuperglobalLiveLikeCi3(): void
    {
        $input = new Input();
        $_POST = ['a' => '1'];
        $this->assertSame('1', $input->post('a'));
        $this->assertNull($input->post('absent'));

        // le code metier modifie parfois $_POST en cours de requete
        $_POST['b'] = '2';
        $this->assertSame('2', $input->post('b'));
        $this->assertSame(['a' => '1', 'b' => '2'], $input->post());
    }

    public function testNestedIndexSyntax(): void
    {
        $input = new Input();
        $_POST = ['rows' => ['x' => ['y' => 'z'], 3 => 'trois']];

        $this->assertSame('z', $input->post('rows[x][y]'));
        $this->assertSame('trois', $input->post('rows[3]'));
        $this->assertNull($input->post('rows[nope]'));
    }

    public function testGet(): void
    {
        $input = new Input();
        $_GET = ['ecole' => 'MUL'];
        $this->assertSame('MUL', $input->get('ecole'));
    }
}
