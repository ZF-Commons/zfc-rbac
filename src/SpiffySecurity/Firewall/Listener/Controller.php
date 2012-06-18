<?php

namespace SpiffySecurity\Firewall\Listener;

use Zend\Mvc\MvcEvent;

class Controller
{
    public function onRoute(MvcEvent $e)
    {
        $app        = $e->getTarget();
        $security   = $app->getServiceManager()->get('SpiffySecurity\Service\Security');
        $match      = $app->getMvcEvent()->getRouteMatch();
        $controller = $match->getParam('controller');
        $action     = $match->getParam('action');
        $resource   = sprintf('%s:%s', $controller, $action);

        if (!$security->getFirewall('controller')->isAllowed($security->getRole(), $resource)) {
            $e->setError($security::ERROR_CONTROLLER_UNAUTHORIZED)
              ->setParam('role', $security->getRole())
              ->setParam('controller', $controller)
              ->setParam('action', $action);

            $app->events()->trigger('dispatch.error', $e);
        }
    }
}
