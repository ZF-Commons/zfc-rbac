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

use Zend\Permissions\Rbac\Rbac;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RbacEvent;
use ZfcRbacTest\Asset\SimpleAssertion;

/**
 * @covers \ZfcRbac\Service\AuthorizationService
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicGetterAndSetters()
    {
        // Let's fill the RBAC container with some values
        $rbac = new Rbac();

        $identityProvider     = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $this->assertSame($rbac, $authorizationService->getRbac());
    }

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

            // Simple is refused from dynamic assertion
            [
                'member',
                'read',
                function() { return false; },
                false
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
    public function testGranted($role, $permission, $assertion = null, $isGranted)
    {
        // Let's fill the RBAC container with some values
        $rbac = new Rbac();

        $rbac->addRole('admin');
        $rbac->addRole('member', 'admin');
        $rbac->addRole('guest', 'member');

        $rbac->getRole('guest')->addPermission('read');
        $rbac->getRole('member')->addPermission('write');
        $rbac->getRole('admin')->addPermission('delete');

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->once())->method('getRoles')->will($this->returnValue($role));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $this->assertEquals($isGranted, $authorizationService->isGranted($permission, $assertion));
    }

    /**
     * Assert that event to load roles and permissions is not triggered if no role can be found in an
     * identity, because it will be refused anyway
     */
    public function testDoesNotLoadIfNoIdentityIsFound()
    {
        $rbac = new Rbac();

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->once())->method('getRoles')->will($this->returnValue([]));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->once())
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $authorizationService->setEventManager($eventManager);

        $eventManager->expects($this->never())
                     ->method('trigger');

        $authorizationService->isGranted('foo');
    }

    public function testLoadRoles()
    {
        $rbac = new Rbac();

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->exactly(2))->method('getRoles')->will($this->returnValue(['role1']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->exactly(2))
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $authorizationService->setEventManager($eventManager);

        $eventManager->expects($this->exactly(1))
                     ->method('trigger')
                     ->with(RbacEvent::EVENT_LOAD_ROLES);

        // Call twice to assert initialization is not done twice
        $authorizationService->isGranted('foo');
        $authorizationService->isGranted('foo');
    }

    public function testLoadRolesMultipleTimesIfForceReload()
    {
        $rbac = new Rbac();

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->exactly(2))->method('getRoles')->will($this->returnValue(['role1']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->exactly(2))
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);
        $authorizationService->setForceReload(true);

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $authorizationService->setEventManager($eventManager);

        $eventManager->expects($this->exactly(2))
                     ->method('trigger')
                     ->with(RbacEvent::EVENT_LOAD_ROLES);

        $authorizationService->isGranted('foo');
        $authorizationService->isGranted('foo');
    }

    public function testThrowExceptionForInvalidAssertion()
    {
        $rbac = new Rbac();

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->once())->method('getRoles')->will($this->returnValue(['role1']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $this->setExpectedException('ZfcRbac\Exception\InvalidArgumentException');

        $authorizationService->isGranted('foo', new \stdClass());
    }

    public function testDynamicAssertions()
    {
        $rbac = new Rbac();

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->any())->method('getRoles')->will($this->returnValue(['role1']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        // Using a callable
        $called = false;
        $this->assertFalse($authorizationService->isGranted('foo',
                function(IdentityInterface $expectedIdentity = null) use($identity, &$called) {
                    $this->assertSame($expectedIdentity, $identity);
                    $called = true;

                    return false;
                })
        );
        $this->assertTrue($called);

        // Using an assertion object
        $assertion = new SimpleAssertion();
        $this->assertFalse($authorizationService->isGranted('foo', $assertion));
        $this->assertTrue($assertion->getCalled());
    }

    public function testReturnGuestRoleIfNoIdentityIsFound()
    {
        $rbac = new Rbac();

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue(null));

        $authorizationService = new AuthorizationService($rbac, $identityProvider, 'guest');

        $result = $authorizationService->getIdentityRoles();

        $this->assertInternalType('array', $result);
        $this->assertEquals(['guest'], $result);
    }

    public function testThrowExceptionIfIdentityIsWrongType()
    {
        $this->setExpectedException(
            'ZfcRbac\Exception\RuntimeException',
            'ZfcRbac expects your identity to implement ZfcRbac\Identity\IdentityInterface, "stdClass" given'
        );

        $rbac = new Rbac();

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue(new \stdClass()));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $result = $authorizationService->getIdentityRoles();

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
}
