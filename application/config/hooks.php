<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
/*$hook['pre_system'] = array(
								'class'    => 'Errors_hook',
								'function' => 'error_catcher',
								'filename' => 'Errors_hook.php',
								'filepath' => 'hooks'
							);*/
$hook['post_controller_constructor'][] = [
    'class'    => 'Loginchecker',
    'function' => 'loginCheck',
    'filename' => 'Loginchecker.php',
    'filepath' => 'hooks',
    'params'   => []
];