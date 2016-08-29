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

namespace ZfcRbacTest\Service;

use Rbac\Rbac;
use Rbac\Role\RoleInterface;
use Rbac\Traversal\Strategy\RecursiveRoleIteratorStrategy;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Identity\IdentityProviderInterface;
use ZfcRbac\Role\InMemoryRoleProvider;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleService;
use ZfcRbacTest\Asset\SimpleAssertion;
use ZfcRbac\Assertion\AssertionPluginManager;
use Zend\ServiceManager\Config;

/**
 * @covers \ZfcRbac\Service\AuthorizationService
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function grantedProvider()
    {
        return [
            // Simple is granted
            [
                'guest',
                'read',
                null,
                true
            ],

            // Simple is allowed from parent
            [
                'member',
                'read',
                null,
                true
            ],

            // Simple is refused
            [
                'guest',
                'write',
                null,
                false
            ],

            // Simple is refused from parent
            [
                'guest',
                'delete',
                null,
                false
            ],

            // Simple is refused from assertion map
            [
                'admin',
                'delete',
                false,
                false,
                [
                    'delete' => SimpleAssertion::class
                ]
            ],

            // Simple is accepted from assertion map
            [
                'admin',
                'delete',
                true,
                true,
                [
                    'delete' => SimpleAssertion::class
                ]
            ],

            // Simple is refused from no role
            [
                [],
                'read',
                null,
                false
            ],
        ];
    }

    /**
     * @dataProvider grantedProvider
     */
//    public function testGranted($role, $permission, $context, $isGranted, $assertions = array())
//    {
//        $this->markTestSkipped(
//            'Seems Rbac\Traversal\Strategy\TraversalStrategyInterface has been removed.'
//        );
//
//        $roleConfig = [
//            'admin'  => [
//                'children'    => ['member'],
//                'permissions' => ['delete']
//            ],
//            'member' => [
//                'children'    => ['guest'],
//                'permissions' => ['write']
//            ],
//            'guest'  => [
//                'permissions' => ['read']
//            ]
//        ];
//
//        $assertionPluginConfig = [
//            'invokables' => [
//                SimpleAssertion::class => SimpleAssertion::class
//            ]
//        ];
//
//        $identity = $this->getMockBuilder(IdentityInterface::class)->getMock();
//        $identity->expects($this->once())->method('getRoles')->will($this->returnValue((array) $role));
//
//        $identityProvider = $this->getMockBuilder(IdentityProviderInterface::class)->getMock();
//        $identityProvider->expects($this->any())
//            ->method('getIdentity')
//            ->will($this->returnValue($identity));
//
//        $rbac                   = new Rbac(new RecursiveRoleIteratorStrategy());
//        $roleService            = new RoleService(
//            $identityProvider,
//            new InMemoryRoleProvider($roleConfig),
//            $rbac->getTraversalStrategy()
//        );
//        $assertionPluginManager = new AssertionPluginManager(new Config($assertionPluginConfig));
//        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
//
//        $authorizationService->setAssertions($assertions);
//
//        $this->assertEquals($isGranted, $authorizationService->isGranted($permission, $context));
//    }

    public function testDoNotCallAssertionIfThePermissionIsNotGranted()
    {
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();

        $roleService = $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->assertFalse($authorizationService->isGranted(null, 'foo'));
    }

    public function testThrowExceptionForInvalidAssertion()
    {
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();

        $rbac->expects($this->once())->method('isGranted')->will($this->returnValue(true));

        $roleService = $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->setExpectedException(\ZfcRbac\Exception\InvalidArgumentException::class);

        $authorizationService->setAssertion('foo', new \stdClass());
        $authorizationService->isGranted(null, 'foo');
    }

    public function testDynamicAssertions()
    {
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();

        $rbac->expects($this->exactly(2))->method('isGranted')->will($this->returnValue(true));

        $roleService = $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->exactly(2))->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        // Using a callable
        $called = false;

        $authorizationService->setAssertion(
            'foo',
            function (AuthorizationService $injectedService) use ($authorizationService, &$called) {
                $this->assertSame($injectedService, $authorizationService);

                $called = true;

                return false;
            }
        );

        $this->assertFalse($authorizationService->isGranted(null, 'foo'));
        $this->assertTrue($called);

        // Using an assertion object
        $assertion = new SimpleAssertion();
        $authorizationService->setAssertion('foo', $assertion);

        $this->assertFalse($authorizationService->isGranted(null, 'foo', false));
        $this->assertTrue($assertion->getCalled());
    }

    public function testAssertionMap()
    {
        $rbac                   = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $roleService            = $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $authorizationService->setAssertions(['foo' => 'bar', 'bar' => 'foo']);

        $this->assertTrue($authorizationService->hasAssertion('foo'));
        $this->assertTrue($authorizationService->hasAssertion('bar'));

        $authorizationService->setAssertion('bar', null);

        $this->assertFalse($authorizationService->hasAssertion('bar'));
    }

}
