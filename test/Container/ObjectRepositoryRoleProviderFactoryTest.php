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

namespace ZfcRbacTest\Container;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use ZfcRbac\Container\ObjectRepositoryRoleProviderFactory;
use ZfcRbac\Exception\RuntimeException;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Role\ObjectRepositoryRoleProvider;

/**
 * @covers \ZfcRbac\Container\ObjectRepositoryRoleProviderFactory
 */
class ObjectRepositoryRoleProviderFactoryTest extends TestCase
{
    public function testFactoryUsingObjectRepository(): void
    {
        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions([
            'role_provider' => [
                ObjectRepositoryRoleProvider::class => [
                    'role_name_property' => 'name',
                    'object_repository' => 'RoleObjectRepository',
                ],
            ],
        ]));
        $container->setService('RoleObjectRepository', $this->getMockBuilder(ObjectRepository::class)->getMock());

        $roleProvider = (new ObjectRepositoryRoleProviderFactory())($container);
        $this->assertInstanceOf(ObjectRepositoryRoleProvider::class, $roleProvider);
    }

    public function testFactoryUsingObjectManager(): void
    {
        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions([
            'role_provider' => [
                ObjectRepositoryRoleProvider::class => [
                    'role_name_property' => 'name',
                    'object_manager' => 'ObjectManager',
                    'class_name' => 'Role',
                ],
            ],
        ]));
        $objectManager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $objectManager->expects($this->once())
            ->method('getRepository')
            ->with('Role')
            ->will($this->returnValue($this->getMockBuilder(ObjectRepository::class)->getMock()));

        $container->setService('ObjectManager', $objectManager);

        $roleProvider = (new ObjectRepositoryRoleProviderFactory())($container);
        $this->assertInstanceOf(ObjectRepositoryRoleProvider::class, $roleProvider);
    }

    /**
     * This is required due to the fact that the ServiceManager catches ALL exceptions and throws it's own...
     */
    public function testThrowExceptionIfNoRoleNamePropertyIsSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The "role_name_property" option is missing');

        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions([
            'role_provider' => [
                ObjectRepositoryRoleProvider::class => [],
            ],
        ]));
        (new ObjectRepositoryRoleProviderFactory())($container);
    }

    /**
     * This is required due to the fact that the ServiceManager catches ALL exceptions and throws it's own...
     */
    public function testThrowExceptionIfNoObjectManagerNorObjectRepositoryIsSet(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No object repository was found while creating the ZfcRbac object repository role provider. Are
             you sure you specified either the "object_repository" option or "object_manager"/"class_name" options?');

        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions([
            'role_provider' => [
                ObjectRepositoryRoleProvider::class => [
                    'role_name_property' => 'name',
                ],
            ],
        ]));
        (new ObjectRepositoryRoleProviderFactory())($container);
    }
}
