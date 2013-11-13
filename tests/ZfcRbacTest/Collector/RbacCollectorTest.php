<?php
namespace ZfcRbacTest\Collector;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Rbac\RoleInterface;
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
        $collector = new RbacCollector();
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
        $collector = new RbacCollector();
        $unserialized = array(
            'guards'      => array('foo' => 'bar'),
            'roles'       => array('foo' => 'bar'),
            'permissions' => array('foo' => 'bar'),
            'options'     => array('foo' => 'bar')
        );
        $serialized = serialize($unserialized);

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
        $collector = new RbacCollector();
        $dataIdentityRoles = array('guest', 'user');
        $dataGuards = array(
            'ZfcRbac\Guard\RouteGuard' => array(
                'admin/*' => array('admin'),
                'login'   => array('guest')
            )
        );

        //region Mock ZfcRbac\Options\ModuleOptions
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
        //endregion

        //region Mock AuthorizationService && Rbac Mock
        $dumpIteratorMock = $this->getMock('RecursiveIterator');
        $dumpIteratorMock->expects($this->any())->method('hasChildren')->will($this->returnValue(false));

        $roleOneMock = $this->getMockBuilder('Zend\Permissions\Rbac\Role')->disableOriginalConstructor()->getMock();
        $roleOneMock->expects($this->any())->method('getParent')->will($this->returnValue(null));
        $roleOneMock->expects($this->any())->method('getName')->will($this->returnValue('user'));
        $roleOneMock->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $roleOneMock->expects($this->any())->method('getChildren')->will($this->returnValue($dumpIteratorMock));

        $roleTwoMock = $this->getMockBuilder('Zend\Permissions\Rbac\Role')->disableOriginalConstructor()->getMock();
        $roleTwoMock->expects($this->any())->method('getParent')->will($this->returnValue($roleOneMock));
        $roleTwoMock->expects($this->any())->method('getName')->will($this->returnValue('guest'));
        $roleTwoMock->expects($this->any())->method('hasChildren')->will($this->returnValue(false));
        $roleTwoMock->expects($this->any())->method('getChildren')->will($this->returnValue($dumpIteratorMock));

        // @todo Fix RbacMock to be a valid Iterateable Mock Object
        $rbacMock = $this->getMock('Zend\Permissions\Rbac\Rbac');

        $rbacMock->expects($this->at(0))->method('rewind');
        $rbacMock->expects($this->at(1))->method('valid')->will($this->returnValue(true));
        $rbacMock->expects($this->at(2))->method('hasChildren')->will($this->returnValue(false));
        $rbacMock->expects($this->at(3))->method('valid')->will($this->returnValue(true));
        $rbacMock->expects($this->at(4))->method('current')->will($this->returnValue($roleOneMock));
        $rbacMock->expects($this->at(5))->method('getChildren')->will($this->returnValue($roleOneMock));
        $rbacMock->expects($this->at(6))->method('rewind');
        $rbacMock->expects($this->at(7))->method('valid')->will($this->returnValue(false));
        $rbacMock->expects($this->at(8))->method('key')->will($this->returnValue(true));
//        $rbacMock->expects($this->at(8))->method('current')->will($this->returnValue($roleTwoMock));
//        $rbacMock->expects($this->at(6))->method('next');
//        $rbacMock->expects($this->at(7))->method('valid')->will($this->returnValue(false));


        $authServiceMock = $this->getMockBuilder('ZfcRbac\Service\AuthorizationService')
            ->disableOriginalConstructor()
            ->getMock();

        $authServiceMock->expects($this->once())
            ->method('getRbac')
            ->will($this->returnValue($rbacMock));
        //endregion

        $authIdentityMock = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $authIdentityMock->expects($this->once())
            ->method('getIdentityRoles')
            ->will($this->returnValue($dataIdentityRoles));

        //region Mock ServiceLocator
        $slMockMap = array(
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

        //@todo some assertions would be nice :)
    }
}
