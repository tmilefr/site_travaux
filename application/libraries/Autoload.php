<?php
/**
 * Simple autoloader, so we don't need Composer just for this.
 */
class Autoloader
{
    public static function register()
    {
        spl_autoload_register(function ($class) {
            $file = APPPATH.'libraries/'.$class.'.php';

            if (file_exists($file)) {
                require_once $file;
                //echo ($file.'  found');
                return true;
            } else {
                $file = APPPATH.'libraries/elements/'.$class.'.php';
                if (file_exists($file)) {
                    require_once $file;
                    //echo ($file.'  found');
                    return true;
                }
            }
            echo ($file.' not found');
            return false;
        });
    }
}

Autoloader::register();