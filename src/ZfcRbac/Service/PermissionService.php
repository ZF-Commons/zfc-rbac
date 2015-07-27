<?php

namespace ZfcRbac\Service;

use Application\Service\AbstractService;
use Rbac\Rbac;
use Rbac\Role\RoleInterface;

class PermissionService extends AbstractService
{

    /**
     * @var RoleService
     */
    private $roleService;

    /**
     * @var Rbac
     */
    private $rbac;

    public function __construct(RoleService $roleService, Rbac $rbac)
    {
        $this->roleService = $roleService;
        $this->rbac            = $rbac;
    }

    public function getIdentityPermissions()
    {
        $roles       = $this->roleService->getIdentityRoles();
        $iterator    = $this->rbac->getTraversalStrategy()->getRolesIterator($roles);
        $permissions = [];
        foreach ($iterator as $role) {
            /* @var RoleInterface $role */
            foreach ($role->getPermissions() as $permission) {
                $permissions[] = (string)$permission;
            }
        }

        $permissions = array_unique($permissions);
        asort($permissions);

        return $permissions;
    }
}
