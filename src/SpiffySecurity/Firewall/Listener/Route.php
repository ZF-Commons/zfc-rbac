<?php

namespace SpiffySecurity\Firewall\Listener;

use Zend\Mvc\MvcEvent;

class Route
{
    public function onRoute(MvcEvent $e)
    {
        $app      = $e->getTarget();
        $route    = $e->getRouteMatch()->getMatchedRouteName();
        $security = $app->getServiceManager()->get('SpiffySecurity\Service\Security');
        $firewall = $security->getFirewall('route');

        if (!$security->getFirewall('route')->isGranted($security->getIdentity(), $route)) {
            $e->setError($security::ERROR_ROUTE_UNAUTHORIZED)
                ->setParam('identity', $security->getIdentity())
                ->setParam('route', $route);

            $app->events()->trigger('dispatch.error', $e);
        }
    }
}
