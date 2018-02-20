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
use Psr\Container\ContainerInterface;
use ZfcRbac\Assertion\AssertionPluginManager;
use ZfcRbac\Container\AuthorizationServiceFactory;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleServiceInterface;

/**
 * @covers \ZfcRbac\Container\AuthorizationServiceFactory
 */
class AuthorizationServiceFactoryTest extends TestCase
{
    public function testCanCreateAuthorizationService()
    {
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $container->expects($this->at(0))->method('get')->with(RoleServiceInterface::class)->willReturn($this->getMockBuilder(RoleServiceInterface::class)->getMock());
        $container->expects($this->at(1))->method('get')->with(AssertionPluginManager::class)->willReturn($this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock());
        $container->expects($this->at(2))->method('get')->with(ModuleOptions::class)->willReturn(new ModuleOptions());

        $factory = new AuthorizationServiceFactory();
        $authorizationService = $factory($container);

        $this->assertInstanceOf(AuthorizationService::class, $authorizationService);
    }
}
