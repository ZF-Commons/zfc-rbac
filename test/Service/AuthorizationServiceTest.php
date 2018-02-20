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

declare(strict_types=1);

namespace ZfcRbacTest\Service;

use PHPUnit\Framework\TestCase;
use ZfcRbac\Assertion\AssertionPluginManager;
use ZfcRbac\Exception\InvalidArgumentException;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Rbac;
use ZfcRbac\Role\RoleInterface;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleServiceInterface;
use ZfcRbacTest\Asset\FlatRole;
use ZfcRbacTest\Asset\Identity;
use ZfcRbacTest\Asset\SimpleAssertion;

/**
 * @covers \ZfcRbac\Service\AuthorizationService
 */
class AuthorizationServiceTest extends TestCase
{
    public function grantedProvider(): array
    {
        return [
            // Simple is granted
            [
                'guest',
                'read',
                null,
                true,
            ],

            // Simple is allowed from parent
            [
                'member',
                'read',
                null,
                true,
            ],

            // Simple is refused
            [
                'guest',
                'write',
                null,
                false,
            ],

            // Simple is refused from parent
            [
                'guest',
                'delete',
                null,
                false,
            ],

            // Simple is refused from assertion map
            [
                'admin',
                'delete',
                false,
                false,
                [
                    'delete' => SimpleAssertion::class,
                ],
            ],

            // Simple is accepted from assertion map
            [
                'admin',
                'delete',
                true,
                true,
                [
                    'delete' => SimpleAssertion::class,
                ],
            ],

            // Simple is refused from no role
            [
                [],
                'read',
                null,
                false,
            ],
        ];
    }

    public function testDoNotCallAssertionIfThePermissionIsNotGranted(): void
    {
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->assertFalse($authorizationService->isGranted(null, 'foo'));
    }

    public function testReturnsFalseForIdentityWithoutRoles(): void
    {
        $identity = new Identity();

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->never())->method('isGranted');

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->assertFalse($authorizationService->isGranted($identity, 'foo'));
    }

    public function testReturnsTrueForIdentityWhenHasPermissionButNoAssertionsExists(): void
    {
        $role = new FlatRole('admin');
        $identity = new Identity([$role]);

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->once())->method('isGranted')->willReturn(true);

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->assertTrue($authorizationService->isGranted($identity, 'foo'));
    }

    public function testUsesAssertionsAsInstances(): void
    {
        $role = new FlatRole('admin');
        $identity = new Identity([$role]);
        $assertion = new SimpleAssertion();

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->once())->method('isGranted')->willReturn(true);

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
        $authorizationService->setAssertion('foo', $assertion);

        $authorizationService->isGranted($identity, 'foo');

        $this->assertTrue($assertion->gotCalled());
    }

    public function testUsesAssertionsAsStrings(): void
    {
        $role = new FlatRole('admin');
        $identity = new Identity([$role]);
        $assertion = new SimpleAssertion();

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->once())->method('isGranted')->willReturn(true);

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->once())->method('get')->with('fooFactory')->willReturn($assertion);

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
        $authorizationService->setAssertion('foo', 'fooFactory');

        $authorizationService->isGranted($identity, 'foo');

        $this->assertTrue($assertion->gotCalled());
    }

    public function testUsesAssertionsAsCallable(): void
    {
        $role = new FlatRole('admin');
        $identity = new Identity([$role]);
        $assertion = new SimpleAssertion();

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->once())->method('isGranted')->willReturn(true);

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $called = false;

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
        $authorizationService->setAssertion(
            'foo',
            function ($permission, IdentityInterface $identity = null, $context = null) use (&$called) {
                $called = true;

                return false;
            }
        );

        $authorizationService->isGranted($identity, 'foo');

        $this->assertTrue($called);
    }

    public function testAssertionsAsStringsAreCached(): void
    {
        $role = new FlatRole('admin');
        $identity = new Identity([$role]);
        $assertion = new SimpleAssertion();

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->any())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->any())->method('isGranted')->willReturn(true);

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->once())->method('get')->with('fooFactory')->willReturn($assertion);

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
        $authorizationService->setAssertion('foo', 'fooFactory');

        $authorizationService->isGranted($identity, 'foo');
        $authorizationService->isGranted($identity, 'foo');

        $this->assertTrue($assertion->gotCalled());
        $this->assertSame(2, $assertion->calledTimes());
    }

    public function testUsesAssertionsAsArrays(): void
    {
        $role = new FlatRole('admin');
        $identity = new Identity([$role]);

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue($identity->getRoles()));

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $rbac->expects($this->once())->method('isGranted')->willReturn(true);

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager->expects($this->never())->method('get');

        $called1 = false;
        $called2 = false;

        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);
        $authorizationService->setAssertion(
            'foo',
            [
                function ($permission, IdentityInterface $identity = null, $context = null) use ($authorizationService, &$called1) {
                    $called1 = true;

                    return true;
                },
                function ($permission, IdentityInterface $identity = null, $context = null) use ($authorizationService, &$called2) {
                    $called2 = true;

                    return false;
                },
            ]
        );

        $this->assertFalse($authorizationService->isGranted($identity, 'foo'));

        $this->assertTrue($called1);
        $this->assertTrue($called2);
    }

    public function testThrowExceptionForInvalidAssertion(): void
    {
        $role = $this->getMockBuilder(RoleInterface::class)->getMock();
        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();

        $rbac->expects($this->once())->method('isGranted')->will($this->returnValue(true));

        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $roleService->expects($this->once())->method('getIdentityRoles')->will($this->returnValue([$role]));

        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $this->expectException(InvalidArgumentException::class);

        $authorizationService->setAssertion('foo', new \stdClass());
        $authorizationService->isGranted(null, 'foo');
    }

    public function testAssertionMap(): void
    {
        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $authorizationService->setAssertions(['foo' => 'bar', 'bar' => 'foo']);

        $this->assertTrue($authorizationService->hasAssertion('foo'));
        $this->assertTrue($authorizationService->hasAssertion('bar'));

        $authorizationService->setAssertion('bar', null);

        $this->assertFalse($authorizationService->hasAssertion('bar'));
    }

    public function testContextIsPassedToRoleService(): void
    {
        $identity = new Identity([]);
        $context = 'context';

        $rbac = $this->getMockBuilder(Rbac::class)->disableOriginalConstructor()->getMock();
        $roleService = $this->getMockBuilder(RoleServiceInterface::class)->disableOriginalConstructor()->getMock();
        $assertionPluginManager = $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock();
        $authorizationService = new AuthorizationService($rbac, $roleService, $assertionPluginManager);

        $roleService->expects($this->once())->method('getIdentityRoles')->with($identity, $context)->willReturn([]);
        $authorizationService->isGranted($identity, 'foo', $context);
    }
}
