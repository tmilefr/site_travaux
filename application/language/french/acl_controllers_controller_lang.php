<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions pour Acl_controllers_controller (gestion des droits)
// NB : la clé Acl_controllers_controller (titre dans le sysmenu) est dans menu_lang.php.
// =====================================================================

$lang['Acl_controllers_controller_list']        = 'Gestion des droits';
$lang['Acl_controllers_controller_edit']        = 'Edition utilisateur Syst&egrave;me';
$lang['Acl_controllers_controller_subtitle']    = 'Les utilisateurs sp&eacute;ciaux du site';
$lang['ADD_Acl_controllers_controller']         = 'Ajouter';
$lang['EDIT_Acl_controllers_controller']        = 'Valider';
$lang['LIST_Acl_controllers_controller']        = 'Liste';

// =====================================================================
// Bulk add action (ajout d'une action sur tous les contrôleurs)
// =====================================================================
$lang['Acl_controllers_controller_bulk_add_action']    = 'Ajouter une action sur tous les contr&ocirc;leurs';
$lang['Acl_controllers_controller_bulk_subtitle']      = 'Ajoute l\'action saisie &agrave; chaque contr&ocirc;leur o&ugrave; elle n\'existe pas encore.';
$lang['Acl_controllers_controller_bulk_action_name']   = 'Nom de l\'action';
$lang['Acl_controllers_controller_bulk_action_help']   = 'Lettres, chiffres et underscore. Ex : JsonData, export, bulk.';
$lang['Acl_controllers_controller_bulk_preview']       = 'Pr&eacute;visualiser';
$lang['Acl_controllers_controller_bulk_to_add_x']      = '&Agrave; ajouter (%d)';
$lang['Acl_controllers_controller_bulk_existing_x']    = 'D&eacute;j&agrave; pr&eacute;sent (%d)';
$lang['Acl_controllers_controller_bulk_nothing_to_add']= 'Aucun contr&ocirc;leur &agrave; mettre &agrave; jour.';
$lang['Acl_controllers_controller_bulk_confirm_x']     = 'Confirmer l\'ajout sur %d contr&ocirc;leur(s)';
$lang['Acl_controllers_controller_bulk_added_x']       = '%d action(s) « %s » ajout&eacute;e(s).';
$lang['Acl_controllers_controller_bulk_invalid']       = 'Nom d\'action invalide.';

// =====================================================================
// Tableau de bord ACL (KPIs + warnings)
// =====================================================================
$lang['ACL_KPI_CONTROLLERS']  = 'Contrôleurs';
$lang['ACL_KPI_ACTIONS']      = 'Actions';
$lang['ACL_KPI_RULES']        = 'Règles ACL';
$lang['ACL_KPI_ROLES_USING']  = 'Rôles actifs';
$lang['ACL_KPI_CTRL_SHORT']   = 'ctrl';
$lang['ACL_WARN_NO_ACTION']   = 'Contrôleur(s) sans aucune action déclarée :';
$lang['ACL_WARN_NO_ROLE']     = 'Contrôleur(s) non utilisé(s) par un rôle :';
$lang['ACL_WARN_MISSING_FILE']= 'Contrôleur(s) déclaré(s) en base mais sans fichier PHP correspondant :';
$lang['ACL_WARN_DETAILS']     = 'Voir';

// =====================================================================
// Carte contrôleur : groupes d'actions + statistiques
// =====================================================================
$lang['ACL_GROUP_STANDARD']   = 'Standard';
$lang['ACL_GROUP_BUSINESS']   = 'Métier';
$lang['ACL_STAT_USED']        = 'Actions utilisées par au moins un rôle';
$lang['ACL_STAT_ORPHAN']      = 'Actions non référencées par un rôle';
$lang['ACL_STAT_MISSING_FILE']= 'fichier manquant';
$lang['NO_ACTION_DEFINED']    = 'Aucune action définie pour ce contrôleur.';

// =====================================================================
// Toolbar de filtrage de la liste
// =====================================================================
$lang['ACL_TOOLBAR_SEARCH_PH']  = 'Rechercher un contrôleur ou une action...';
$lang['ACL_TOOLBAR_RESET']      = 'Réinitialiser';
$lang['ACL_FILTER_NO_ACTION']   = 'Sans action';
$lang['ACL_FILTER_ORPHANS']     = 'Avec orphelines';
$lang['ACL_FILTER_MISSING_FILE']= 'Fichier manquant';

// =====================================================================
// Formulaire d'édition
// =====================================================================
$lang['controller_help']    = 'Nom exact de la classe PHP (ex. Acl_users_controller).';
$lang['actions_help']       = 'Une action = une méthode publique du contrôleur (ex. list, add, edit, delete).';
$lang['STD_ACTIONS']        = 'Actions standard :';
$lang['FORM_MAIN_INFO']     = 'Informations';
$lang['FORM_META']          = 'Informations système';
$lang['FORM_ERRORS_TITLE']  = 'Veuillez corriger les erreurs ci-dessous :';
$lang['CANCEL']             = 'Annuler';
$lang['GO_TO_ROLES']        = 'Gérer les rôles';

//SCAN
$lang['Acl_controllers_controller_scan']   = 'Synchroniser depuis le code';
$lang['Acl_scan_label']                    = 'Scan ACL';
$lang['Acl_scan_title']                    = 'Synchronisation depuis le code';
$lang['Acl_scan_files']                    = 'fichier(s) analysé(s)';
$lang['Acl_scan_new_ctrls']                = 'nouveau(x) contrôleur(s)';
$lang['Acl_scan_new_actions']              = 'nouvelle(s) action(s)';
$lang['Acl_scan_obsolete']                 = 'élément(s) obsolète(s)';
$lang['Acl_scan_in_sync']                  = 'La base ACL est synchronisée avec le code.';
$lang['Acl_scan_new_ctrls_title']          = 'Contrôleurs absents de la base';
$lang['Acl_scan_new_actions_title']        = 'Actions manquantes sur des contrôleurs existants';
$lang['Acl_scan_obsolete_title']           = 'Éléments en base sans équivalent dans le code';
$lang['Acl_scan_obsolete_note']            = '(suppression manuelle pour ne casser aucune règle ACL)';
$lang['Acl_scan_no_php_file']              = 'aucun fichier PHP correspondant';
$lang['Acl_scan_obsolete_methods']         = 'méthodes absentes du code :';
$lang['Acl_scan_select_all']               = 'Tout cocher / décocher';
$lang['Acl_scan_actions_short']            = 'action(s)';
$lang['Acl_scan_apply']                    = 'Appliquer la sélection';
$lang['Acl_scan_done']                     = 'Scan ACL terminé : %d contrôleur(s) ajouté(s), %d action(s) ajoutée(s).';

$lang['ACL_LBL_STANDARD']   = 'Standard';
$lang['ACL_LBL_BUSINESS']   = 'Métier';
$lang['ACL_LBL_NO_CUSTOM']  = 'CRUD uniquement.';
$lang['NO_ACTION_DEFINED']  = 'Aucune action définie.';