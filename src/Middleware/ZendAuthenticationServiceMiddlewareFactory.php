<?php

namespace ZfcRbac\Middleware;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;

/**
 * Class ZendAuthenticationServiceMiddlewareFactory
 *
 * @package ZfcRbac\Middleware
 * @todo    pull (tbd) configuration options from container and inject
 */
class ZendAuthenticationServiceMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $authService = $container->get(AuthenticationService::class);

        return new ZendAuthenticationServiceMiddleware($authService);
    }
}