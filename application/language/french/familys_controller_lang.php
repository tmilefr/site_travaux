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

// ---------------------------------------------------------------------
// IMPORT CSV ABCM — titres et sous-titres des nouvelles vues
// ---------------------------------------------------------------------
$lang['Familys_controller_import']                  = 'Import des familles (CSV ABCM)';
$lang['Familys_controller_import_subtitle']         = 'Synchronisation à partir de l\'export ABCM';
$lang['Familys_controller_import_history']         = 'Historique des imports ABCM';
$lang['Familys_controller_import_history_subtitle']= 'Suivi des imports CSV précédents';


// ---------------------------------------------------------------------
// IMPORT CSV ABCM — labels et messages
// ---------------------------------------------------------------------

// Étape 1 — formulaire d'upload
$lang['IMPORT_UPLOAD_TITLE']        = 'Import d\'un fichier CSV ABCM';
$lang['IMPORT_UPLOAD_HELP']         = 'Sélectionnez le fichier CSV exporté depuis ABCM. Le fichier sera analysé et un aperçu des changements vous sera proposé avant toute modification en base.';
$lang['IMPORT_FILE_LABEL']          = 'Fichier CSV';
$lang['IMPORT_FILE_HELP']           = 'Format attendu : CSV, séparateur point-virgule, encodage Windows-1252 (ABCM).';
$lang['IMPORT_UPLOAD_BTN']          = 'Analyser le fichier';

// Étape 1 — preview
$lang['IMPORT_PREVIEW_TITLE']       = 'Aperçu des changements';
$lang['IMPORT_PREVIEW_STATS']       = '%d ligne(s) de données analysée(s), %d famille(s) distincte(s) identifiée(s).';
$lang['IMPORT_TO_CREATE']           = 'À créer';
$lang['IMPORT_TO_UPDATE']           = 'À mettre à jour';
$lang['IMPORT_TO_REACTIVATE']       = 'À réactiver';
$lang['IMPORT_TO_MARK']             = 'À désactiver';
$lang['IMPORT_ERRORS']              = 'Erreurs';

$lang['IMPORT_TO_CREATE_TITLE']     = 'Familles nouvelles à créer';
$lang['IMPORT_TO_UPDATE_TITLE']     = 'Familles existantes à mettre à jour';
$lang['IMPORT_TO_REACTIVATE_TITLE'] = 'Familles à réactiver';
$lang['IMPORT_TO_REACTIVATE_HELP']  = 'Ces familles étaient marquées "à désactiver" mais réapparaissent dans ce CSV : leur drapeau sera levé automatiquement.';
$lang['IMPORT_TO_MARK_TITLE']       = 'Familles à marquer "à désactiver"';
$lang['IMPORT_TO_MARK_HELP']        = 'Ces familles ont un code ABCM mais ne figurent plus dans le CSV. Elles seront marquées (drapeau to_deactivate=1). Leur historique reste intact ; vous pourrez décider manuellement de leur sort depuis la liste des familles.';
$lang['IMPORT_ERRORS_TITLE']        = 'Lignes en erreur (ignorées)';

$lang['IMPORT_X_NEW_CHILDREN']      = '%d nouvel(s) enfant(s) à ajouter';
$lang['IMPORT_X_UPDATED_CHILDREN']  = '%d enfant(s) à mettre à jour';

$lang['IMPORT_CONFIRM_QUESTION']    = 'Confirmer l\'application de %d action(s) ?';
$lang['IMPORT_CONFIRM_BTN']         = 'Confirmer et appliquer';
$lang['IMPORT_CONFIRM_JS']          = 'Confirmer l\'application des changements en base ?';
$lang['IMPORT_CANCEL_BTN']          = 'Annuler';
$lang['IMPORT_NOTHING_TO_DO']       = 'Aucune action à appliquer : la base est déjà alignée avec ce CSV.';
$lang['IMPORT_BACK_BTN']            = 'Retour';

// Étape 2 — résultat
$lang['IMPORT_APPLIED_X']           = 'Import appliqué : %d créée(s), %d modifiée(s), %d marquée(s) à désactiver, %d réactivée(s).';

// Erreurs côté upload
$lang['IMPORT_NO_FILE']             = 'Aucun fichier reçu.';
$lang['IMPORT_EMPTY_FILE']          = 'Le fichier reçu est vide.';
$lang['IMPORT_BAD_EXTENSION']       = 'Extension de fichier invalide (.csv attendu).';
$lang['IMPORT_STORE_FAILED']        = 'Impossible de stocker le fichier sur le serveur.';
$lang['IMPORT_PARSE_FAILED']        = 'Impossible de parser le fichier CSV (format ou encodage incompatible).';
$lang['IMPORT_BAD_PATH']            = 'Chemin de fichier invalide.';
$lang['IMPORT_FILE_GONE']           = 'Le fichier uploadé n\'est plus disponible. Veuillez recommencer l\'import.';

// Historique
$lang['IMPORT_HISTORY_TITLE']       = 'Imports précédents';
$lang['IMPORT_HISTORY_EMPTY']       = 'Aucun import effectué pour le moment.';
$lang['IMPORT_NEW_BTN']             = 'Nouvel import';


// ---------------------------------------------------------------------
// Labels de champs (utilisés par les vues import + import_history,
// et exploités automatiquement par render_object si besoin).
// ---------------------------------------------------------------------
$lang['code_famille_abcm']          = 'Code famille ABCM';
$lang['code_membre_abcm']           = 'Code membre ABCM';
$lang['to_deactivate']              = 'À désactiver';
$lang['classe']                     = 'Classe';

$lang['filename']                   = 'Fichier';
$lang['nb_lines']                   = 'Lignes';
$lang['nb_families']                = 'Familles';
$lang['nb_created']                 = 'Créées';
$lang['nb_updated']                 = 'Modifiées';
$lang['nb_marked']                  = 'Marquées';
$lang['nb_reactivated']             = 'Réactivées';
$lang['nb_errors']                  = 'Erreurs';
$lang['created']                    = 'Date';