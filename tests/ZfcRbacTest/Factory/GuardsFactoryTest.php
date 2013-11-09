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
use ZfcRbac\Factory\GuardsFactory;
use ZfcRbac\Guard\GuardPluginManager;
use ZfcRbac\Options\ModuleOptions;

/**
 * @covers \ZfcRbac\Factory\GuardsFactory
 */
class GuardsFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $moduleOptions = new ModuleOptions(array(
            'guards' => array(
                'ZfcRbac\Guard\RouteGuard' => array(
                    'admin/*' => 'role1'
                ),
                'ZfcRbac\Guard\ControllerGuard' => array(
                    array(
                        'controller' => 'MyController',
                        'actions'    => array('index', 'edit'),
                        'roles'      => array('role')
                    )
                )
            )
        ));

        $pluginManager = new GuardPluginManager();

        $serviceManager = new ServiceManager();
        $serviceManager->setService('ZfcRbac\Options\ModuleOptions', $moduleOptions);
        $serviceManager->setService('ZfcRbac\Guard\GuardPluginManager', $pluginManager);
        $serviceManager->setService(
            'ZfcRbac\Service\AuthorizationService',
            $this->getMock('ZfcRbac\Service\AuthorizationService', array(), array(), '', false)
        );

        $pluginManager->setServiceLocator($serviceManager);

        $factory = new GuardsFactory();
        $guards  = $factory->createService($serviceManager);

        $this->assertInternalType('array', $guards);

        $this->assertCount(2, $guards);
        $this->assertInstanceOf('ZfcRbac\Guard\RouteGuard', $guards[0]);
        $this->assertInstanceOf('ZfcRbac\Guard\ControllerGuard', $guards[1]);
    }
}
