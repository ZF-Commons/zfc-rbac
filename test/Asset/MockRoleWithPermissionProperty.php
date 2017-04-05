<?php

namespace ZfcRbacTest\Asset;

use ZfcRbac\Rbac\Role\RoleInterface;

class MockRoleWithPermissionProperty implements RoleInterface
{
    private $permissions = ['permission-property-a', 'permission-property-b'];

    public function getName()
    {
        return 'role-with-permission-property';
    }
    public function hasPermission($permission)
    {
        return false;
    }
}
