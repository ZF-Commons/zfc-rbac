<?php

namespace ZfcRbacTest\Controller\Plugin;

use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\Controller\Plugin\HasRole;
use ZfcRbacTest\RbacServiceTestCase;
use ZfcRbacTest\Service\Asset\Controller\DummyController;

class HasRoleTest extends RbacServiceTestCase
{
    public function testHasRolePlugin()
    {
        $rbacService = new RbacService();
        $rbacService->setIdentity('role1');

        $rbac = $rbacService->getRbac();
        $rbac->addRole('role1');
        $rbac->addRole('role2');

        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');
        $serviceManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo('ZfcRbac\Service\Rbac'))
            ->will($this->returnValue($rbacService));

        $controller = $this->getMock('Zend\Mvc\Controller\AbstractActionController');
        $controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $plugin = new HasRole();
        $plugin->setController($controller);

        $this->assertEquals(true, $plugin('role1'));
        $this->assertEquals(false, $plugin('role2'));
        $this->assertEquals(true, $plugin(array('role1', 'role2')));
        $this->assertEquals(true, $plugin(array('role1', 'unknown')));
        $this->assertEquals(false, $plugin(array('role2', 'unknown')));
        $this->assertEquals(false, $plugin(array('unknown1', 'unknown2')));

        $this->assertEquals($rbacService->hasRole('role1'), $plugin('role1'));
        $this->assertEquals($rbacService->hasRole(array('role1', 'role2')), $plugin(array('role1', 'role2')));
        $this->assertEquals($rbacService->hasRole('role2'), $plugin('role2'));
        $this->assertEquals($rbacService->hasRole('unknown'), $plugin('unknown'));
        $this->assertEquals($rbacService->hasRole(array('role1', 'unknown')), $plugin(array('role1', 'unknown')));
        $this->assertEquals($rbacService->hasRole(array('role2', 'unknown')), $plugin(array('role2', 'unknown')));
        $this->assertEquals($rbacService->hasRole(array('unknown1', 'unknown2')), $plugin(array('unknown1', 'unknown2')));
    }
}
