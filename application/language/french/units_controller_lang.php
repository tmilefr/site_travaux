<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions pour Units_controller (unités associatives)
// =====================================================================

// CRUD / sous-titres
// NB : les libellés du menu (Units_controller_list, Units_controller_valid)
// sont dans menu_lang.php.
$lang['Units_controller_add']               = 'Ajout d\'unit&eacute;s suppl&eacute;mentaire';
$lang['Units_controller_subtitle']          = '';
$lang['LIST_Units_controller']              = 'Liste';
$lang['ADD_Units_controller']               = 'Ajout';

// Validation des unités (sous-titres et variantes hors-menu)
$lang['Units_controller_valid_subtitle']    = 'veuillez les unités que vous voulez valider';
$lang['Units_controller_valids']            = 'Liste des sessions à valider';
$lang['Units_controller_valids_subtitle']   = 'Veuillez confirmer les éléments ci-après';
$lang['VALIDS_EDITION']                     = 'Valider';
$lang['EDIT_VALID_UNIT']                    = 'Validation des unités';

// Champs de la table unites
$lang['unites_id_famille']                  = 'Famille';
$lang['unites_valides']                     = 'Nombre d\'unit&eacute;';
$lang['unites_date']                        = 'Date';
$lang['unites_heure_debut']                 = 'Heure de d&eacute;but';
$lang['unites_heure_fin']                   = 'Heure de fin';
$lang['unites_desc']                        = 'Description';
$lang['unites_comm']                        = 'Commentaire';
$lang['nb_unites_valides']                  = 'Unit&eacute;';
$lang['type_session']                       = 'Type de session';

// Explications de session (utilisées sur les vues d'inscription / validation)
$lang['TITRE_TYPE_SESSION']                 = 'Session de type ';
$lang['INFO_TYPE_SESSION']                  = 'Quelques explications sur la session';
$lang['INFO_TYPE_SESSION1']                 = 'Le nombre d\'unit&eacute; associative correspond au temps pass&eacute; sur la session';
$lang['INFO_TYPE_SESSION2']                 = 'Le nombre d\'unit&eacute; associative correspond &agrave; un temps d&eacute;fini pour l\'action';
$lang['INFO_WHO_MANAGE']                    = 'Pilote de la session';

// Erreur métier (inscriptions)
$lang['TOO_MANY_PEOPLE']                    = '<div class="alert alert-danger" role="alert">Trop de personne inscrite!</div>';

// Sous-titre actualisé
$lang['Units_controller_valid_subtitle']    = 'Cochez les unités à valider, puis cliquez sur "Valider".';

// État vide (rien à valider)
$lang['UV_EMPTY_TITLE']                     = 'Aucune unité en attente de validation';
$lang['UV_EMPTY_HELP']                      = 'Toutes les sessions ont été traitées. Revenez après la prochaine session.';

// Barre de filtres
$lang['UV_SEARCH_PLACEHOLDER']              = 'Rechercher (titre, famille, référent...)';
$lang['UV_ALL_DATES']                       = 'Toutes les dates';
$lang['UV_ALL_FAMILYS']                     = 'Toutes les familles';
$lang['UV_ALL_TYPES']                       = 'Tous les types';
$lang['UV_RESET']                           = 'Réinitialiser';

// Compteurs
$lang['UV_LBL_SESSIONS']                    = 'sessions';
$lang['UV_LBL_UNITS']                       = 'unités à valider';
$lang['UV_LBL_SELECTED']                    = 'sélectionnées';
$lang['UV_LBL_SELECTED_LONG']               = 'unité(s) sélectionnée(s)';
$lang['UV_LBL_REGISTERED']                  = 'inscrits';

// Carte de session
$lang['UV_OPEN_SESSION']                    = 'Ouvrir';
$lang['UV_CHECK_ALL']                       = 'Tout cocher';

// Type de session (libellés courts utilisés dans la vue)
$lang['type_session_horaire']               = 'Horaire';
$lang['type_session_action']                = 'Action';

// Aucun résultat (filtres)
$lang['UV_NO_RESULT']                       = 'Aucune session ne correspond aux filtres en cours.';