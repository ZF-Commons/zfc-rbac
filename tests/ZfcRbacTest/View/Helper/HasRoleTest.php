<?php

namespace ZfcRbacTest\View\Helper;

use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\View\Helper\HasRole;
use ZfcRbacTest\RbacServiceTestCase;

class HasRoleTest extends RbacServiceTestCase
{
    public function testHasRoleViewHelper()
    {
        $rbacService = new RbacService;
        $rbacService->setIdentity('role1');

        $rbac = $rbacService->getRbac();
        $rbac->addRole('role1');
        $rbac->addRole('role2');

        $helper = new HasRole($rbacService);

        $this->assertEquals(true, $helper('role1'));
        $this->assertEquals(false, $helper('role2'));
        $this->assertEquals(true, $helper(array('role1', 'role2')));
        $this->assertEquals(true, $helper(array('role1', 'unknown')));
        $this->assertEquals(false, $helper(array('role2', 'unknown')));
        $this->assertEquals(false, $helper(array('unknown1', 'unknown2')));

        $this->assertEquals($rbacService->hasRole('role1'), $helper('role1'));
        $this->assertEquals($rbacService->hasRole(array('role1', 'role2')), $helper(array('role1', 'role2')));
        $this->assertEquals($rbacService->hasRole('role2'), $helper('role2'));
        $this->assertEquals($rbacService->hasRole('unknown'), $helper('unknown'));
        $this->assertEquals($rbacService->hasRole(array('role1', 'unknown')), $helper(array('role1', 'unknown')));
        $this->assertEquals($rbacService->hasRole(array('role2', 'unknown')), $helper(array('role2', 'unknown')));
        $this->assertEquals($rbacService->hasRole(array('unknown1', 'unknown2')), $helper(array('unknown1', 'unknown2')));
    }
}
