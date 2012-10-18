<?php

namespace ZfcRbac\Firewall\Listener;

use Zend\Mvc\MvcEvent;

class Controller
{
    public static function onRoute(MvcEvent $e)
    {
        $app        = $e->getTarget();
        $security   = $app->getServiceManager()->get('ZfcRbac\Service\Security');
        $match      = $app->getMvcEvent()->getRouteMatch();
        $controller = $match->getParam('controller');
        $action     = $match->getParam('action');
        $resource   = sprintf('%s:%s', $controller, $action);

        try {
            if (!$security->getFirewall('controller')->isGranted($resource)) {
                $e->setError($security::ERROR_CONTROLLER_UNAUTHORIZED)
                  ->setParam('identity', $security->getIdentity())
                  ->setParam('controller', $controller)
                  ->setParam('action', $action);

                $app->getEventManager()->trigger('dispatch.error', $e);
            }
        } catch (\InvalidArgumentException $e) {
            return;
        }
    }
}
