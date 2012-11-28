<?php

namespace ZfcRbac\Provider;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\AbstractRole;

abstract class AbstractProvider implements ListenerAggregateInterface, ProviderInterface
{
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

            $name = ($role instanceof AbstractRole) ? $role->getName() : $role;
            
            if (!empty($roles[$name])) {
                $this->recursiveroles($rbac, $roles, $name);
            }
        }
    }
}