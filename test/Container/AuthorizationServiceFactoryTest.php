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

use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Assertion\AssertionPluginManager;
use ZfcRbac\Container\AuthorizationServiceFactory;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Service\AuthorizationService;
use ZfcRbac\Service\RoleService;
use ZfcRbac\Service\RoleServiceInterface;

/**
 * @covers \ZfcRbac\Container\AuthorizationServiceFactory
 */
class AuthorizationServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $serviceManager->setService(\Rbac\Rbac::class, $this->getMockBuilder(\Rbac\Rbac::class)->getMock());

        $serviceManager->setService(
            RoleServiceInterface::class,
            $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock()
        );
        $serviceManager->setService(
            AssertionPluginManager::class,
            $this->getMockBuilder(AssertionPluginManager::class)->disableOriginalConstructor()->getMock()
        );
        $serviceManager->setService(
            ModuleOptions::class,
            new ModuleOptions([])
        );

        $factory              = new AuthorizationServiceFactory();
        $authorizationService = $factory($serviceManager, 'requestedName');

        $this->assertInstanceOf(AuthorizationService::class, $authorizationService);
    }
}
