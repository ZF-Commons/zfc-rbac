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

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Exception\RuntimeException;
use ZfcRbac\Role\ObjectRepositoryRoleProvider;
use ZfcRbac\Role\RoleProviderPluginManager;

/**
 * @covers \ZfcRbac\Container\ObjectRepositoryRoleProviderFactory
 */
class ObjectRepositoryRoleProviderFactoryTest extends TestCase
{
    public function testFactoryUsingObjectRepository()
    {
        $serviceManager = new ServiceManager();
        $pluginManager  = new RoleProviderPluginManager($serviceManager);

        $options = [
            'role_name_property' => 'name',
            'object_repository'  => 'RoleObjectRepository',
        ];

        $serviceManager->setService('RoleObjectRepository', $this->getMockBuilder(ObjectRepository::class)->getMock());

        $roleProvider = $pluginManager->get(ObjectRepositoryRoleProvider::class, $options);
        $this->assertInstanceOf(ObjectRepositoryRoleProvider::class, $roleProvider);
    }

    public function testFactoryUsingObjectManager()
    {
        $serviceManager = new ServiceManager();
        $pluginManager  = new RoleProviderPluginManager($serviceManager);

        $options = [
            'role_name_property' => 'name',
            'object_manager'     => 'ObjectManager',
            'class_name'         => 'Role',
        ];

        $objectManager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $objectManager->expects($this->once())
                      ->method('getRepository')
                      ->with($options['class_name'])
                      ->will($this->returnValue($this->getMockBuilder(ObjectRepository::class)->getMock()));

        $serviceManager->setService('ObjectManager', $objectManager);

        $roleProvider = $pluginManager->get(ObjectRepositoryRoleProvider::class, $options);
        $this->assertInstanceOf(ObjectRepositoryRoleProvider::class, $roleProvider);
    }

    /**
     * This is required due to the fact that the ServiceManager catches ALL exceptions and throws it's own...
     */
    public function testThrowExceptionIfNoRoleNamePropertyIsSet()
    {
        try {
            $serviceManager = new ServiceManager();
            $pluginManager  = new RoleProviderPluginManager($serviceManager);

            $pluginManager->get(ObjectRepositoryRoleProvider::class, []);
        } catch (ServiceNotCreatedException $e) {
            while ($e = $e->getPrevious()) {
                if ($e instanceof RuntimeException) {
                    $this->assertTrue(true); // we got here
                    return true;
                }
            }
        }

        $this->fail(
            'ZfcRbac\Factory\ObjectRepositoryRoleProviderFactory::createService() :: '
            .'ZfcRbac\Exception\RuntimeException was not found in the previous Exceptions'
        );
    }

    /**
     * This is required due to the fact that the ServiceManager catches ALL exceptions and throws it's own...
     */
    public function testThrowExceptionIfNoObjectManagerNorObjectRepositoryIsSet()
    {
        try {
            $serviceManager = new ServiceManager();
            $pluginManager  = new RoleProviderPluginManager($serviceManager);

            $pluginManager->get(ObjectRepositoryRoleProvider::class, [
                'role_name_property' => 'name',
            ]);
        } catch (ServiceNotCreatedException $e) {
            while ($e = $e->getPrevious()) {
                if ($e instanceof RuntimeException) {
                    $this->assertTrue(true); // we got here
                    return true;
                }
            }
        }

        $this->fail(
             'ZfcRbac\Factory\ObjectRepositoryRoleProviderFactory::createService() :: '
            .'ZfcRbac\Exception\RuntimeException was not found in the previous Exceptions'
        );
    }
}
