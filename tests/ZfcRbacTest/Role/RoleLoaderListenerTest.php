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

namespace ZfcRbacTest\Role;

use Zend\Permissions\Rbac\Rbac;
use ZfcRbac\Role\RoleLoaderListener;
use ZfcRbac\Service\RbacEvent;
use ZfcRbacTest\Role\Asset\SimpleRole;

/**
 * @covers \ZfcRbac\Role\RoleLoaderListener
 */
class RoleLoaderListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Provider for assert that conversion happen correctly
     */
    public function conversionProvider()
    {
        return [
            // With an RoleInterface instance
            [
                'roleConfig' => [new SimpleRole('role', 'parent')],
                'role'       => 'role',
                'parent'     => ['role' => 'parent']
            ],

            // With an array of RoleInterface instances
            [
                'roleConfig' => [
                    new SimpleRole('role1', 'parent1'),
                    new SimpleRole('role2', 'parent2')
                ],
                'role'   => ['role1', 'role2'],
                'parent' => ['role1' => 'parent1', 'role2' => 'parent2']
            ],

            // With a single string name
            [
                'roleConfig' => ['role'],
                'role'       => 'role',
                'parent'     => ['role' => null]
            ],

            // With an array of strings
            [
                'roleConfig' => ['role1', 'role2'],
                'role'       => ['role1', 'role2'],
                'parent'     => ['role1' => null, 'role2' => null]
            ],

            // With an array of string that map to a parent
            [
                'roleConfig' => ['role' => 'parent'],
                'role'       => ['role'],
                'parent'     => ['role' => 'parent']
            ],
        ];
    }

    /**
     * @dataProvider conversionProvider
     */
    public function testConversions($roleConfig, $role, $parentRole)
    {
        $roleProvider = $this->getMock('ZfcRbac\Role\RoleProviderInterface');
        $roleProvider->expects($this->once())
                     ->method('getRoles')
                     ->will($this->returnValue($roleConfig));

        $rbac      = new Rbac();
        $rbac->setCreateMissingRoles(true);
        $rbacEvent = new RbacEvent($rbac);

        $roleLoaderListener = new RoleLoaderListener($roleProvider);
        $roleLoaderListener->onLoadRoles($rbacEvent);

        $roles = (array) $role;

        foreach ($roles as $singleRole) {
            $this->assertTrue($rbac->hasRole($singleRole));

            $role = $rbac->getRole($singleRole);
            $this->assertInstanceOf('Zend\Permissions\Rbac\RoleInterface', $role);

            if (null !== $parentRole[$singleRole]) {
                $this->assertEquals($parentRole[$singleRole], $role->getParent()->getName());
            }
        }
    }

    public function testAttachToRightEvent()
    {
        $roleProvider       = $this->getMock('ZfcRbac\Role\RoleProviderInterface');
        $roleLoaderListener = new RoleLoaderListener($roleProvider);

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())
                     ->method('attach')
                     ->with(RbacEvent::EVENT_LOAD_ROLES);

        $roleLoaderListener->attach($eventManager);
    }

    public function testAddRolesToRbacContainer()
    {
        $roleProvider = $this->getMock('ZfcRbac\Role\RoleProviderInterface');
        $roleProvider->expects($this->once())
                     ->method('getRoles')
                     ->will($this->returnValue(['role1', 'role2']));

        $rbac      = new Rbac();
        $rbacEvent = new RbacEvent($rbac);

        $roleLoaderListener = new RoleLoaderListener($roleProvider);

        $roleLoaderListener->onLoadRoles($rbacEvent);

        $this->assertTrue($rbac->hasRole('role1'));
        $this->assertTrue($rbac->hasRole('role2'));
    }

    public function testCanAddParentRolesToRbacContainer()
    {
        $roleProvider = $this->getMock('ZfcRbac\Role\RoleProviderInterface');
        $roleProvider->expects($this->once())
                     ->method('getRoles')
                     ->will($this->returnValue(['role1', 'role2' => 'parent1']));

        $rbac      = new Rbac();
        $rbac->setCreateMissingRoles(true);
        $rbacEvent = new RbacEvent($rbac);

        $roleLoaderListener = new RoleLoaderListener($roleProvider);

        $roleLoaderListener->onLoadRoles($rbacEvent);

        $this->assertTrue($rbac->hasRole('role1'));
        $this->assertTrue($rbac->hasRole('role2'));

        $this->assertEquals('parent1', $rbac->getRole('role2')->getParent()->getName());
    }
}
