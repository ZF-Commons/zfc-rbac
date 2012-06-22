<?php

namespace SpiffySecurity\Provider;

use SpiffySecurity\Rbac\Rbac;
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

    public function load(Rbac $rbac);
}