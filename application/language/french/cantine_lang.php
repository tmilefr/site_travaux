<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// =====================================================================
// Traductions pour le module Cantine (Garde du midi)
// À intégrer dans application/language/french/traduction_lang.php
// =====================================================================

// Titres de menu / controller
$lang['GESTION_Cantine_controller']        = 'Garde du midi';
$lang['Cantine_controller_register']       = 'Garde du midi';
$lang['Cantine_controller_register_fam']   = 'Garde du midi';
$lang['Cantine_controller_register_sys']   = 'Garde du midi';
$lang['Cantine_controller_config']         = 'Paramétrer la garde';

// Vue parent : agenda hebdomadaire (cartes jour)
$lang['cantine_title']            = 'Inscriptions à la garde du midi';
$lang['cantine_subtitle']         = 'Inscrivez-vous pour encadrer les enfants pendant la pause méridienne.';
$lang['cantine_config_link']      = 'Paramétrer';
$lang['cantine_back_to_agenda']   = 'Retour à l\'agenda';

// Navigation semaine
$lang['cantine_prev_week']        = 'Précédente';
$lang['cantine_this_week']        = 'Semaine en cours';
$lang['cantine_next_week']        = 'Suivante';

// Compteurs
$lang['cantine_stat_days']        = 'Jours de garde';
$lang['cantine_stat_sessions']    = 'Sessions à venir';
$lang['cantine_stat_mine']        = 'Mes inscriptions';
$lang['cantine_stat_open']        = 'Places restantes';

// Contenu carte jour
$lang['cantine_day_inactive']     = 'Pas de garde ce jour';
$lang['cantine_registered']       = 'Inscrits';
$lang['cantine_slot_free']        = 'Place libre';
$lang['cantine_you']              = 'vous';

// Boutons action
$lang['cantine_btn_register']     = 'M\'inscrire';
$lang['cantine_btn_cancel']       = 'Me désinscrire';
$lang['cantine_btn_view']         = 'Détails';
$lang['cantine_full']             = 'Complet';
$lang['cantine_passed']           = 'Date passée';
$lang['cantine_validated']        = 'Validée';
$lang['cantine_confirm_cancel']   = 'Confirmer la désinscription ?';
$lang['cantine_register_hint']    = 'Cliquez sur « M\'inscrire » pour prendre un créneau. Vous pourrez vous désinscrire tant que le référent n\'a pas encore validé l\'unité.';

// Format liste (gardé pour compat éventuelle)
$lang['cantine_col_date']         = 'Date';
$lang['cantine_col_hours']        = 'Horaires';
$lang['cantine_col_school']       = 'École';
$lang['cantine_col_slots']        = 'Inscrits';
$lang['cantine_col_units']        = 'Unités';
$lang['cantine_col_action']       = 'Action';
$lang['cantine_no_sessions']            = 'Aucune session de garde du midi n\'est actuellement planifiée pour cette semaine.';
$lang['cantine_no_sessions_admin_cta']  = 'Générer les sessions.';

// Jours de la semaine
$lang['cantine_day_1']            = 'Lundi';
$lang['cantine_day_2']            = 'Mardi';
$lang['cantine_day_3']            = 'Mercredi';
$lang['cantine_day_4']            = 'Jeudi';
$lang['cantine_day_5']            = 'Vendredi';

// Vue admin : paramétrage + génération
$lang['cantine_config_title']     = 'Paramétrage de la garde du midi';
$lang['cantine_config_subtitle']  = 'Définissez les règles puis générez les sessions sur la période souhaitée.';
$lang['cantine_school']           = 'École';
$lang['cantine_nb_upcoming']      = 'sessions planifiées à venir';

// Étape 1 : config jours
$lang['cantine_step1_title']      = 'Règles de la garde du midi';
$lang['cantine_step1_hint']       = 'Ces règles serviront à générer les sessions à l\'étape 2. Vous pouvez les modifier à tout moment : elles ne changent que les futures sessions générées.';
$lang['cantine_day_needed']       = 'Garde nécessaire';
$lang['cantine_nb_parents']       = 'Nombre de parents requis';
$lang['cantine_nb_units']         = 'Unités accordées / parent';
$lang['cantine_hours']            = 'Horaires (début / fin)';
$lang['cantine_referent']         = 'Référent validateur';
$lang['cantine_referent_none']    = 'Aucun référent';
$lang['cantine_save_config']      = 'Enregistrer les règles';

// Étape 2 : génération
$lang['cantine_step2_title']      = 'Générer les sessions';
$lang['cantine_step2_hint']       = 'Création des sessions de garde en masse pour une période. Les dates déjà existantes sont ignorées (pas de doublon).';
$lang['cantine_period_school_end']= 'Jusqu\'à la fin de l\'année scolaire';
$lang['cantine_period_custom']    = 'Période personnalisée';
$lang['cantine_date_deb']         = 'Du';
$lang['cantine_date_fin']         = 'Au';
$lang['cantine_btn_generate']     = 'Générer les sessions';
$lang['cantine_confirm_generate'] = 'Générer les sessions cantine pour cette période ? Les dates déjà présentes seront ignorées.';

// Historique générations
$lang['cantine_gen_history']      = 'Dernières générations effectuées';
$lang['cantine_gen_date']         = 'Effectuée le';
$lang['cantine_gen_period']       = 'Période';
$lang['cantine_gen_created']      = 'Créées';
$lang['cantine_gen_skipped']      = 'Ignorées';

$lang['cantine_config_hint']      = 'Les sessions générées apparaissent automatiquement dans l\'écran « Travaux disponibles », dans les sessions du référent et, après validation, dans l\'historique des unités de chaque famille.';
