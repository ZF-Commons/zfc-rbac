<?php
namespace ZfcRbacTest\Collector;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use ZfcRbac\Collector\RbacCollector;
use ZfcRbac\Guard\GuardInterface;

/**
 * @covers \ZfcRbac\Collector\RbacCollector
 */
class RbacCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultGetterReturnValues()
    {
        $collector = new RbacCollector();

        $this->assertSame(-100, $collector->getPriority());
        $this->assertSame('zfc_rbac', $collector->getName());
    }

    public function testSerialize()
    {
        $collector  = new RbacCollector();
        $serialized = $collector->serialize();

        $this->assertInternalType('string', $serialized);

        $unserialized = unserialize($serialized);

        $this->assertSame(array(), $unserialized['guards']);
        $this->assertSame(array(), $unserialized['roles']);
        $this->assertSame(array(), $unserialized['permissions']);
        $this->assertSame(array(), $unserialized['options']);
    }

    public function testUnserialize()
    {
        $collector    = new RbacCollector();
        $unserialized = array(
            'guards'      => array('foo' => 'bar'),
            'roles'       => array('foo' => 'bar'),
            'permissions' => array('foo' => 'bar'),
            'options'     => array('foo' => 'bar')
        );
        $serialized   = serialize($unserialized);

        $collector->unserialize($serialized);

        $collection = $collector->getCollection();

        $this->assertInternalType('array', $collection);
        $this->assertSame(array('foo' => 'bar'), $collection['guards']);
        $this->assertSame(array('foo' => 'bar'), $collection['roles']);
        $this->assertSame(array('foo' => 'bar'), $collection['permissions']);
        $this->assertSame(array('foo' => 'bar'), $collection['options']);
    }

    public function testCollectNothingIfNoApplicationIsSet()
    {
        $mvcEvent  = new MvcEvent();
        $collector = new RbacCollector();

        $this->assertNull($collector->collect($mvcEvent));
    }

    public function testCollect()
    {
        $collector         = new RbacCollector();
        $dataIdentityRoles = array('guest', 'user');
        $dataGuards        = array(
            'ZfcRbac\Guard\RouteGuard' => array(
                'admin/*' => array('admin'),
                'login'   => array('guest')
            )
        );

        $moduleOptionsMock = $this->getMock('ZfcRbac\Options\ModuleOptions');
        $moduleOptionsMock->expects($this->once())
                          ->method('getIdentityProvider')
                          ->will($this->returnValue('ZfcRbac\Identity\AuthenticationIdentityProvider'));
        $moduleOptionsMock->expects($this->once())
                          ->method('getGuestRole')
                          ->will($this->returnValue('guest'));
        $moduleOptionsMock->expects($this->once())
                          ->method('getProtectionPolicy')
                          ->will($this->returnValue(GuardInterface::POLICY_DENY));
        $moduleOptionsMock->expects($this->once())
                          ->method('getGuards')
                          ->will($this->returnValue($dataGuards));

        $rbacImplementation = new Rbac();
        $roleGuest          = new Role('guest');
        $roleUser           = new Role('user');

        $roleGuest->addPermission('read');
        $roleUser->addPermission('comment');

        $rbacImplementation->addRole($roleGuest);
        $rbacImplementation->addRole($roleUser, $roleGuest);

        $authServiceMock = $this->getMockBuilder('ZfcRbac\Service\AuthorizationService')
                                ->disableOriginalConstructor()
                                ->getMock();
        $authServiceMock->expects($this->once())
                        ->method('getRbac')
                        ->will($this->returnValue($rbacImplementation));

        $authIdentityMock = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $authIdentityMock->expects($this->once())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue($dataIdentityRoles));

        //region Mock ServiceLocator
        $slMockMap          = array(
            array('ZfcRbac\Service\AuthorizationService', $authServiceMock),
            array('ZfcRbac\Options\ModuleOptions', $moduleOptionsMock),
            array('ZfcRbac\Identity\AuthenticationIdentityProvider', $authIdentityMock)
        );

        $serviceLocatorMock = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocatorMock->expects($this->any())
                           ->method('get')
                           ->will($this->returnValueMap($slMockMap));
        //endregion

        $applicationMock = $this->getMock('Zend\Mvc\ApplicationInterface');

        $applicationMock->expects($this->once())
                        ->method('getServiceManager')
                        ->will($this->returnValue($serviceLocatorMock));

        $mvcEventMock = $this->getMock('Zend\Mvc\MvcEvent');

        $mvcEventMock->expects($this->once())
                     ->method('getApplication')
                     ->will($this->returnValue($applicationMock));

        $collector->collect($mvcEventMock);
        $collector->unserialize($collector->serialize());

        $theCollection = $collector->getCollection();

        $this->assertArrayHasKey('guards', $theCollection);
        $this->assertArrayHasKey('roles', $theCollection);
        $this->assertArrayHasKey('permissions', $theCollection);
        $this->assertArrayHasKey('options', $theCollection);

        $guards      = $theCollection['guards'];
        $roles       = $theCollection['roles'];
        $permissions = $theCollection['permissions'];
        $options     = $theCollection['options'];

        // Guard Assertions
        $this->assertArrayHasKey('ZfcRbac\Guard\RouteGuard', $guards);

        // Roles Assertions
        $this->assertArrayHasKey('user', $roles);
        $this->assertSame('guest', $roles['user']);

        // Permission Assertions
        $this->assertArrayHasKey('read', $permissions);
        $this->assertArrayHasKey('comment', $permissions);

        // Option Assertions
        $this->assertArrayHasKey('current_roles', $options);
        $this->assertArrayHasKey('guest_role', $options);
        $this->assertArrayHasKey('protection_policy', $options);
    }
}
