<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfcRbacTest\Collector;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use ZfcRbac\Collector\RbacCollector;
use ZfcRbac\Guard\GuardInterface;
use ZfcRbac\Options\ModuleOptions;

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

        $this->assertSame([], $unserialized['guards']);
        $this->assertSame([], $unserialized['roles']);
        $this->assertSame([], $unserialized['permissions']);
        $this->assertSame([], $unserialized['options']);
    }

    public function testUnserialize()
    {
        $collector    = new RbacCollector();
        $unserialized = [
            'guards'      => ['foo' => 'bar'],
            'roles'       => ['foo' => 'bar'],
            'permissions' => ['foo' => 'bar'],
            'options'     => ['foo' => 'bar']
        ];
        $serialized   = serialize($unserialized);

        $collector->unserialize($serialized);

        $collection = $collector->getCollection();

        $this->assertInternalType('array', $collection);
        $this->assertSame(['foo' => 'bar'], $collection['guards']);
        $this->assertSame(['foo' => 'bar'], $collection['roles']);
        $this->assertSame(['foo' => 'bar'], $collection['permissions']);
        $this->assertSame(['foo' => 'bar'], $collection['options']);
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
        $dataIdentityRoles = ['guest', 'user'];
        $dataGuards        = [
            'ZfcRbac\Guard\RouteGuard' => [
                'admin/*' => ['admin'],
                'login'   => ['guest']
            ]
        ];

        $moduleOptions = new ModuleOptions([
            'identity_provider' => 'ZfcRbac\Identity\AuthenticationIdentityProvider',
            'guest_role'        => 'guest',
            'protection_policy' => GuardInterface::POLICY_DENY,
            'guards'            => $dataGuards
        ]);

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

        $authServiceMock->expects($this->once())
                        ->method('getIdentityRoles')
                        ->will($this->returnValue($dataIdentityRoles));

        $authIdentityMock = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');

        //region Mock ServiceLocator
        $slMockMap          = [
            ['ZfcRbac\Service\AuthorizationService', $authServiceMock],
            ['ZfcRbac\Options\ModuleOptions', $moduleOptions],
            ['ZfcRbac\Identity\AuthenticationIdentityProvider', $authIdentityMock]
        ];

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
