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

use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;
use ZfcRbac\Container\InMemoryRoleProviderFactory;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Role\InMemoryRoleProvider;

/**
 * @covers \ZfcRbac\Container\InMemoryRoleProviderFactory
 */
class InMemoryRoleProviderFactoryTest extends TestCase
{
    public function testFactoryUsingObjectRepository(): void
    {
        $container = new ServiceManager();
        $container->setService(ModuleOptions::class, new ModuleOptions([
            'role_provider' => [
                InMemoryRoleProvider::class => [
                    'admin' => [
                        'children' => ['member'],
                        'permissions' => ['delete'],
                    ],
                    'member' => [
                        'children' => ['guest'],
                        'permissions' => ['write'],
                    ],
                    'guest',
                ],
            ],
        ]));

        $roleProvider = (new InMemoryRoleProviderFactory())($container);
        $this->assertInstanceOf(InMemoryRoleProvider::class, $roleProvider);
        $this->assertCount(3, $roleProvider->getRoles(['admin', 'member', 'guest']));
    }
}
