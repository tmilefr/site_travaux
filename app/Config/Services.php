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
    public static function bootstrapTools(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('bootstrapTools');
        }

        return new \App\Libraries\Bootstrap_tools();
    }

    public static function cronJobs(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('cronJobs');
        }

        return new \App\Libraries\CronJobs();
    }

    public static function renderObject(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('renderObject');
        }

        return new \App\Libraries\Render_object(static::bootstrapTools());
    }

    /** Contrôle d'accès (rôles / actions) de la requête courante. */
    public static function acl(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('acl');
        }

        return new \App\Libraries\Acl();
    }

    /** Authentification (web, API JWT, SSO Delta). */
    public static function auth(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('auth');
        }

        return new \App\Libraries\Auth();
    }

    public static function passwordAuthenticator(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('passwordAuthenticator');
        }

        return new \App\Libraries\PasswordAuthenticator();
    }

    public static function refNotifier(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('refNotifier');
        }

        return new \App\Libraries\RefNotifier();
    }

    public static function inscriptions(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('inscriptions');
        }

        return new \App\Libraries\Inscriptions();
    }

    public static function libpdf(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('libpdf');
        }

        return new \App\Libraries\Libpdf();
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
