<?php
defined('BASEPATH') || exit('No direct script access allowed');

// =====================================================================
// Traductions pour Admwork_controller (gestion des sessions / travaux)
// =====================================================================

// Titres / CRUD
// NB : les libellés visibles dans le menu (Admwork_controller_list,
// Admwork_controller_register_fam/sys, Admwork_controller_my_sessions,
// Admwork_controller_worker) sont dans menu_lang.php.
$lang['GESTION_Admwork_controller']             = 'Gestion des sessions';
$lang['Admwork_controller_edit']                = 'Editer';
$lang['Admwork_controller_add']                 = 'Ajouter';
$lang['Admwork_controller_subtitle']            = 'une session';
$lang['ADD_Admwork_controller']                 = 'Ajouter';
$lang['LIST_Admwork_controller']                = 'Liste des sessions';
$lang['EDIT_Admwork_controller']                = 'Edition d\'une session';
$lang['DRAFTVALIDATION_Admwork_controller']     = 'Publier brouillon';

// Vues parent / admin
$lang['work_planned']                           = 'Travaux disponibles';
$lang['work_planned_subtitle']                  = 'voici les prochains travaux ';
$lang['ADM_WORK']                               = 'Administration';
$lang['DO_PDF']                                 = 'Imprimer un PDF';

// Inscription / désinscription
$lang['REGISTER_WORK']                          = 'S\'inscrire';
$lang['SEE_YOUR_REGISTRED_WORK']                = 'Votre inscription';
$lang['MOD_REGISTER_WORK']                      = 'D&eacute;tails de votre inscription';
$lang['SEE_WORK']                               = 'D&eacute;tails';
$lang['REGISTER_CHANGE']                        = 'Valider';
$lang['REGISTER_CANCEL']                        = 'Annuler mon inscription';
$lang['REGISTER_WORK_CLOSED']                   = 'Inscription fermée';

// Pilote / référent
$lang['PILOT_IS']                               = 'Le pilote est : ';
$lang['PILOT_CONTACT']                          = 'Pour le contacter : ';

// Compteurs / unités
$lang['INFO_NB_UNIT']                           = 'Nombre d\'unit&eacute;(s) pour la session : ';
$lang['INFO_UNIT']                              = ' unit&eacute;(s)';
$lang['nb_units']                               = 'Nombre d\'unit&eacute;';

// Champs de la table travaux
$lang['date_travaux']                           = 'Date';
$lang['heure_deb_trav']                         = 'Heure de d&eacute;but';
$lang['heure_fin_trav']                         = 'Heure de fin';
$lang['heure_debut_prevue']                     = 'Heure de d&eacute;but';
$lang['heure_fin_prevue']                       = 'Heure de fin';
$lang['titre']                                  = 'Titre';
$lang['referent_travaux']                       = 'R&eacute;f&eacute;rent';
$lang['accespar']                               = 'Ouvert aux parents de ';
$lang['nb_inscrits_max']                        = 'Nombre maximum';
$lang['type_participant']                       = 'Participant';
$lang['REGISTRED']                              = 'Inscrits &agrave; la session';
$lang['REGISTRED_NONE']                         = 'pas d\'inscrit';

// Information générique session (sécurité, assurances...)
$lang['INFO_GENE_SESSION']                      = '<p class="red">Veuillez noter que pour des raisons de s&eacute;curit&eacute; et d\'assurances, il n\'est plus possible : <br/> - de faire venir les enfants sur le chantier
<br/> - de faire travailler des enfants mineurs (par exemple en remplacement d\'un des parents) <br /> Merci pour votre compr&eacute;hension</p>';

// =====================================================================
// Validation des présences par le référent
// =====================================================================

// Sous-titre de la page (le titre Admwork_controller_my_sessions est dans menu_lang.php)
$lang['Admwork_controller_my_sessions_subtitle']    = 'les sessions où je suis référent';
$lang['REF_MY_SESSIONS_TITLE']                      = 'Mes sessions à animer';
$lang['REF_MY_SESSIONS_SUBTITLE']                   = 'Retrouvez ici les sessions où vous êtes référent, avec la possibilité de valider les présences après chaque session.';
$lang['REF_NO_SESSIONS']                            = 'Vous n\'êtes référent d\'aucune session pour le moment.';
$lang['REF_VALIDATE_ACTION']                        = 'Valider les présences';
$lang['REF_PREVIEW_ACTION']                         = 'Voir les inscrits';
$lang['REF_NOT_YET']                                = 'session pas encore eu lieu';

// Formulaire de validation
$lang['REF_VALIDATE_PAGE_TITLE']                    = 'Validation des présences';
$lang['REF_VALIDATE_INTRO']                         = 'En tant que référent de cette session, merci de valider la présence des parents inscrits.';
$lang['REF_VALIDATE_HELP']                          = 'Cochez les parents présents, ajustez le nombre d\'unités effectives si besoin, ajoutez un commentaire, et signalez les absences non excusées. Votre validation sera ensuite confirmée par le bureau.';
$lang['REF_VALIDATE_SUBMIT']                        = 'Enregistrer les présences';
$lang['REF_VALIDATE_CONFIRM']                       = 'Confirmer l\'enregistrement des présences ?';
$lang['REF_VALIDATE_SAVED']                         = 'Présences enregistrées. Merci !';

// Mode aperçu (avant la session)
$lang['REF_PREVIEW_PAGE_TITLE']                     = 'Votre session à venir';
$lang['REF_PREVIEW_INTRO']                          = 'Vous êtes référent de cette session.';
$lang['REF_PREVIEW_DAYS_LEFT']                      = 'Elle aura lieu dans %d jours.';
$lang['REF_PREVIEW_TOMORROW']                       = 'Elle aura lieu demain.';
$lang['REF_PREVIEW_TODAY']                          = 'Elle a lieu aujourd\'hui !';
$lang['REF_PREVIEW_HELP']                           = 'Vous trouverez ci-dessous la liste des parents inscrits. Le jour de la session, ce même lien vous permettra de valider leur présence.';
$lang['REF_PREVIEW_FOOTER']                         = 'La validation des présences sera activée à partir du jour de la session.';

// Champs de saisie validation
$lang['PRESENT']                                    = 'Présent';
$lang['REF_COMMENT']                                = 'Commentaire';
$lang['REF_COMMENT_PLACEHOLDER']                    = 'ex: en retard, est reparti plus tôt…';
$lang['REF_COMMENT_GLOBAL']                         = 'Commentaire général sur la session (optionnel)';
$lang['REF_COMMENT_GLOBAL_PLACEHOLDER']             = 'Ambiance, points à signaler au bureau…';
$lang['REF_NO_SHOW']                                = 'No-show';
$lang['REF_NO_SHOW_LABEL']                          = 'Désinscrire';

// Token / accès par lien email
$lang['REF_TOKEN_ERROR_TITLE']                      = 'Lien invalide ou expiré';
$lang['REF_TOKEN_INVALID']                          = 'Ce lien de validation n\'est plus valide. Il a peut-être expiré (durée de 30 jours après la session) ou il est incorrect.';
$lang['REF_TOKEN_ERROR_HELP']                       = 'Vous pouvez vous connecter à votre espace pour retrouver vos sessions à valider, ou contacter le bureau.';
$lang['LOGIN_TO_CONTINUE']                          = 'Se connecter';

// Trace côté admin (vue sys)
$lang['VALIDATED_BY_REF']                           = 'Validé par le référent le';
