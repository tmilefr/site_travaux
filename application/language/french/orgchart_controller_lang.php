<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions pour Orgchart_controller (commissions / association)
// =====================================================================

// CRUD / sous-titres
// NB : les libellés de menu (Orgchart_controller_organisation,
// Orgchart_controller_orga, Orgchart_controller_list) sont dans menu_lang.php.
$lang['Orgchart_controller_edit']           = 'Editer';
$lang['Orgchart_controller_add']            = 'Ajouter';
$lang['Orgchart_controller_subtitle']       = 'un groupe';
$lang['Orgchart_controller_header_edit']    = 'Groupe';
$lang['ADD_Orgchart_controller']            = 'Ajouter';
$lang['LIST_Orgchart_controller']           = 'Liste des commissions';
$lang['EDIT_Orgchart_controller']           = 'Edition';

// Sous-titres de la vue principale (titres dans menu_lang.php)
$lang['Orgchart_controller_orga_subtitle']  = 'commissions - 1 mission : entretenir notre association et notre école avec vous !';
$lang['Orgchart_controller_orga_title']     = 'L\'organisation de notre association';
$lang['PAGE_ORGA_INTRO']                    = 'Les commissions sont des groupes de parents s\'int&eacute;ressant &agrave; diff&eacute;rentes th&eacute;matiques. <b>TOUS</b> les parents sont les bienvenus pour s\'impliquer dans une ou plusieurs commissions, de mani&egrave;re fr&eacute;quente ou ponctuelle.Les t&acirc;ches &agrave; r&eacute;aliser sont nombreuses et vari&eacute;es et peuvent &ecirc;tre valoris&eacute;es sous forme d\'unit&eacute;s associatives. <br/> Si vous avez un peu de temps et des affinit&eacute;s pour une th&eacute;matique, n\'h&eacute;sitez pas &agrave; vous inscrire sur la page de la commission qui vous interesses.';

// Champs de la table orgchart
$lang['titleOrgchart']                      = 'Titre du groupe';
$lang['typeOrgchart']                       = 'Type';
$lang['colorOrgchart']                      = 'Couleur';
$lang['acteursOrgchart']                    = 'Membres';

// Détail commission
$lang['GROUP_INFO']                         = 'Groupe';
$lang['GROUP_MEMBER']                       = 'Membres';
$lang['NEED_YOU']                           = 'Nous recherchons';
$lang['TITLE_AGENDA']                       = 'AGENDA du Conseil d\'administration';

// Encarts d'une commission
$lang['COM_TTILE_ACTIONS']                  = 'Action de la commission';
$lang['COM_TTILE_ROLE']                     = 'Rôle du responsable';
$lang['COM_TTILE_NEEDS']                    = 'Moyens et besoins';

// Candidatures depuis la page commission (le module CRUD est dans candidatures_controller_lang.php)
$lang['CANDIDATE']                          = 'Participer';
$lang['EDIT_CANDIDATE']                     = 'Modifier ma candidature';
$lang['CANCEL_CANDIDATE']                   = 'Annuler ma candidature';
$lang['CANDIDATE_SENDED']                   = 'Candidature ajoutée / mise à jour';
$lang['CANDIDATE_COM']                      = 'Candidature ';

// Helpers de membres (table trombi)
$lang['email_Trombi_model[]']               = 'Email de contact';
$lang['help_nom_Trombi_model[]']            = 'famille';
