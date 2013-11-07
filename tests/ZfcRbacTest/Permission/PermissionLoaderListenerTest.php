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

use Zend\Cache\Storage\Adapter\Memory;
use Zend\Permissions\Rbac\Rbac;
use ZfcRbac\Permission\PermissionLoaderListener;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RbacEvent;

/**
 * @covers \ZfcRbac\Permission\PermissionLoaderListener
 */
class PermissionLoaderListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testAddPermissionsToRbacContainer()
    {
        $rbac      = new Rbac();
        $rbacEvent = new RbacEvent($rbac);

        $identityProvider     = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $rbacEvent->setTarget($authorizationService);

        // Add some roles
        $rbac->addRole('admin');
        $rbac->addRole('member', 'admin');
        $rbac->addRole('guest');

        $permissionProvider = $this->getMock('ZfcRbac\Permission\PermissionProviderInterface');
        $permissionProvider->expects($this->once())
                           ->method('getPermissions')
                           ->will($this->returnValue(array(
                                'read'   => array('member', 'guest'),
                                'write'  => 'member',
                                'edit'   => array('roles' => 'member'),
                                'delete' => 'admin',
                           )));

        $permissionLoaderListener = new PermissionLoaderListener($permissionProvider);

        $permissionLoaderListener->onLoadPermissions($rbacEvent);

        $role = $rbac->getRole('member');
        $this->assertTrue($role->hasPermission('read'));
        $this->assertTrue($role->hasPermission('write'));
        $this->assertTrue($role->hasPermission('edit'));
        $this->assertFalse($role->hasPermission('delete'));

        $role = $rbac->getRole('admin');
        $this->assertTrue($role->hasPermission('read'));
        $this->assertTrue($role->hasPermission('write'));
        $this->assertTrue($role->hasPermission('edit'));
        $this->assertTrue($role->hasPermission('delete'));

        $role = $rbac->getRole('guest');
        $this->assertTrue($role->hasPermission('read'));
        $this->assertFalse($role->hasPermission('write'));
        $this->assertFalse($role->hasPermission('edit'));
        $this->assertFalse($role->hasPermission('delete'));
    }

    public function testCanLoadPermissionsFromCache()
    {
        $permissionProvider = $this->getMock('ZfcRbac\Permission\PermissionProviderInterface');
        $permissionProvider->expects($this->never())
                           ->method('getPermissions');

        $rbac = new Rbac();
        $rbac->addRole('role1');

        $rbacEvent = new RbacEvent($rbac);

        $cacheStorage             = new Memory();
        $permissionLoaderListener = new PermissionLoaderListener($permissionProvider, $cacheStorage);

        $cacheStorage->setItem($permissionLoaderListener->getCacheKey(), array('permission1' => 'role1'));

        $permissionLoaderListener->onLoadPermissions($rbacEvent);
    }
}
 