<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions pour les MENUS — chargées sur toutes les pages.
//
// Render_menu (application/libraries/Render_menu.php) lit application/
// models/json/Menus.json et appelle Lang($element->name) pour chaque
// entrée. Comme le menu est rendu sur toutes les pages (via template/
// head.php), il a besoin des libellés de TOUS les contrôleurs, pas
// seulement de celui qui sert la requête courante.
//
// Ce fichier centralise donc toutes ces clés. Il est chargé sans
// condition par MY_Controller, juste après traduction_lang.php.
//
// /!\ Les clés ici doivent rester EN PHASE avec Menus.json. Si vous
// ajoutez une entrée dans Menus.json, ajoutez aussi la clé ici.
// =====================================================================


// ---------------------------------------------------------------------
// sysmenu
// ---------------------------------------------------------------------
$lang['Acl_users_controller']           = 'Utilisateurs';
$lang['Acl_roles_controller']           = 'R&ocirc;les';
$lang['Acl_controllers_controller']     = 'ACL';
$lang['Sendmail_controller']            = 'Envois d\' e-mails';


// ---------------------------------------------------------------------
// optionmenu
// ---------------------------------------------------------------------
$lang['Option_controller']              = 'Options';
$lang['Templates_controller']           = 'Modèle de texte';
$lang['Parameters']                     = 'Param&egrave;tres';


// ---------------------------------------------------------------------
// mainmenu — Notre association
// ---------------------------------------------------------------------
$lang['Orgchart_controller_organisation']   = 'Notre association';
$lang['Orgchart_controller_orga']           = ' Les commissions';
$lang['Candidatures_controller_list']       = 'Gestion des candidatures';
$lang['Orgchart_controller_list']           = 'Gestion des commissions';
$lang['GroupesMembers_controller_list']     = 'Gestion des membres de commissions';
$lang['Files_controller_list']              = 'Gestion des fichiers';
$lang['Event_controller_list']              = 'Gestion des évènements';


// ---------------------------------------------------------------------
// mainmenu — Cantine (avec variantes _fam / _sys via $element->opt)
// ---------------------------------------------------------------------
$lang['Cantine_controller_register']        = 'Garde du midi';
$lang['Cantine_controller_register_fam']    = 'Garde du midi';   // libellé menu famille
$lang['Cantine_controller_register_sys']    = 'Garde du midi';   // libellé menu admin
$lang['Cantine_controller_config']          = 'Paramétrage garde midi';


// ---------------------------------------------------------------------
// mainmenu — Familles & unités (avec variantes _fam / _sys)
// ---------------------------------------------------------------------
$lang['Familys_controller_histo']           = 'Mon compte';      // libellé brut, par sécurité
$lang['Familys_controller_histo_fam']       = 'Mes unités';
$lang['Familys_controller_histo_sys']       = 'Les familles';
$lang['Familys_controller_list']            = 'Gestion des familles';
$lang['Units_controller_valid']             = 'Validation des unit&eacute;s sur sessions';
$lang['Units_controller_list']              = 'Unit&eacute;s suppl&eacute;mentaires';
$lang['Familys_controller_skills']          = 'Comp&eacute;tences des familles';
$lang['Familys_controller_stats']           = 'Synthèse unités';


// ---------------------------------------------------------------------
// mainmenu — Travaux / sessions (avec variantes _fam / _sys)
// ---------------------------------------------------------------------
$lang['Admwork_controller_register']        = 'Travaux';
$lang['Admwork_controller_register_fam']    = 'Travaux disponibles';
$lang['Admwork_controller_register_sys']    = 'Les travaux';
$lang['Admwork_controller_list']            = 'Gestion des travaux';
$lang['Admwork_controller_worker']          = 'Statistiques participants';
$lang['Admwork_controller_my_sessions']     = 'Mes sessions à animer';


// ---------------------------------------------------------------------
// Barre utilisateur (rendue dans template/head.php)
// ---------------------------------------------------------------------
$lang['Myaccount']                  = 'Mon Compte';
$lang['Login_out']                  = 'D&eacute;connection';
$lang['Maintenance_in_progress']    = 'Maintenance en cours';
