<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// =====================================================================
// Traductions pour Cantine_controller (Garde du midi)
// =====================================================================

// Titre interne (la clé d'entrée du menu est dans menu_lang.php :
// Cantine_controller_register, _register_fam, _register_sys, _config)
$lang['GESTION_Cantine_controller']         = 'Garde du midi';

// =====================================================================
// Vue parent : agenda hebdomadaire
// =====================================================================
$lang['cantine_title']            = 'Agenda de la garde du midi';
$lang['cantine_subtitle']         = 'Inscrivez-vous pour encadrer les enfants pendant la pause méridienne.';
$lang['cantine_config_link']      = 'Paramétrer les jours';
$lang['cantine_back_to_agenda']   = 'Retour à l\'agenda';

// Navigation semaine
$lang['cantine_prev_week']        = 'Semaine précédente';
$lang['cantine_this_week']        = 'Semaine en cours';
$lang['cantine_next_week']        = 'Semaine suivante';

// Compteurs
$lang['cantine_stat_days']        = 'Jours de garde';
$lang['cantine_stat_sessions']    = 'Sessions à venir';
$lang['cantine_stat_mine']        = 'Mes inscriptions';
$lang['cantine_stat_open']        = 'Places restantes';

// Jours
$lang['cantine_day_1']            = 'Lundi';
$lang['cantine_day_2']            = 'Mardi';
$lang['cantine_day_3']            = 'Mercredi';
$lang['cantine_day_4']            = 'Jeudi';
$lang['cantine_day_5']            = 'Vendredi';

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
$lang['cantine_register_hint']    = 'Cliquez sur « M\'inscrire » pour prendre un créneau. Une fois inscrit, vous pouvez vous désinscrire tant que la date n\'est pas passée.';

// Format liste (gardé pour compat éventuelle)
$lang['cantine_col_date']         = 'Date';
$lang['cantine_col_hours']        = 'Horaires';
$lang['cantine_col_school']       = 'École';
$lang['cantine_col_slots']        = 'Inscrits';
$lang['cantine_col_units']        = 'Unités';
$lang['cantine_col_action']       = 'Action';
$lang['cantine_no_sessions']            = 'Aucune session de garde du midi n\'est actuellement planifiée pour cette semaine.';
$lang['cantine_no_sessions_admin_cta']  = 'Générer les sessions.';

// =====================================================================
// Vue admin : paramétrage / agenda
// =====================================================================
$lang['cantine_config_title']     = 'Paramétrage de la garde du midi';
$lang['cantine_config_subtitle']  = 'Définissez les jours où une garde est nécessaire et le nombre de parents requis chaque jour.';
$lang['cantine_school']           = 'École';
$lang['cantine_day_needed']       = 'Garde nécessaire';
$lang['cantine_nb_parents']       = 'Nombre de parents requis';
$lang['cantine_save_config']      = 'Enregistrer la configuration';
$lang['cantine_config_hint']      = 'La configuration s\'applique à partir de la semaine en cours et pour toutes les semaines suivantes de l\'année scolaire.';

// Agenda mensuel des sessions générées (admin)
$lang['cantine_agenda_title']     = 'Agenda des sessions planifiées';
$lang['cantine_agenda_hint']      = 'Vue d\'ensemble des sessions de garde déjà créées. Naviguez de mois en mois.';
$lang['cantine_agenda_prev']      = 'Mois précédent';
$lang['cantine_agenda_next']      = 'Mois suivant';
$lang['cantine_agenda_today']     = 'Mois en cours';
$lang['cantine_agenda_empty']     = 'Aucune session générée pour ce mois.';
$lang['cantine_agenda_full']      = 'complet';
$lang['cantine_agenda_open']      = 'place(s)';
$lang['cantine_agenda_legend_full']    = 'Session complète';
$lang['cantine_agenda_legend_partial'] = 'Places à pourvoir';
$lang['cantine_agenda_legend_empty']   = 'Aucun inscrit';

// Bloc admin unifié (règles + génération)
$lang['cantine_admin_title']      = 'Configuration & génération';
$lang['cantine_admin_hint']       = 'Définissez les règles de la garde du midi puis générez les sessions sur la période souhaitée.';

// Format compact des règles (ligne)
$lang['cantine_rules_col_active']    = 'Actif';
$lang['cantine_rules_col_day']       = 'Jour';
$lang['cantine_rules_col_parents']   = 'Parents';
$lang['cantine_rules_col_units']     = 'Unités';
$lang['cantine_rules_col_hours']     = 'Horaires';
$lang['cantine_rules_col_referent']  = 'Référent';

// =====================================================================
// Sections (utilisées sur la liste agenda parent)
// =====================================================================
$lang['SECTION_UPCOMING']       = 'À venir';
$lang['SECTION_PAST']           = 'Sessions passées';
$lang['SECTION_NO_UPCOMING']    = 'Aucune session à venir pour le moment.';
$lang['SECTION_NO_PAST']        = 'Aucune session passée à afficher.';
$lang['TODAY_MARKER']           = 'Aujourd\'hui';

// Bloc statistiques (vue agenda parent)
$lang['STATS_UPCOMING']         = 'À venir';
$lang['STATS_AVAILABLE']        = 'Places dispo';
$lang['STATS_MY_REGISTRATIONS'] = 'Mes inscriptions';
$lang['STATS_PAST']             = 'Passées';

// Boutons / états (vue agenda parent)
$lang['SEE_DETAILS']            = 'Détails';
$lang['FULL']                   = 'Complet';
$lang['REGISTERED_TAG']         = 'Inscrit';
