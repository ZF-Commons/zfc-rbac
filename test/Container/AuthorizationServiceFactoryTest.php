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
use ZfcRbac\Assertion\AssertionContainerInterface;
use ZfcRbac\Container\AuthorizationServiceFactory;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Rbac;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleServiceInterface;

/**
 * @covers \ZfcRbac\Container\AuthorizationServiceFactory
 */
class AuthorizationServiceFactoryTest extends TestCase
{
    public function testCanCreateAuthorizationService(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(ModuleOptions::class)->willReturn(new ModuleOptions([]));
        $container->get(RoleServiceInterface::class)->willReturn($this->createMock(RoleServiceInterface::class));
        $container->get(AssertionContainerInterface::class)->willReturn($this->createMock(AssertionContainerInterface::class));
        $container->get(Rbac::class)->willReturn(new Rbac());

        $factory = new AuthorizationServiceFactory();
        $authorizationService = $factory($container->reveal());

        $this->assertInstanceOf(AuthorizationService::class, $authorizationService);
    }
}
