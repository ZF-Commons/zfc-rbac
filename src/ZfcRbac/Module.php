<?php

namespace ZfcRbac;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

/**
 * Module class for ZfcRbac
 */
class Module implements BootstrapListenerInterface, ConfigProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $event)
    {
        /** @var \Zend\Mvc\Application $application */
        $application    = $event->getTarget();
        $serviceManager = $application->getServiceManager();
        $eventManager   = $application->getEventManager();

        /** @var \ZfcRbac\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $serviceManager->get('ZfcRbac\Options\ModuleOptions');

        // Register the guards listeners (if specified)
        $guardsOptions = $moduleOptions->getGuards();

        if ($routeRules = $guardsOptions->getRouteRules()) {
            $eventManager->attachAggregate($serviceManager->get('ZfcRbac\Guard\RouteGuard'));
        }

        if ($controllerRules = $guardsOptions->getControllerRules()) {
            $eventManager->attachAggregate($serviceManager->get('ZfcRbac\Guard\ControllerGuard'));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}
