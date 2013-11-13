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

namespace ZfcRbacTest\Factory;

use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Permission\PermissionProviderPluginManager;

/**
 * @covers \ZfcRbac\Factory\ObjectRepositoryPermissionProviderFactory
 */
class ObjectRepositoryPermissionProviderFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryUsingObjectRepository()
    {
        $pluginManager  = new PermissionProviderPluginManager();
        $serviceManager = new ServiceManager();

        $pluginManager->setServiceLocator($serviceManager);

        $options = [
            'object_repository' => 'PermissionObjectRepository'
        ];

        $serviceManager->setService(
            'PermissionObjectRepository',
            $this->getMock('Doctrine\Common\Persistence\ObjectRepository')
        );

        $permissionProvider = $pluginManager->get('ZfcRbac\Permission\ObjectRepositoryPermissionProvider', $options);
        $this->assertInstanceOf('ZfcRbac\Permission\ObjectRepositoryPermissionProvider', $permissionProvider);
    }

    public function testFactoryUsingObjectManager()
    {
        $pluginManager  = new PermissionProviderPluginManager();
        $serviceManager = new ServiceManager();

        $pluginManager->setServiceLocator($serviceManager);

        $options = [
            'object_manager' => 'ObjectManager',
            'class_name'     => 'Permission'
        ];

        $objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $objectManager->expects($this->once())
                      ->method('getRepository')
                      ->with($options['class_name'])
                      ->will($this->returnValue($this->getMock('Doctrine\Common\Persistence\ObjectRepository')));

        $serviceManager->setService('ObjectManager', $objectManager);

        $permissionProvider = $pluginManager->get('ZfcRbac\Permission\ObjectRepositoryPermissionProvider', $options);
        $this->assertInstanceOf('ZfcRbac\Permission\ObjectRepositoryPermissionProvider', $permissionProvider);
    }

    /**
     * This test covers that the proper exception is found in the exception chain when no proper configuration is
     * provided for the requested service.
     *
     * This is required due to the fact that the ServiceManager catches ALL exceptions and throws it's own...
     */
    public function testThrowExceptionIfNoObjectManagerNorObjectRepositoryIsSet()
    {
        try {
            $pluginManager  = new PermissionProviderPluginManager();
            $serviceManager = new ServiceManager();

            $pluginManager->setServiceLocator($serviceManager);
            $pluginManager->get('ZfcRbac\Permission\ObjectRepositoryPermissionProvider', []);
        } catch(\Zend\ServiceManager\Exception\ServiceNotCreatedException $smException) {
            while ($e = $smException->getPrevious()) {
                if ($e instanceof \ZfcRbac\Exception\RuntimeException) {
                    return true;
                }
            }
        }

        $this->fail(
             'ZfcRbac\Factory\ObjectRepositoryPermissionProviderFactory::createService() :: '
             .'ZfcRbac\Exception\RuntimeException was not found in the previous Exceptions'
        );
    }
}
