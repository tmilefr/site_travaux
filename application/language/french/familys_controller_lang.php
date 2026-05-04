<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions pour Familys_controller (gestion des familles)
// =====================================================================

// CRUD / sous-titres
// NB : les libellés visibles dans le menu (Familys_controller_list,
// Familys_controller_skills, Familys_controller_stats, et les variantes
// Familys_controller_histo_fam/sys) sont dans menu_lang.php.
$lang['Familys_controller_edit']                = 'Editer';
$lang['Familys_controller_add']                 = 'Ajouter';
$lang['Familys_controller_subtitle']            = 'une famille';
$lang['ADD_Familys_controller']                 = 'Ajouter';
$lang['LIST_Familys_controller']                = 'Liste des familles';
$lang['EDIT_Familys_controller']                = 'Edition';

// Vues fam / sys (sous-titres et variantes hors-menu)
$lang['Familys_controller_histofam']            = 'Mon  Compte';
$lang['Familys_controller_histofam_subtitle']   = 'Mes unit&eacute;s';
$lang['Familys_controller_histo_subtitle']      = 'Vos travaux &agrave; venir ou valid&eacute;s';
$lang['Familys_controller_histosys']            = 'Veuillez selectionner une famille';
$lang['Familys_controller_histosys_subtitle']   = 'Unit&eacute;s associatives';

// Compétences (sous-titre — le titre est dans menu_lang.php)
$lang['Familys_controller_skills_subtitle']     = 'cliquez sur une comp&eacute;tences pour filtrer';

// Statistiques familles (sous-titres — le titre principal Familys_controller_stats est dans menu_lang.php)
$lang['Familys_controller_statssys']            = 'Statistisques familles';
$lang['Familys_controller_statssys_subtitle']   = 'Etats des unités associatives ';
$lang['_title_family']                          = 'Famille';
$lang['_title_raf']                             = 'Reste à faire';
$lang['_title_tovalid']                         = 'A valider';
$lang['_title_addition']                        = 'Unités supplémentaires';
$lang['_title_valid']                           = 'Validés';
$lang['_title_ecole']                           = 'Ecole';

// Sous-pages d'une famille
$lang['Familys_controller_units']               = 'Gestion des Unit&eacute;s';
$lang['Familys_controller_check']               = 'Gestion des Ch&egrave;ques';




// Compteurs d'unités
$lang['UNIT_TITLE']                             = 'Etat des compteurs';
$lang['UNIT_TODO']                              = 'Unités à faire';
$lang['UNIT_RAF']                               = 'Unités restantes à faire';
$lang['UNIT_TOVALID']                           = 'Unités en attente de validation';
$lang['INFO_UNITS_fam']                         = 'Les unités associatives sont à faire entre le 1er juin et le 31 mai. N\'hesitez pas à nous contacter en cas de problème pour les réaliser.';
$lang['INFO_UNITS_sys']                         = 'Les unités associatives sont à faire entre le 1er juin et le 31 mai.';

// Blocs récapitulatifs (vue famille / sys)
$lang['COMING_sys']                             = 'Sessions &agrave venir / unit&eacute;s en attente de validation';
$lang['VALID_sys']                              = 'Unit&eacute;s valid&eacute;es sur sessions';
$lang['ADDED_sys']                              = 'Unit&eacute;s compl&eacute;mentaires (hors session)';
$lang['COMING_fam']                             = 'Sessions &agrave venir / unit&eacute;s en attente de validation';
$lang['VALID_fam']                              = 'Unit&eacute;s valid&eacute;es sur sessions';
$lang['ADDED_fam']                              = 'Unit&eacute;s compl&eacute;mentaires (hors session)';

// Champs de la famille (édition)
$lang['name']                                   = 'Nom affich&eacute;';
$lang['ville']                                  = 'Ville';
$lang['nb_enfants']                             = 'Nombre d\'enfant';
$lang['capacity']                               = 'Comp&eacute;tences';
$lang['civil_year']                             = 'Année civile';
