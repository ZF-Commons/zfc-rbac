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
        /* @var \Zend\Mvc\Application $application */
        $application    = $event->getTarget();
        $serviceManager = $application->getServiceManager();
        $eventManager   = $application->getEventManager();

        /* @var \ZfcRbac\Guard\GuardInterface[]|array $guards */
        $guards = $serviceManager->get('ZfcRbac\Guards');

        // Register listeners, if any
        foreach ($guards as $guard) {
            $eventManager->attachAggregate($guard);
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
