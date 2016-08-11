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

use PHPUnit\Framework\TestCase;
use Rbac\Rbac;
use Rbac\Role\RoleInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Identity\IdentityProviderInterface;
use ZfcRbac\Role\InMemoryRoleProvider;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleService;
use ZfcRbacTest\Asset\SimpleAssertion;
use ZfcRbac\Assertion\AssertionPluginManager;

/**
 * @covers \ZfcRbac\Service\AuthorizationService
 */
class AuthorizationServiceTest extends TestCase
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
     * @param       $role
     * @param       $permission
     * @param       $context
     * @param       $isGranted
     * @param array $assertions
     */
    public function testGranted($role, $permission, $context, $isGranted, $assertions = array())
    {
        $roleConfig = [
            'admin'  => [
                'children'    => ['member'],
                'permissions' => ['delete']
            ],
            'member' => [
                'children'    => ['guest'],
                'permissions' => ['write']
            ],
            'guest'  => [
                'permissions' => ['read']
            ]
        ];

        $assertionPluginConfig = [
            'invokables' => [
                SimpleAssertion::class => SimpleAssertion::class
            ]
        ];

        $serviceManager = new ServiceManager();

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->once())->method('getRoles')->will($this->returnValue((array) $role));

        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($identity));

        $rbac                   = new Rbac();
        $roleService            = new RoleService($identityProvider, new InMemoryRoleProvider($roleConfig));
        $assertionPluginManager = new AssertionPluginManager($serviceManager, $assertionPluginConfig);
        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $authorizationService->setAssertions($assertions);

        $this->assertEquals($isGranted, $authorizationService->isGranted($permission, $context));
    }

    public function testDoNotCallAssertionIfThePermissionIsNotGranted()
    {
        $role = $this->createMock(RoleInterface::class);
        $rbac = $this->createMock(Rbac::class);

        $roleService = $this->createMock(RoleService::class);
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->createMock(AssertionPluginManager::class);
        $assertionPluginManager->expects($this->never())->method('get');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->assertFalse($authorizationService->isGranted('foo'));
    }

    public function testThrowExceptionForInvalidAssertion()
    {
        $role = $this->createMock(RoleInterface::class);
        $rbac = $this->createMock(Rbac::class);

        $rbac->expects($this->once())->method('isGranted')->will($this->returnValue(true));

        $roleService = $this->createMock(RoleService::class);
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->createMock(AssertionPluginManager::class);
        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->expectException(\ZfcRbac\Exception\InvalidArgumentException::class);

        $authorizationService->setAssertion('foo', new \stdClass());
        $authorizationService->isGranted('foo');
    }

    public function testDynamicAssertions()
    {
        $role = $this->createMock(RoleInterface::class);
        $rbac = $this->createMock(Rbac::class);

        $rbac->expects($this->exactly(2))->method('isGranted')->will($this->returnValue(true));

        $roleService = $this->createMock(RoleService::class);
        $roleService->expects($this->exactly(2))->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->createMock(AssertionPluginManager::class);
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

        $this->assertFalse($authorizationService->isGranted('foo'));
        $this->assertTrue($called);

        // Using an assertion object
        $assertion = new SimpleAssertion();
        $authorizationService->setAssertion('foo', $assertion);

        $this->assertFalse($authorizationService->isGranted('foo', false));
        $this->assertTrue($assertion->getCalled());
    }

    public function testAssertionMap()
    {
        $rbac                   = $this->createMock(Rbac::class);
        $roleService            = $this->createMock(RoleService::class);
        $assertionPluginManager = $this->createMock(AssertionPluginManager::class);
        $authorizationService   = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $authorizationService->setAssertions(['foo' => 'bar', 'bar' => 'foo']);

        $this->assertTrue($authorizationService->hasAssertion('foo'));
        $this->assertTrue($authorizationService->hasAssertion('bar'));

        $authorizationService->setAssertion('bar', null);

        $this->assertFalse($authorizationService->hasAssertion('bar'));
    }

    /**
     * @covers ZfcRbac\Service\AuthorizationService::getIdentity
     */
    public function testGetIdentity()
    {
        $rbac             = $this->createMock(Rbac::class);
        $identity         = $this->createMock(IdentityInterface::class);
        $roleService      = $this->createMock(RoleService::class);
        $assertionManager = $this->createMock(AssertionPluginManager::class);
        $authorization    = new AuthorizationService($rbac, $roleService, $assertionManager);

        $roleService->expects($this->once())->method('getIdentity')->will($this->returnValue($identity));

        $this->assertSame($authorization->getIdentity(), $identity);
    }
}
