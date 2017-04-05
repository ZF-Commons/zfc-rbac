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

namespace ZfcRbacTest\Rbac;

use PHPUnit\Framework\TestCase;
use ZfcRbac\Rbac\Exception\RuntimeException;
use ZfcRbac\Rbac\Rbac;
use ZfcRbac\Rbac\Role\HierarchicalRole;
use ZfcRbac\Rbac\Role\Role;

/**
 * @covers \ZfcRbac\Rbac\Rbac
 * @group  Coverage
 */
class RbacTest extends TestCase
{
    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testEnforcePermissionAsString()
    {
        $this->expectException(RuntimeException::class);

        $rbac = new Rbac();
        $rbac->isGranted([], new \stdClass());
    }

    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testCanConvertSingleRole()
    {
        $role = new Role('Foo');
        $role->addPermission('permission');

        $rbac = new Rbac();

        $this->assertTrue($rbac->isGranted($role, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testCanUseEmptyArray()
    {
        $rbac = new Rbac();
        $this->assertFalse($rbac->isGranted([], 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testCanCheckMultipleRolesWithMatchingPermission()
    {
        $role1 = new Role('Foo');

        $role2 = new Role('Bar');
        $role2->addPermission('permission');

        $roles = [$role1, $role2];
        $rbac  = new Rbac();

        $this->assertTrue($rbac->isGranted($roles, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testReturnFalseIfNoRoleHasPermission()
    {
        $role1 = new Role('Foo');
        $role2 = new Role('Bar');

        $roles = [$role1, $role2];
        $rbac  = new Rbac();

        $this->assertFalse($rbac->isGranted($roles, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testCanCheckHierarchicalRole()
    {
        $childRole  = new Role('Bar');
        $childRole->addPermission('permission');

        $parentRole = new HierarchicalRole('Foo');
        $parentRole->addChild($childRole);

        $rbac = new Rbac();

        $this->assertTrue($rbac->isGranted($parentRole, 'permission'));
    }

    /**
     * @covers \ZfcRbac\Rbac\Rbac::isGranted
     */
    public function testReturnFalseIfNoHierarchicalRoleHasPermission()
    {
        $childRole  = new Role('Bar');

        $parentRole = new HierarchicalRole('Foo');
        $parentRole->addChild($childRole);

        $rbac = new Rbac();

        $this->assertFalse($rbac->isGranted($parentRole, 'permission'));
    }
}
