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

namespace ZfcRbacTest\Guard;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use ZfcRbac\Guard\ControllerGuard;

/**
 * @covers \ZfcRbac\Guard\ControllerGuard
 */
class ControllerGuardTest extends \PHPUnit_Framework_TestCase
{
    public function rulesConversionProvider()
    {
        return array(
            // Without actions
            array(
                'rules' => array(
                    array(
                        'controller' => 'MyController',
                        'roles'      => 'role1'
                    ),
                    array(
                        'controller' => 'MyController2',
                        'roles'      => array('role2', 'role3')
                    ),
                    new \ArrayIterator(array(
                        'controller' => 'MyController3',
                        'roles'      => new \ArrayIterator(array('role4'))
                    ))
                ),
                'expected' => array(
                    'MyController'  => array('role1'),
                    'MyController2' => array('role2', 'role3'),
                    'MyController3' => array('role4')
                )
            ),

            // With one action
            array(
                'rules' => array(
                    array(
                        'controller' => 'MyController',
                        'actions'    => 'delete',
                        'roles'      => 'role1'
                    ),
                    array(
                        'controller' => 'MyController2',
                        'actions'    => array('delete'),
                        'roles'      => 'role2'
                    ),
                    new \ArrayIterator(array(
                        'controller' => 'MyController3',
                        'actions'    => new \ArrayIterator(array('delete')),
                        'roles'      => new \ArrayIterator(array('role3'))
                    ))
                ),
                'expected' => array(
                    'MyController'  => array(
                        'delete' => array('role1')
                    ),
                    'MyController2'  => array(
                        'delete' => array('role2')
                    ),
                    'MyController3'  => array(
                        'delete' => array('role3')
                    ),
                )
            ),

            // With multiple actions
            array(
                'rules' => array(
                    array(
                        'controller' => 'MyController',
                        'actions'    => array('edit', 'delete'),
                        'roles'      => 'role1'
                    ),
                    new \ArrayIterator(array(
                        'controller' => 'MyController2',
                        'actions'    => new \ArrayIterator(array('edit', 'delete')),
                        'roles'      => new \ArrayIterator(array('role2'))
                    ))
                ),
                'expected' => array(
                    'MyController'  => array(
                        'edit'   => array('role1'),
                        'delete' => array('role1')
                    ),
                    'MyController2'  => array(
                        'edit'   => array('role2'),
                        'delete' => array('role2')
                    )
                )
            ),
        );
    }

    /**
     * @dataProvider rulesConversionProvider
     */
    public function testRulesConversions(array $rules, array $expected)
    {
        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $controllerGuard  = new ControllerGuard($identityProvider, $rules);

        $reflProperty = new \ReflectionProperty($controllerGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($controllerGuard));
    }

    public function controllerDataProvider()
    {
        return array(
            array(
                'rules' => array(
                    array(
                        'controller' => 'Blog',
                        'roles'      => 'admin'
                    )
                ),
                'controller'  => 'Blog',
                'action'      => 'edit',
                'role'        => 'admin',
                'isGranted'   => true
            ),

            array(
                'rules' => array(
                    array(
                        'controller' => 'Blog',
                        'roles'      => 'admin'
                    )
                ),
                'controller'  => 'Blog',
                'action'      => 'edit',
                'role'        => 'guest',
                'isGranted'   => false
            ),

            array(
                'rules' => array(
                    array(
                        'controller' => 'Blog',
                        'actions'    => 'edit',
                        'roles'      => 'admin'
                    ),
                    array(
                        'controller' => 'Blog',
                        'actions'    => 'read',
                        'roles'      => 'guest'
                    )
                ),
                'controller'  => 'Blog',
                'action'      => 'read',
                'role'        => 'guest',
                'isGranted'   => true
            ),

            array(
                'rules' => array(
                    array(
                        'controller' => 'Blog',
                        'actions'    => array('read', 'edit'),
                        'roles'      => 'guest'
                    ),
                ),
                'controller'  => 'Blog',
                'action'      => 'delete',
                'role'        => 'guest',
                'isGranted'   => false
            ),
        );
    }

    /**
     * @dataProvider controllerDataProvider
     */
    public function testRouteGranted(array $rules, $controller, $action, $role, $isGranted)
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(array());
        $routeMatch->setParam('controller', $controller);
        $routeMatch->setParam('action', $action);

        $event->setRouteMatch($routeMatch);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->once())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue($role));

        $controllerGuard = new ControllerGuard($identityProvider, $rules);

        $this->assertEquals($isGranted, $controllerGuard->isGranted($event));
    }

    public function testProperlySetAuthorizedParamOnAuthorization()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(array());

        $application  = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $application->expects($this->never())
                    ->method('getEventManager')
                    ->will($this->returnValue($eventManager));

        $routeMatch->setParam('controller', 'MyController');
        $routeMatch->setParam('action', 'edit');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $routeGuard = new ControllerGuard($identityProvider, array(
            array(
                'controller' => 'MyController',
                'actions'    => 'edit',
                'roles'      => 'member'
            )
        ));

        $routeGuard->onRoute($event);

        $this->assertEquals(ControllerGuard::GUARD_AUTHORIZED, $event->getParam('guard-result'));
    }

    public function testProperlySetUnauthorizedAndTriggerEventOnUnauthorization()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(array());

        $application  = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $application->expects($this->once())
                    ->method('getEventManager')
                    ->will($this->returnValue($eventManager));

        $eventManager->expects($this->once())
                     ->method('trigger')
                     ->with(MvcEvent::EVENT_DISPATCH_ERROR);

        $routeMatch->setParam('controller', 'MyController');
        $routeMatch->setParam('action', 'delete');

        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $routeGuard = new ControllerGuard($identityProvider, array(
            array(
                'controller' => 'MyController',
                'actions'    => 'edit',
                'roles'      => 'member'
            )
        ));

        $routeGuard->onRoute($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(ControllerGuard::GUARD_UNAUTHORIZED, $event->getParam('guard-result'));
    }
}
 