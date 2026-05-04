<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions COMMUNES — chargées globalement par MY_Controller.
//
// Les libellés spécifiques à un contrôleur ont été déplacés dans des
// fichiers dédiés : <controller_name>_lang.php (par ex. admwork_controller_lang.php),
// chargés automatiquement par MY_Controller à la fin du __construct().
//
// On ne garde ici que ce qui est utilisé transversalement :
//   - libellés ACL et menus généraux ("Parameters", "role_id", ...)
//   - icônes / classes Bootstrap utilisées par render_object
//   - boutons & helpers génériques (YES, CANCEL, AddRow, SAVED_OK, ...)
//   - libellés transverses du composant de liste (LIST_*, BULK_*, COUNT_*)
//   - libellés communs partagés par plusieurs vues (description, type,
//     ecole, login, password, name, ville, nb_enfants, capacity, id,
//     cle, value, filter)
// =====================================================================


// ---------------------------------------------------------------------
// Champs partagés par plusieurs modèles / formulaires
// (utilisés à la fois par Acl_users, Familys, etc.)
// ---------------------------------------------------------------------
$lang['role_id']        = 'R&ocirc;les';
$lang['login']          = 'Login (local)';
$lang['password']       = 'Mot de passe';
$lang['type']           = 'Type';
$lang['description']    = 'Description';
$lang['ecole']          = 'Ecole';

// Identifiants génériques (Options, Parameters, ...)
$lang['id']             = 'Identifiant';
$lang['cle']            = 'Cl&eacute;';
$lang['value']          = 'Valeur';
$lang['filter']         = 'Filtre';


$lang['civil_year'] = "Année civile";

// ---------------------------------------------------------------------
// Boutons et messages génériques
// ---------------------------------------------------------------------
$lang['Submit']                     = 'Valider';
$lang['See']                        = 'Voir';
$lang['All']                        = 'Tous';
$lang['NotFull']                    = 'Place(s) libre(s)';
$lang['Archived']                   = 'Archives';
$lang['NOT_FOUND']                  = 'N/A';
$lang['SAVED_OK']                   = '<div class="alert alert-info">ok</div>';
$lang['YES']                        = 'oui';
$lang['CANCEL']                     = 'non';
$lang['DELETE_CONFIRMATION']        = 'Etes vous sûr de vouloir effacer ?';
$lang['TXT_DELETE_CONFIRMATION']    = 'Cette action est irreversible';

// Helpers de formulaire (boutons +/- pour les listes répétables)
$lang['AddRow']     = '<span class="oi oi-plus" title="Ajouter une ligne">';
$lang['RemoveRow']  = '<span class="oi oi-circle-x" title="Supprimer une ligne">';


// ---------------------------------------------------------------------
// Icônes et classes Bootstrap (utilisées par Render_object)
// ---------------------------------------------------------------------
$lang['edit_icon']              = 'oi-pencil';
$lang['edit_class']             = 'btn-warning';
$lang['add_icon']               = 'oi oi-plus';
$lang['add_class']              = 'btn-success';
$lang['delete_icon']            = 'oi-circle-x';
$lang['delete_class']           = 'btn-danger confirmModalLink';
$lang['view_icon']              = 'oi-zoom-in';
$lang['view_class']             = 'btn-success';
$lang['sendbymail_icon']        = 'oi oi-envelope-closed';
$lang['sendbymail_class']       = 'btn-danger';
$lang['recap_icon']             = 'oi oi-grid-three-up';
$lang['recap_class']            = 'btn-info';
$lang['set_rules_icon']         = 'oi-grid-three-up';
$lang['set_rules_class']        = 'btn-info';
$lang['list_icon']              = 'oi oi-spreadsheet';
$lang['list_class']             = 'btn-info';
$lang['draftvalidation_icon']   = 'oi oi-task';
$lang['draftvalidation_class']  = 'btn-success confirmModalLink';
$lang['featured_icon']          = 'oi oi-eye';
$lang['featured_class']         = 'btn';


// ---------------------------------------------------------------------
// Composant de LISTE génériques (filtres, pagination, tri, export, bulk)
// ---------------------------------------------------------------------
$lang['LIST_ACTIVE_FILTERS']    = 'Filtres actifs';
$lang['LIST_REMOVE_FILTER']     = 'Retirer ce filtre';
$lang['LIST_RESET_ALL']         = 'Tout réinitialiser';
$lang['LIST_SEARCH']            = 'Recherche';

$lang['LIST_EMPTY']             = 'Aucun élément à afficher.';
$lang['LIST_EMPTY_FILTERED']    = 'Aucun résultat ne correspond à votre recherche ou à vos filtres.';

