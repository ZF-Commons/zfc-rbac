<?php

namespace ZfcRbacTest\Service;

use Zend\Authentication\AuthenticationService;
use ZfcRbac\Identity\StandardIdentity;
use ZfcRbacTest\RbacServiceTestCase;
use ZfcRbacTest\Service\Asset\Assertion\SimpleTrueAssertion;
use ZfcRbacTest\Service\Asset\Firewall\SimpleFirewall;

class RbacTest extends RbacServiceTestCase
{
    public function setUp()
    {
        $rbacService = $this->getRbacService();
        $rbac        = $rbacService->getRbac();

        $rbac->addRole('parent');
        $parent = $rbac->getRole('parent');

        $parent->addChild('child1');
        $parent->addChild('child2');
        $parent->addChild('child3');

        $child1 = $rbac->getRole('child1');
        $child1->addChild('subchild1');

        $parent->addPermission('parent.permission');
        $child1->addPermission('child1.permission');
    }

    public function testHasRoleAcceptsStrings()
    {
        $this->getRbacService()->setIdentity('child1');

        $this->assertEquals(true, $this->getRbacService()->hasRole('child1'));
        $this->assertEquals(false, $this->getRbacService()->hasRole('parent'));
    }

    public function testHasRoleAcceptsArrays()
    {
        $this->getRbacService()->setIdentity('child1');

        $this->assertEquals(true, $this->getRbacService()->hasRole(array('parent', 'child1')));
        $this->assertEquals(true, $this->getRbacService()->hasRole(array('child1', 'child2')));
        $this->assertEquals(false, $this->getRbacService()->hasRole(array('parent', 'child2')));
    }

    public function testHasRoleChecksInheritance()
    {
        $this->getRbacService()->setIdentity('parent');

        $this->assertEquals(true, $this->getRbacService()->hasRole('child1'));
        $this->assertEquals(true, $this->getRbacService()->hasRole('subchild1'));
    }

    public function testIsGrantedExpectedString()
    {
        $this->setExpectedException('InvalidArgumentException', 'isGranted() expects a string for permission');
        $this->getRbacService()->isGranted(false);
    }

    public function testIsGrantedAcceptsAssertionInterface()
    {
        $assertion = new SimpleTrueAssertion();

        $this->getRbacService()->setIdentity('parent');
        $this->assertEquals(true, $this->getRbacService()->isGranted('parent.permission', $assertion));
    }

    public function testIsGrantedAcceptsCallableAssertions()
    {
        $callable = function() {
            return true;
        };

        $this->getRbacService()->setIdentity('parent');
        $this->assertEquals(true, $this->getRbacService()->isGranted('parent.permission', $callable));
    }

    public function testIsGrantedThrowsExceptionOnInvalidAssertion()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Assertions must be a Callable or an instance of ZfcRbac\AssertionInterface'
        );

        $this->getRbacService()->isGranted('parent.permission', 'failblog.com');
    }

    public function testIsGrantedAcceptsNullAssertions()
    {
        $this->getRbacService()->setIdentity('child1');
        $this->assertEquals(true, $this->getRbacService()->isGranted('child1.permission', null));
    }

    public function testAddFirewallThrowsExceptionOnDuplicateName()
    {
        $firewall = new SimpleFirewall();

        $this->setExpectedException('InvalidArgumentException');
        $this->getRbacService()->addFirewall($firewall);
        $this->getRbacService()->addFirewall($firewall);
    }

    public function testGetFirewallThrowsExceptionOnMissingFirewall()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->getRbacService()->getFirewall('doesnotexist');
    }

    public function testSetIdentityAcceptsNullIdentity()
    {
        $this->getRbacService()->setIdentity(null);
        $this->assertEquals(
            $this->getRbacService()->getOptions()->getAnonymousRole(),
            $this->getFirstRoleFromRbacService()
        );
    }

    public function testSetIdentityAcceptsAuthenticationServiceIdentity()
    {
        $authService = new AuthenticationService();
        $identity    = $authService->getIdentity();

        $this->getRbacService()->setIdentity($identity);
    }

    public function testSetIdentityAcceptsIdentityInterface()
    {
        $identity = new StandardIdentity('foo');

        $this->getRbacService()->setIdentity($identity);
        $this->assertEquals('foo', $this->getFirstRoleFromRbacService());
    }

    public function testSetIdentityAcceptsStringIdentity()
    {
        $this->getRbacService()->setIdentity('foo');
        $this->assertEquals('foo', $this->getFirstRoleFromRbacService());
    }

    public function testSetIdentityThrowsExceptionOnInvalidIdentityType()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->getRbacService()->setIdentity(false);
    }

    public function testGetRbacReturnsInstanceOfRbac()
    {
        $this->assertInstanceOf('Zend\Permissions\Rbac\Rbac', $this->getRbacService()->getRbac());
    }

    protected function getFirstRoleFromRbacService()
    {
        $identity = $this->getRbacService()->getIdentity();
        $roles    = $identity->getRoles();

        return array_shift($roles);
    }
}