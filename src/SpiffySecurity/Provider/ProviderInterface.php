<?php

namespace SpiffySecurity\Provider;

use Zend\ServiceManager\ServiceLocatorInterface;

interface ProviderInterface
{
    public function getRoles();

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
}