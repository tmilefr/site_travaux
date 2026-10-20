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
$config['civil_year'] = '2025-2026'; // année par défaut (sélecteur du menu)
$config['civil_years'] = [ // années proposées dans le sélecteur du menu
	'2022-2023' => '2022-2023',
	'2023-2024' => '2023-2024',
	'2024-2025' => '2024-2025',
	'2025-2026' => '2025-2026',
	'2026-2027' => '2026-2027',
	'2027-2028' => '2027-2028',
];

$config['role_famille'] = 2;


