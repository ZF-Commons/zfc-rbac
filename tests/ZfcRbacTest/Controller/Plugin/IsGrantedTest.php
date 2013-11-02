<?php

namespace ZfcRbacTest\Controller\Plugin;

use ZfcRbac\Service\Rbac as RbacService;
use ZfcRbac\Controller\Plugin\IsGranted;
use ZfcRbacTest\RbacServiceTestCase;

class IsGrantedTest extends RbacServiceTestCase
{
    protected $rbacService;

    public function setUp()
    {
        $rbacService = new RbacService();
        $rbacService->setIdentity('role1');

        $rbac = $rbacService->getRbac();
        $rbac->addRole('role1');
        $rbac->getRole('role1')->addPermission('test.success');

        $this->rbacService = $rbacService;
    }

    public function testSimple()
    {
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');
        $serviceManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo('ZfcRbac\Service\Rbac'))
            ->will($this->returnValue($this->rbacService));

        $controller = $this->getMock('Zend\Mvc\Controller\AbstractActionController');
        $controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $plugin = new IsGranted();
        $plugin->setController($controller);

        $this->assertEquals($this->rbacService->isGranted('test.fail'), $plugin('test.fail'));
        $this->assertEquals($this->rbacService->isGranted('test.success'), $plugin('test.success'));

        $this->assertTrue($plugin('test.success'));
        $this->assertFalse($plugin('test.fail'));
    }

    public function testDynamicAssertion()
    {
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');
        $serviceManager->expects($this->any())
            ->method('get')
            ->with($this->equalTo('ZfcRbac\Service\Rbac'))
            ->will($this->returnValue($this->rbacService));

        $controller = $this->getMock('Zend\Mvc\Controller\AbstractActionController');
        $controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceManager));

        $plugin = new IsGranted();
        $plugin->setController($controller);

        $trueAssert = function($rbacService) {
            return true;
        };
        $falseAssert = function($rbacService) {
            return false;
        };

        $this->assertTrue($plugin('test.success', $trueAssert));
        $this->assertFalse($plugin('test.success', $falseAssert));
        $this->assertFalse($plugin('test.fail', $trueAssert));
        $this->assertFalse($plugin('test.fail', $falseAssert));
    }
}