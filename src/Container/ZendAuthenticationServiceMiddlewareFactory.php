<?php

namespace ZfcRbac\Container;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use ZfcRbac\Middleware\ZendAuthenticationServiceMiddleware;

/**
 * Class ZendAuthenticationServiceMiddlewareFactory
 *
 * @package ZfcRbac\Container
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
