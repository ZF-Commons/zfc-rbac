<?php

namespace ZfcRbacTest\View\Helper;

use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\View\Helper\HasRole;
use ZfcRbacTest\RbacServiceTestCase;

class HasRoleTest extends RbacServiceTestCase
{
    public function testInvokingProxiesToRbacService()
    {
        $rbacService = $this->getRbacService();
        $rbacService->setIdentity(array('admin', 'member'));

        $identity = $rbacService->getIdentity();

        $helper = new HasRole($identity);

        $this->assertTrue($helper('admin'));
        $this->assertTrue($helper('member'));
        $this->assertFalse($helper('other_role'));
    }
}