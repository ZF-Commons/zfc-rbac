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
use ZfcRbac\Permission\PermissionProviderChain;
use ZfcRbac\Service\RbacEvent;

/**
 * @covers \ZfcRbac\Permission\PermissionProviderChain
 */
class PermissionProviderChainTest extends \PHPUnit_Framework_TestCase
{
    public function testCanAddPermissionsProvidersUsingConstructor()
    {
        $permissionProviderChain = new PermissionProviderChain($this->getPermissionProviders());
        $result                  = $permissionProviderChain->getPermissions(new RbacEvent(new Rbac()));

        $expected = [
            'role1' => ['edit', 'read'],
            'role2' => ['delete', 'write']
        ];

        $this->assertEquals($expected, $result);
    }

    public function testCanAddRoleProvidersUsingAdder()
    {
        $permissionProviderChain = new PermissionProviderChain();

        foreach ($this->getPermissionProviders() as $permissionProvider) {
            $permissionProviderChain->addPermissionProvider($permissionProvider);
        }

        $result = $permissionProviderChain->getPermissions(new RbacEvent(new Rbac()));

        $expected = [
            'role1' => ['edit', 'read'],
            'role2' => ['delete', 'write']
        ];

        $this->assertEquals($expected, $result);
    }

    protected function getPermissionProviders()
    {
        $permissionProvider1 = $this->getMock('ZfcRbac\Permission\PermissionProviderInterface');
        $permissionProvider1->expects($this->once())
                            ->method('getPermissions')
                            ->will($this->returnValue(['role1' => 'edit']));

        $permissionProvider2 = $this->getMock('ZfcRbac\Permission\PermissionProviderInterface');
        $permissionProvider2->expects($this->once())
                            ->method('getPermissions')
                            ->will($this->returnValue([
                                'role2' => ['delete', 'write'],
                                'role1' => ['read']
                            ]));

        return [$permissionProvider1, $permissionProvider2];
    }
}
 