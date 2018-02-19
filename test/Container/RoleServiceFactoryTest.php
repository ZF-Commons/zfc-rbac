<?php

declare(strict_types=1);
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

namespace ZfcRbacTest\Container;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ZfcRbac\Container\RoleServiceFactory;
use ZfcRbac\Exception\RuntimeException;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Role\RoleProviderPluginManager;

/**
 * @covers \ZfcRbac\Container\RoleServiceFactory
 */
class RoleServiceFactoryTest extends TestCase
{
    public function testCanCreateRoleService()
    {
        $options = new ModuleOptions([
            'guest_role'           => 'guest',
            'role_provider'        => [
                \ZfcRbac\Role\InMemoryRoleProvider::class => [
                    'foo',
                ],
            ],
        ]);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))->method('get')->with(ModuleOptions::class)->willReturn($options);
        $container->expects($this->at(1))->method('get')->with(RoleProviderPluginManager::class)->willReturn(new RoleProviderPluginManager($this->getMockBuilder(ContainerInterface::class)->getMock()));

        $factory     = new RoleServiceFactory();
        $roleService = $factory($container, 'requestedName');

        $this->assertInstanceOf(\ZfcRbac\Service\RoleService::class, $roleService);
        $this->assertEquals('guest', $roleService->getGuestRole());
    }

    public function testThrowExceptionIfNoRoleProvider()
    {
        $this->expectException(RuntimeException::class);

        $options = new ModuleOptions([
            'guest_role'        => 'guest',
            'role_provider'     => [],
        ]);

        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))->method('get')->with(ModuleOptions::class)->willReturn($options);

        $factory = new RoleServiceFactory();
        $factory($container, 'requestedName');
    }
}
