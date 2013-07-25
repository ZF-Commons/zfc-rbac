<?php

namespace ZfcRbac\Firewall\Listener;

use InvalidArgumentException;
use Zend\Mvc\MvcEvent;

class Controller
{
    /**
     * @param MvcEvent $e
     */
    public static function onRoute(MvcEvent $event)
    {
        $app         = $event->getTarget();
        $rbacService = $app->getServiceManager()->get('ZfcRbac\Service\Rbac');
        $match       = $app->getMvcEvent()->getRouteMatch();
        $controller  = $match->getParam('controller');
        $action      = $match->getParam('action');
        $resource    = sprintf('%s:%s', $controller, $action);

        try {
            if ($rbacService->getFirewall('controller')->isGranted($resource)) {
                return;
            }
        } catch (InvalidArgumentException $e) {
            //if Exception, default to unauthorized
        }
        try {
            $event->setError($rbacService::ERROR_CONTROLLER_UNAUTHORIZED)
                ->setParam('identity', $rbacService->getIdentity())
                ->setParam('controller', $controller)
                ->setParam('action', $action);
            $app->getEventManager()->trigger('dispatch.error', $event);
        } catch (InvalidArgumentException $e) {
            return;
        }
    }
}
