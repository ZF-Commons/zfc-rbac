<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZfcRbac\Rbac;

use Generator;
use ZfcRbac\Rbac\Exception\RuntimeException;
use ZfcRbac\Rbac\Role\HierarchicalRoleInterface;
use ZfcRbac\Rbac\Role\RoleInterface;
use Traversable;

/**
 * Rbac object. It is used to check a permission against roles
 */
class Rbac
{
    /**
     * Determines if access is granted by checking the roles for permission.
     *
     * @param  RoleInterface|RoleInterface[]|Traversable $roles
     * @param  string                                    $permission
     * @return bool
     */
    public function isGranted($roles, $permission)
    {
        if (!is_string($permission)) {
            throw new RuntimeException(sprintf(
                'Permission must be a string, "%s" given',
                is_object($permission) ? get_class($permission) : gettype($permission)
            ));
        }

        if ($roles instanceof RoleInterface) {
            $roles = [$roles];
        }

        foreach ($this->flattenRoles($roles) as $role) {
            /* @var RoleInterface $role */
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  RoleInterface[]|Traversable $roles
     * @return Generator
     */
    protected function flattenRoles($roles)
    {
        foreach ($roles as $role) {
            yield $role;

            if (!$role instanceof HierarchicalRoleInterface) {
                continue;
            }

            $children = $this->flattenRoles($role->getChildren());

            foreach ($children as $child) {
                yield $child;
            }
        }
    }
}