$lang['LIST_RESULT']            = 'résultat';
$lang['LIST_RESULTS']           = 'résultats';
$lang['LIST_PAGE_X_OF_Y']       = 'page %d sur %d';
$lang['LIST_PER_PAGE']          = 'Par page :';

/* Tri secondaire */
$lang['LIST_SORTED_BY']         = 'Tri';
$lang['LIST_RESET_SORT']        = 'Réinitialiser le tri';

/* Colonnes masquables */
$lang['LIST_COLUMNS']           = 'Colonnes';

/* Export CSV */
$lang['LIST_EXPORT_CSV']        = 'Exporter (CSV)';

/* Bulk actions */
$lang['BULK_SELECTED']          = 'élément(s) sélectionné(s)';
$lang['BULK_DELETE']            = 'Supprimer';
$lang['BULK_DELETED_X']         = '%d élément(s) supprimé(s).';
$lang['BULK_NOTHING_SELECTED']  = 'Aucun élément sélectionné.';
$lang['BULK_FORBIDDEN']         = 'Cette action n\'est pas autorisée pour ce contrôleur.';
$lang['BULK_NOT_IMPLEMENTED']   = 'Cette action groupée n\'est pas encore implémentée.';
$lang['BULK_CONFIRM_PREFIX']    = 'Voulez-vous vraiment';


// ---------------------------------------------------------------------
// Countdown (helpers de formatage de dates relatives, utilisés par
// _format_countdown() dans plusieurs vues)
// ---------------------------------------------------------------------
$lang['COUNT_IN_DAYS']      = 'dans %d j';
$lang['COUNT_IN_DAY']       = 'demain';
$lang['COUNT_AGO_DAYS']     = 'il y a %d j';
$lang['COUNT_AGO_DAY']      = 'hier';
$lang['COUNT_AGO_MONTH']    = 'il y a ~1 mois';
$lang['COUNT_AGO_MONTHS']   = 'il y a %d mois';

// Données famille (édition)
$lang['idfamille']                              = 'R&eacute;f&eacute;rence DELTA';
$lang['e_mail']                                 = 'E-mail principal (Login delta enfance)';
$lang['e_mail_comp']                            = 'E-mail compl&eacute;mentaire';
$lang['e_mail_comp_AddRow']                     = 'Ajouter un e-mail';
$lang['FAMILY']                                 = 'Famille';
$lang['FAMILY_DATA']                            = 'Donn&eacute;es de la famille';
$lang['FAMILY_MEMBER']                          = 'Membres de la famille';
$lang['DELTA_ENFANCE_DATA']                     = 'Donn&eacute;es dans DELTA ENFANCE';
$lang['MY_FAMILY_EDITION']                      = 'Sauver mes modifications';

// Vues "côté famille connectée"
$lang['YOUR_FAMILY_DATA']                       = 'Vos Donn&eacute;es compl&eacute;mentaires';
$lang['YOUR_FAMILY_MEMBER']                     = 'Membres de votre famille';
$lang['YOUR_DELTA_ENFANCE_DATA']                = 'Donn&eacute;es dans DELTA ENFANCE';

// Encarts d'aide
$lang['INFO_FAMILLE_TITLE']                     = 'Aidez nous !';
$lang['INFO_FAMILLE']                           = 'Plus nous avons d\'information sur votre famille et plus il nous sera ais&eacute; de remplir notre mission. ';
$lang['INFO_FAMILLE_FOOTER']                    = 'Par exemple, sur la fiche de cantine, tr&eacute;s souvent nous avons un nom qui ne correspond &agrave; rien dans ce site. Merci de votre compr&eacute;hension !';

// Encarts "Mon compte" / Delta Enfance
$lang['INFO_ACCOUNT_FORM_TITLE']                = 'Relation avec Delta Enfance';
$lang['INFO_ACCOUNT_FORM_BODY']                 = 'A chaque connection avec delta enfance, votre mot de passe est mis &agrave; jour dans ce site.';
$lang['INFO_ACCOUNT_FORM_FOOTER']               = 'Ainsi, si la connection avec delta enfance ne fonctionne plus, vous pouvez utiliser le login local avec le mot de passe de delta enfance';
$lang['password_change']                        = 'Cocher pour changer le mot de passe';

// Membres de la famille (table members)
$lang['members']                                = 'Membres de la famille';
$lang['members_AddRow']                         = 'Ajouter un membre &agrave; la famille';
$lang['nom_Members_model[]']                    = 'Nom';
$lang['prenom_Members_model[]']                 = 'Pr&eacute;nom';