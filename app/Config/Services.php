<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /**
     * Moteur de vues : expose les composants du controleur courant
     * ($this->render_object, $this->lang ...) dans les gabarits, comme en CI3.
     */
    public static function renderer(?string $viewPath = null, ?\Config\View $config = null, bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('renderer', $viewPath, $config);
        }

        $viewPath = in_array($viewPath, [null, '', '0'], true) ? (new Paths())->viewDirectory : $viewPath;
        $config ??= config(\Config\View::class);

        return new \App\Libraries\Compat\CompatView(
            $config,
            $viewPath,
            static::locator(),
            CI_DEBUG,
            static::logger()
        );
    }

    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
}
