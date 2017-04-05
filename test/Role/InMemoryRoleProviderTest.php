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

use PHPUnit\Framework\TestCase;
use ZfcRbac\Rbac\Role\HierarchicalRoleInterface;
use ZfcRbac\Rbac\Role\RoleInterface;
use ZfcRbac\Role\InMemoryRoleProvider;

/**
 * @covers \ZfcRbac\Role\InMemoryRoleProvider
 */
class InMemoryRoleProviderTest extends TestCase
{
    public function testInMemoryProvider()
    {
        $inMemoryProvider = new InMemoryRoleProvider([
            'admin' => [
                'children'    => ['member'],
                'permissions' => ['delete']
            ],
            'member' => [
                'children'    => ['guest'],
                'permissions' => ['write']
            ],
            'guest'
        ]);

        $roles = $inMemoryProvider->getRoles(['admin', 'member', 'guest']);

        $this->assertCount(3, $roles);

        // Test admin role
        $adminRole = $roles[0];
        $this->assertInstanceOf(HierarchicalRoleInterface::class, $adminRole);
        $this->assertEquals('admin', $adminRole->getName());
        $this->assertTrue($adminRole->hasPermission('delete'));

        // Test member role
        $memberRole = $roles[1];
        $this->assertInstanceOf(HierarchicalRoleInterface::class, $memberRole);
        $this->assertEquals('member', $memberRole->getName());
        $this->assertTrue($memberRole->hasPermission('write'));
        $this->assertFalse($memberRole->hasPermission('delete'));

        // Test guest role
        $guestRole = $roles[2];
        $this->assertInstanceOf(RoleInterface::class, $guestRole);
        $this->assertNotInstanceOf(HierarchicalRoleInterface::class, $guestRole);
        $this->assertEquals('guest', $guestRole->getName());
        $this->assertFalse($guestRole->hasPermission('write'));
        $this->assertFalse($guestRole->hasPermission('delete'));
    }
}
