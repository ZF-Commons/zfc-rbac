<?php

namespace ZfcRbac\Provider;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractProvider implements ListenerAggregateInterface
{
    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return mixed
     */
    abstract public static function factory(ServiceLocatorInterface $sl, array $spec);

    /**
     * Recursive function to add roles according to their parent role.
     *
     * @param Rbac $rbac
     * @param $roles
     * @param int $parentName
     * @return void
     */
    protected function recursiveRoles(Rbac $rbac, $roles, $parentName = 0)
    {
        if (!isset($roles[$parentName])) {
            return;
        }
        foreach ((array) $roles[$parentName] as $role) {
            if ($parentName) {
                $rbac->getRole($parentName)->addChild($role);
            } else {
                $rbac->addRole($role);
            }

            if (!empty($roles[$role])) {
                $this->recursiveroles($rbac, $roles, $role);
            }
        }
    }
}