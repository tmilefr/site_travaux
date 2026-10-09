<?php

// =====================================================================
// Traductions pour les MENUS — chargées sur toutes les pages.
//
// App\Cells\MenuCell lit app/Models/json/Menus.json et appelle
// tr($element->name) pour chaque entrée. Comme le menu est rendu sur toutes les pages (via template/
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

return [
    'Acl_users_controller' => 'Utilisateurs',
    'Acl_roles_controller' => 'R&ocirc;les',
    'Acl_controllers_controller' => 'ACL',
    'Sendmail_controller' => 'Envois d\' e-mails',
    'Option_controller' => 'Options',
    'Templates_controller' => 'Modèle de texte',
    'Translations_controller' => 'Traductions',
    'Parameters' => 'Param&egrave;tres',
    'Orgchart_controller_organisation' => 'Notre association',
    'Orgchart_controller_orga' => ' Les commissions',
    'Candidatures_controller_list' => 'Gestion des candidatures',
    'Orgchart_controller_list' => 'Gestion des commissions',
    'GroupesMembers_controller_list' => 'Gestion des membres de commissions',
    'Files_controller_list' => 'Gestion des fichiers',
    'Event_controller_list' => 'Gestion des évènements',
    'Cantine_controller_register' => 'Garde du midi',
    'Cantine_controller_register_fam' => 'Garde du midi',
    'Cantine_controller_register_sys' => 'Garde du midi',
    'Cantine_controller_config' => 'Paramétrage garde midi',
    'Familys_controller_histo' => 'Mon compte',
    'Familys_controller_histo_fam' => 'Mes unités',
    'Familys_controller_histo_sys' => 'Les familles',
    'Familys_controller_list' => 'Gestion des familles',
    'Units_controller_valid' => 'Validation des unit&eacute;s sur sessions',
    'Units_controller_list' => 'Unit&eacute;s suppl&eacute;mentaires',
    'Familys_controller_skills' => 'Comp&eacute;tences des familles',
    'Familys_controller_stats' => 'Synthèse unités',
    'Admwork_controller_register' => 'Travaux',
    'Admwork_controller_register_fam' => 'Travaux disponibles',
    'Admwork_controller_register_sys' => 'Les travaux',
    'Admwork_controller_list' => 'Gestion des travaux',
    'Admwork_controller_worker' => 'Statistiques participants',
    'Admwork_controller_my_sessions' => 'Mes sessions à animer',
    'Myaccount' => 'Mon Compte',
    'Login_out' => 'D&eacute;connection',
    'Maintenance_in_progress' => 'Maintenance en cours',
];
