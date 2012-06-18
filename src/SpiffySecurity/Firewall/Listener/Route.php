<?php

namespace SpiffySecurity\Firewall\Listener;

use Zend\Mvc\MvcEvent;

class Route
{
    public function onRoute(MvcEvent $e)
    {
        $app      = $e->getTarget();
        $sm       = $app->getServiceManager();
        $security = $sm->get('SpiffySecurity\Service\Security');
        $acl      = $security->getAcl();

        $route = 'route:' . $e->getRouteMatch()->getMatchedRouteName();
        if ($acl->hasResource($route)) {
            $role = $security->getRole();

            if (!$acl->isAllowed($role, $route)) {
                $e->setError($security::ERROR_ROUTE_UNAUTHORIZED)
                  ->setParam('role', $role)
                  ->setParam('route', $route);

                $app->events()->trigger('dispatch.error', $e);
            }
        }
    }
}
