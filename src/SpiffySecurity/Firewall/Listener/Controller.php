<?php

namespace SpiffySecurity\Firewall\Listener;

use Zend\Mvc\MvcEvent;

class Controller
{
    public function onRoute(MvcEvent $e)
    {
        $app      = $e->getTarget();
        $sm       = $app->getServiceManager();
        $security = $sm->get('SpiffySecurity\Service\Security');
        $acl      = $security->getAcl();
        $match    = $app->getMvcEvent()->getRouteMatch();

        $controller = $match->getParam('controller');
        $action     = $match->getParam('action');

        $controllerResource = "controller:{$controller}";
        $actionResource     = "controller:{$controller}:{$action}";
        $role               = $security->getRole();
        $resource           = null;

        if ($acl->hasResource($actionResource)) {
            $resource = $actionResource;
        } else if ($acl->hasResource($controllerResource)) {
            $resource = $controllerResource;
        }

        if ($resource && !$acl->isAllowed($role, $resource)) {
            $e->setError($security::ERROR_CONTROLLER_UNAUTHORIZED)
              ->setParam('role', $role)
              ->setParam('controller', $controller)
              ->setParam('action', $action);

            $app->events()->trigger('dispatch.error', $e);
        }
    }
}
