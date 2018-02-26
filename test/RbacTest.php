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

namespace ZfcRbacTest;

use PHPUnit\Framework\TestCase;
use ZfcRbac\Rbac;
use ZfcRbac\Role\HierarchicalRole;
use ZfcRbac\Role\Role;

/**
 * @covers \ZfcRbac\Rbac
 * @group  Coverage
 */
class RbacTest extends TestCase
{
    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testCanConvertSingleRole(): void
    {
        $role = new Role('Foo');
        $role->addPermission('permission');

        $rbac = new Rbac();

        $this->assertTrue($rbac->isGranted($role, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testCanUseEmptyArray(): void
    {
        $rbac = new Rbac();
        $this->assertFalse($rbac->isGranted([], 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testCanCheckMultipleRolesWithMatchingPermission(): void
    {
        $role1 = new Role('Foo');

        $role2 = new Role('Bar');
        $role2->addPermission('permission');

        $roles = [$role1, $role2];
        $rbac = new Rbac();

        $this->assertTrue($rbac->isGranted($roles, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testReturnFalseIfNoRoleHasPermission(): void
    {
        $role1 = new Role('Foo');
        $role2 = new Role('Bar');

        $roles = [$role1, $role2];
        $rbac = new \ZfcRbac\Rbac();

        $this->assertFalse($rbac->isGranted($roles, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testCanCheckHierarchicalRole(): void
    {
        $childRole = new Role('Bar');
        $childRole->addPermission('permission');

        $parentRole = new HierarchicalRole('Foo');
        $parentRole->addChild($childRole);

        $rbac = new \ZfcRbac\Rbac();

        $this->assertTrue($rbac->isGranted($parentRole, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testReturnFalseIfNoHierarchicalRoleHasPermission(): void
    {
        $childRole = new Role('Bar');

        $parentRole = new HierarchicalRole('Foo');
        $parentRole->addChild($childRole);

        $rbac = new Rbac();

        $this->assertFalse($rbac->isGranted($parentRole, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac::isGranted
     */
    public function testCanCheckTraversableAsRolesList(): void
    {
        $role1 = new Role('Foo');

        $role2 = new Role('Bar');
        $role2->addPermission('permission');

        $roles = new \ArrayIterator([$role1, $role2]);
        $rbac = new Rbac();

        $this->assertTrue($rbac->isGranted($roles, 'permission'));
    }
}
