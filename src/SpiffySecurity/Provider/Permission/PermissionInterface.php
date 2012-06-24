<?php

namespace SpiffySecurity\Provider\Permission;

use SpiffySecurity\Rbac\Rbac;
use Zend\ServiceManager\ServiceLocatorInterface;

interface PermissionInterface
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
     * Load permissions into roles.
     *
     * @abstract
     * @param Rbac $rbac
     * @return mixed
     */
    public function load(Rbac $rbac);
}