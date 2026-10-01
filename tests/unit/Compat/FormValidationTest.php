<?php

namespace Tests\Unit\Compat;

use App\Libraries\Compat\Form_validation;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FormValidationTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_POST = [];
    }

    protected function tearDown(): void
    {
        $_POST = [];
        parent::tearDown();
    }

    private function validator(): Form_validation
    {
        $fv = new Form_validation();
        $fv->_SetRules([
            ['field' => 'nom', 'label' => 'Nom', 'rules' => 'trim|required|min_length[2]|max_length[5]'],
            ['field' => 'age', 'label' => 'Age', 'rules' => 'trim|numeric'],
            ['field' => 'mail', 'label' => 'Mail', 'rules' => 'valid_email|required'],
        ], 'Demo_model');

        return $fv;
    }

    public function testRunWithoutDataReturnsFalseLikeCi3(): void
    {
        $fv = $this->validator();
        $this->assertFalse($fv->run('Demo_model'));
        $this->assertSame('', $fv->error('nom'));
    }

    public function testValidPostPasses(): void
    {
        $_POST = ['nom' => '  Ali ', 'age' => '12', 'mail' => 'a@b.fr'];
        $fv = $this->validator();

        $this->assertTrue($fv->run('Demo_model'));
        $this->assertSame('Ali', $_POST['nom'], 'trim doit etre reporte dans $_POST comme en CI3');
    }

    public function testRequiredAndFormatErrors(): void
    {
        $_POST = ['nom' => '', 'age' => 'abc', 'mail' => 'pas-un-mail'];
        $fv = $this->validator();

        $this->assertFalse($fv->run('Demo_model'));
        $this->assertStringContainsString('Nom', $fv->error('nom', '<b>', '</b>'));
        $this->assertStringStartsWith('<b>', $fv->error('nom', '<b>', '</b>'));
        $this->assertNotSame('', $fv->error('age'));
        $this->assertNotSame('', $fv->error('mail'));
        $this->assertCount(3, $fv->error_array());
    }

    public function testLengthRules(): void
    {
        $_POST = ['nom' => 'A', 'age' => '1', 'mail' => 'a@b.fr'];
        $fv = $this->validator();
        $this->assertFalse($fv->run('Demo_model'));
        $this->assertArrayHasKey('nom', $fv->error_array());

        $_POST = ['nom' => 'Trop long', 'age' => '1', 'mail' => 'a@b.fr'];
        $fv = $this->validator();
        $this->assertFalse($fv->run('Demo_model'));
        $this->assertArrayHasKey('nom', $fv->error_array());
    }

    public function testSetDataAndOptionalEmptyField(): void
    {
        $fv = new Form_validation();
        $fv->_SetRules([['field' => 'age', 'label' => 'Age', 'rules' => 'trim|numeric']], 'G');
        $fv->set_data(['age' => '']);

        $this->assertTrue($fv->run('G'), 'un champ vide non requis ne declenche pas de regle');
    }
}
