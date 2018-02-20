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

namespace ZfcRbacTest\Role;

use PHPUnit\Framework\TestCase;
use ZfcRbac\Role\Role;

/**
 * @covers \ZfcRbac\Role\Role
 * @group Coverage
 */
class RoleTest extends TestCase
{
    public function testSetNameByConstructor(): void
    {
        $role = new Role('phpIsHell');
        $this->assertEquals('phpIsHell', $role->getName());
    }

    /**
     * @covers \ZfcRbac\Role\Role::addPermission
     */
    public function testRoleCanAddPermission(): void
    {
        $role = new Role('php');

        $role->addPermission('debug');
        $this->assertTrue($role->hasPermission('debug'));

        $role->addPermission('delete');

        $this->assertTrue($role->hasPermission('delete'));
    }

    /**
     * @covers \ZfcRbac\Role\Role::getPermissions
     */
    public function testRoleCanGetPermissions(): void
    {
        $role = new Role('php');

        $role->addPermission('foo');
        $role->addPermission('bar');

        $expectedPermissions = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $this->assertEquals($expectedPermissions, $role->getPermissions());
    }
}
