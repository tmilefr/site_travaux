<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Routes de l'application.
 *
 * Le routage automatique est désactivé. Chaque contrôleur est déclaré ci-dessous et reçoit
 * toutes ses URL sous la forme historique  Controleur/action/param1/param2...
 * via CrudController::dispatch(), qui ne laisse passer que les actions publiques légitimes
 * (voir CrudController::resolveAction()) et fait suivre les paramètres de l'URL à la méthode.
 *
 * Un nouveau contrôleur doit être ajouté à la liste ci-dessous (vérifié par les tests).
 *
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

$controllers = [
    'Acl_actions_controller',
    'Acl_controllers_controller',
    'Acl_roles_controller',
    'Acl_users_controller',
    'Admwork_controller',
    'Api',
    'Candidatures_controller',
    'Cantine_controller',
    'Event_controller',
    'Familys_controller',
    'Files_controller',
    'GroupesMembers_controller',
    'Home',
    'Options_controller',
    'Orgchart_controller',
    'Parameters',
    'Publics',
    'Sendmail_controller',
    'Templates_controller',
    'Translations_controller',
    'Units_controller',
];

foreach ($controllers as $controller) {
    // L'API accepte tous les verbes HTTP, le reste de l'application GET et POST
    $verbs = $controller === 'Api' ? ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'] : ['GET', 'POST'];

    // Les URL étaient insensibles à la casse (CodeIgniter 3) : forme d'origine et forme minuscule
    // (celle des droits ACL et des liens des e-mails)
    foreach (array_unique([$controller, strtolower($controller)]) as $prefix) {
        $routes->match($verbs, $prefix, $controller . '::dispatch/index');
        $routes->match($verbs, $prefix . '/(:segment)', $controller . '::dispatch/$1');
        $routes->match($verbs, $prefix . '/(:segment)/(:any)', $controller . '::dispatch/$1/$2');
    }
}
