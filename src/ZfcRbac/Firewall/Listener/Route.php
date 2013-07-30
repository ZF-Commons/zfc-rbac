<?php

namespace ZfcRbac\Firewall\Listener;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;

class Route
{
    /**
     * @param MvcEvent $e
     */
    public static function onRoute(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest) {
            return;
        }
        $app         = $e->getTarget();
        $route       = $e->getRouteMatch()->getMatchedRouteName();
        $rbacService = $app->getServiceManager()->get('ZfcRbac\Service\Rbac');

        if (!$rbacService->getFirewall('route')->isGranted($route)) {
            $e->setError($rbacService::ERROR_ROUTE_UNAUTHORIZED)
              ->setParam('identity', $rbacService->getIdentity())
              ->setParam('route', $route);

            $app->getEventManager()->trigger('dispatch.error', $e);
        }
    }
}
