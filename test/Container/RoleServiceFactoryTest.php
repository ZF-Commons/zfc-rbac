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

namespace ZfcRbacTest\Container;

use PHPUnit\Framework\TestCase;
use Rbac\Rbac;
use Rbac\Traversal\Strategy\TraversalStrategyInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Container\RoleServiceFactory;
use ZfcRbac\Exception\RuntimeException;
use ZfcRbac\Identity\AuthenticationIdentityProvider;
use ZfcRbac\Identity\IdentityProviderInterface;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Role\InMemoryRoleProvider;
use ZfcRbac\Role\RoleProviderPluginManager;
use ZfcRbac\Service\RoleService;

/**
 * @covers \ZfcRbac\Container\RoleServiceFactory
 */
class RoleServiceFactoryTest extends TestCase
{
    /**
     * @markTestSkipped skipped
     */
    public function testFactory()
    {
        $options = new ModuleOptions(
            [
                'identity_provider' => AuthenticationIdentityProvider::class,
                'guest_role'        => 'guest',
                'role_provider'     => [
                    InMemoryRoleProvider::class => [
                        'foo'
                    ]
                ]
            ]
        );

        $serviceManager = new ServiceManager();
        $serviceManager->setService(ModuleOptions::class, $options);
        $serviceManager->setService(RoleProviderPluginManager::class, new RoleProviderPluginManager($serviceManager));
        $serviceManager->setService(
            AuthenticationIdentityProvider::class,
            $this->createMock(IdentityProviderInterface::class)
        );

        $factory     = new RoleServiceFactory();
        /** @var RoleService $roleService */
        $roleService = $factory($serviceManager, 'requestedName');

        $this->assertInstanceOf(RoleService::class, $roleService);
        $this->assertEquals('guest', $roleService->getGuestRole());
    }

    public function testThrowExceptionIfNoRoleProvider()
    {
        $this->expectException(RuntimeException::class);

        $options = new ModuleOptions(
            [
                'identity_provider' => AuthenticationIdentityProvider::class,
                'guest_role'        => 'guest',
                'role_provider'     => []
            ]
        );

        $serviceManager = new ServiceManager();
        $serviceManager->setService(ModuleOptions::class, $options);
        $serviceManager->setService(
            AuthenticationIdentityProvider::class,
            $this->createMock(IdentityProviderInterface::class)
        );

        $factory     = new RoleServiceFactory();
        $roleService = $factory($serviceManager, 'requestedName');
    }
}
