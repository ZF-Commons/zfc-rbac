<?php

namespace ZfcRbac\Firewall\Listener;

use InvalidArgumentException;
use Zend\Mvc\MvcEvent;
use Zend\Http\Request as HttpRequest;
use ZfcRbac\Controller\RbacSecuredInterface;
use ZfcRbac\Service\Rbac;
use ZfcRbac\Firewall\ControllerRules as Firewall;

class ControllerRules
{
    /**
     * @param MvcEvent $e
     */
    public static function onDispatch(MvcEvent $e)
    {
        if (!$e->getRequest() instanceof HttpRequest) {
            return;
        }
        $controller = $e->getTarget();

        /* @var \Zend\Mvc\Controller\AbstractActionController|RbacSecuredInterface $controller */
        if ($controller instanceof RbacSecuredInterface) {
            $serviceManager = $controller->getServiceLocator();
            $app = $serviceManager->get('application');
            $mvcEvent = $app->getMvcEvent();
            $match = $mvcEvent->getRouteMatch();
            $controllerName = $match->getParam('controller');
            $action = $match->getParam('action');
            $resource    = sprintf('%s:%s', $controllerName, $action);

            $rules = $controller->getRbacRules();
            $firewall = new Firewall($rules, $controllerName);
            $rbacService = $serviceManager->get('ZfcRbac\Service\Rbac');
            $firewall->setRbac($rbacService);

            try {
                if ($firewall->isGranted($resource)) {
                    return;
                }
            } catch (InvalidArgumentException $ex) {
                //if Exception, default to unauthorized
            }
            try {
                $mvcEvent->setError(Rbac::ERROR_CONTROLLER_UNAUTHORIZED)
                    ->setParam('identity', $rbacService->getIdentity())
                    ->setParam('controller', $controllerName)
                    ->setParam('action', $action);
                $app->getEventManager()->trigger('dispatch.error', $e);
            } catch (InvalidArgumentException $ex) {
                return;
            }
        }
    }
}
