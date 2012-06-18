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

        if (!$security->getFirewall('route')->isAllowed($security->getRole(), $route)) {
            $e->setError($security::ERROR_ROUTE_UNAUTHORIZED)
                ->setParam('role', $security->getRole())
                ->setParam('route', $route);

            $app->events()->trigger('dispatch.error', $e);
        }
    }
}
