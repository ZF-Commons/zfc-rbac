<?php

namespace ZfcRbac\Provider;

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
}
