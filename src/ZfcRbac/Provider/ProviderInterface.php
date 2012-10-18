<?php

namespace ZfcRbac\Provider;

use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceLocatorInterface;

interface ProviderInterface
{
    /**
     * Factory to create the provider.
     *
     * @static
     * @abstract
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return mixed
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec);

    /**
     * Attach to the listeners.
     *
     * @abstract
     * @param \Zend\EventManager\EventManager $events
     * @return void
     */
    public function attachListeners(EventManager $events);
}