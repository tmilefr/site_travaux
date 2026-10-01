<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Garde-fous de structure : routes, langues, modèles.
 *
 * @internal
 */
final class StructureTest extends CIUnitTestCase
{
    public function testEveryControllerIsRouted(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');

        foreach (glob(APPPATH . 'Controllers/*.php') as $file) {
            $name = basename($file, '.php');
            if (in_array($name, ['BaseController', 'CrudController'], true)) {
                continue;
            }
            $this->assertStringContainsString("'{$name}'", $routes, "Le contrôleur {$name} n'est pas déclaré dans app/Config/Routes.php");
        }
    }

    public function testLanguageFilesReturnArrays(): void
    {
        $files = glob(APPPATH . 'Language/fr/*.php');
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $entries = include $file;
            $this->assertIsArray($entries, basename($file));
            foreach (array_keys($entries) as $key) {
                $this->assertStringNotContainsString('.', (string) $key, basename($file) . " : la clé « {$key} » contient un point");
            }
        }
    }

    public function testNoLegacyCi3ApiLeft(): void
    {
        $forbidden = ['get_instance()', '$this->load->', '$this->input->', 'BASEPATH', 'ci_redirect(', 'ci_lang('];

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(APPPATH, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if ($file->getExtension() !== 'php' || str_contains($file->getPathname(), '/Views/errors/')) {
                continue;
            }
            $code = file_get_contents($file->getPathname());
            foreach ($forbidden as $needle) {
                $this->assertStringNotContainsString($needle, $code, $file->getFilename() . " utilise l'API CodeIgniter 3 « {$needle} »");
            }
        }
    }

    public function testModelSchemasAreValidJson(): void
    {
        foreach (glob(APPPATH . 'Models/json/*.json') as $file) {
            $this->assertNotNull(json_decode(file_get_contents($file)), basename($file) . ' : JSON invalide');
        }
    }
}
