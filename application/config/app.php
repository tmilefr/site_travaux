<?php
defined('BASEPATH') || exit('No direct script access allowed');

/* APP STUFF */
$config['app_name'] = 'Site de l\'association des parents de l\'école ABCM de Mulhouse et Lutterbach';
$config['slogan'] 	= 'Outil de gestion des travaux';
$config['about'] 	= 'By NL';
$config['debug_app']= 'none'; //none,debug,profiler
$config['sidebar'] = 'on';
$config['unit_todo'] = 20;
$config['maintenance'] = false;
$config['civil_year'] = '2025-2026';

$config['crlf'] = '';

/* EMAIL */
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'dev-asso.fr';
$config['smtp_port'] = '587';
$config['smtp_user'] = 'noreply@dev-asso.fr';
$config['smtp_pass'] = '7$Y6w4e7y';
$config['smtp_crypto'] = 'tls';
$config['charset'] = 'utf-8';
$config['mailtype'] = 'html';
$config['wordwrap'] = TRUE;
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";

