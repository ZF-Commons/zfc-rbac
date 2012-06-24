<?php

namespace SpiffySecurity\Provider\Permission;

use SpiffySecurity\Rbac\Rbac;
use Zend\ServiceManager\ServiceLocatorInterface;

class InMemory implements PermissionInterface
{
    protected $spec = array();

    public function __construct(array $spec)
    {
        $this->spec = $spec;
    }

    /**
     * Load permissions into roles.
     *
     * @abstract
     * @param Rbac $rbac
     * @return mixed
     */
    public function load(Rbac $rbac)
    {
        foreach($this->spec as $role => $permissions) {
            foreach((array) $permissions as $permission) {
                $rbac->getRole($role)->addPermission($permission);
            }
        }
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return mixed
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        return new \SpiffySecurity\Provider\Permission\InMemory($spec);
    }
}
