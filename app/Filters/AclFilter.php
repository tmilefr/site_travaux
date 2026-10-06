<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Contrôle d'accès (ACL) appliqué avant chaque action :
 * redirige vers la connexion, la page « pas de droit » ou la maintenance.
 */
class AclFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return service('acl')->Route();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
