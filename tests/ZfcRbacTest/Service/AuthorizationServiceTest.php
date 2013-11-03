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
use ZfcRbac\Service\AuthorizationService;

/**
 * @covers \ZfcRbac\Service\AuthorizationService
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    public function grantedProvider()
    {
        return array(
            // Simple is granted
            array(
                'guest',
                'read',
                null,
                true
            ),

            // Simple is allowed from parent
            array(
                'member',
                'read',
                null,
                true
            ),

            // Simple is refused
            array(
                'guest',
                'write',
                null,
                false
            ),

            // Simple is refused from parent
            array(
                'guest',
                'delete',
                null,
                false
            ),

            // Simple is refused from dynamic assertion
            array(
                'member',
                'read',
                function() { return false; },
                false
            ),

            // Simple is refused from no role
            array(
                array(),
                'read',
                null,
                false
            ),
        );
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

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->once())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue($role));


        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $this->assertEquals($isGranted, $authorizationService->isGranted($permission, $assertion));
    }

    /**
     * This test ensures that if an identity has multiple role, all of them must be granted
     */
    public function testNeedAllRoleToBeGranted()
    {
        $rbac = new Rbac();

        $rbac->addRole('reader');
        $rbac->addRole('writer');

        $rbac->getRole('reader')->addPermission('read');

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->once())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue(array('reader', 'writer')));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $this->assertFalse($authorizationService->isGranted('read'));
    }
}