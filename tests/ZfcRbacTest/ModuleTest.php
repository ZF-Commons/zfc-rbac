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

namespace ZfcRbacTest;

use ZfcRbac\Module;
use ZfcRbac\Options\ModuleOptions;

/**
 * @covers \ZfcRbac\Module
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigIsArray()
    {
        $module = new Module();
        $this->assertInternalType('array', $module->getConfig());
    }

    public function testAssertRouteGuardListenerIsAddedIfConfigIsNotEmpty()
    {
        $moduleOptions = new ModuleOptions(array(
            'guards' => array(
                'route_rules' => array(
                    'route' => array('role1', 'role2')
                )
            )
        ));

        $module         = new Module();
        $mvcEvent       = $this->getMock('Zend\Mvc\MvcEvent');
        $application    = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager   = $this->getMock('Zend\EventManager\EventManagerInterface');
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');

        $routeGuard = $this->getMock('ZfcRbac\Guard\RouteGuard', array(), array(), '', false);

        $mvcEvent->expects($this->once())->method('getTarget')->will($this->returnValue($application));
        $application->expects($this->once())->method('getEventManager')->will($this->returnValue($eventManager));
        $application->expects($this->once())->method('getServiceManager')->will($this->returnValue($serviceManager));

        $serviceManager->expects($this->at(0))
                       ->method('get')
                       ->with('ZfcRbac\Options\ModuleOptions')
                       ->will($this->returnValue($moduleOptions));

        $serviceManager->expects($this->at(1))
                       ->method('get')
                       ->with('ZfcRbac\Guard\RouteGuard')
                       ->will($this->returnValue($routeGuard));

        $eventManager->expects($this->at(0))->method('attachAggregate')->with($routeGuard);

        $module->onBootstrap($mvcEvent);
    }

    public function testAssertControllerGuardListenerIsAddedIfConfigIsNotEmpty()
    {
        $moduleOptions = new ModuleOptions(array(
            'guards' => array(
                'controller_rules' => array(
                    array(
                        'controller' => 'MyController',
                        'roles'      => array('role1', 'role2')
                    )
                )
            )
        ));

        $module          = new Module();
        $mvcEvent        = $this->getMock('Zend\Mvc\MvcEvent');
        $application     = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager    = $this->getMock('Zend\EventManager\EventManagerInterface');
        $serviceManager  = $this->getMock('Zend\ServiceManager\ServiceManager');

        $controllerGuard = $this->getMock('ZfcRbac\Guard\ControllerGuard', array(), array(), '', false);

        $mvcEvent->expects($this->once())->method('getTarget')->will($this->returnValue($application));
        $application->expects($this->once())->method('getEventManager')->will($this->returnValue($eventManager));
        $application->expects($this->once())->method('getServiceManager')->will($this->returnValue($serviceManager));

        $serviceManager->expects($this->at(0))
                       ->method('get')
                       ->with('ZfcRbac\Options\ModuleOptions')
                       ->will($this->returnValue($moduleOptions));

        $serviceManager->expects($this->at(1))
                       ->method('get')
                       ->with('ZfcRbac\Guard\ControllerGuard')
                       ->will($this->returnValue($controllerGuard));

        $eventManager->expects($this->at(0))->method('attachAggregate')->with($controllerGuard);

        $module->onBootstrap($mvcEvent);
    }
}
