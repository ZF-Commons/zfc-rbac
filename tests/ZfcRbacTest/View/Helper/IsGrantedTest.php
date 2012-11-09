<?php

namespace ZfcRbacTest\View\Helper;

use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\View\Helper\IsGranted;
use ZfcRbacTest\RbacServiceTestCase;

class IsGrantedTest extends RbacServiceTestCase
{
    public function testInvokingProxiesToRbacService()
    {
        $rbacService = $this->getRbacService();
        $rbacService->setIdentity('test');

        $rbac = $rbacService->getRbac();
        $rbac->addRole('test');
        $rbac->getRole('test')->addPermission('test.success');

        $helper = new IsGranted($rbacService);

        $this->assertEquals($rbacService->isGranted('test.fail'), $helper('test.fail'));
        $this->assertEquals($rbacService->isGranted('test.success'), $helper('test.success'));
        $this->assertNotEquals(false, $helper('test.success'));
    }
}