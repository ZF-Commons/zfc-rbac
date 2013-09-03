<?php

namespace ZfcRbac\Provider;

use Zend\Permissions\Rbac\Rbac;

abstract class AbstractProvider implements ProviderInterface
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
                $childRole = $rbac->hasRole($role) ? $rbac->getRole($role) : $role;
                $rbac->getRole($parentName)->addChild($childRole);
            } else {
                $rbac->addRole($role);
            }

            if (!empty($roles[$role])) {
                $this->recursiveroles($rbac, $roles, $role);
            }
        }
    }
}