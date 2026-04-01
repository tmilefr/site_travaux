
<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Class dealing with errors as exceptions
 */
class MY_Exceptions extends CI_Exceptions
{

    /**
     * Force exception throwing on erros
     */
    public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
    {
        if ( get_instance() && get_instance()->_get('_api') == TRUE){
            set_status_header($status_code);
            //echo debug($obj);
            $message = implode(" / ", (!is_array($message)) ? array($message) : $message);

            throw new CiError($message);
        } else {
            return parent::show_error($heading, $message, $template, $status_code);
        }
    }

}

/**
 * Captured error from Code Igniter
 */
class CiError extends Exception
{

}