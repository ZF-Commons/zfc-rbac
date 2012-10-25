<?php

namespace ZfcRbac\Provider;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

interface ProviderInterface
{
    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return mixed
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec);

    /**
     * Attach to the listeners.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function attachListeners(EventManagerInterface $events);
}