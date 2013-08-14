<?php

namespace ZfcRbac\Provider;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\ListenerAggregateInterface;

interface ProviderInterface extends ListenerAggregateInterface
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
}
