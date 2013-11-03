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
use Zend\Permissions\Rbac\Rbac;
use ZfcRbac\Guard\ControllerGuard;
use ZfcRbac\Guard\GuardInterface;
use ZfcRbac\Service\AuthorizationService;

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
                    'mycontroller'  => array('role1'),
                    'mycontroller2' => array('role2', 'role3'),
                    'mycontroller3' => array('role4')
                )
            ),

            // With one action
            array(
                'rules' => array(
                    array(
                        'controller' => 'MyController',
                        'actions'    => 'DELETE',
                        'roles'      => 'role1'
                    ),
                    array(
                        'controller' => 'MyController2',
                        'actions'    => array('delete'),
                        'roles'      => 'role2'
                    ),
                    new \ArrayIterator(array(
                        'controller' => 'MyController3',
                        'actions'    => new \ArrayIterator(array('DELETE')),
                        'roles'      => new \ArrayIterator(array('role3'))
                    ))
                ),
                'expected' => array(
                    'mycontroller'  => array(
                        'delete' => array('role1')
                    ),
                    'mycontroller2'  => array(
                        'delete' => array('role2')
                    ),
                    'mycontroller3'  => array(
                        'delete' => array('role3')
                    ),
                )
            ),

            // With multiple actions
            array(
                'rules' => array(
                    array(
                        'controller' => 'MyController',
                        'actions'    => array('EDIT', 'delete'),
                        'roles'      => 'role1'
                    ),
                    new \ArrayIterator(array(
                        'controller' => 'MyController2',
                        'actions'    => new \ArrayIterator(array('edit', 'DELETE')),
                        'roles'      => new \ArrayIterator(array('role2'))
                    ))
                ),
                'expected' => array(
                    'mycontroller'  => array(
                        'edit'   => array('role1'),
                        'delete' => array('role1')
                    ),
                    'mycontroller2'  => array(
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
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', array(), array(), '', false);
        $controllerGuard      = new ControllerGuard($authorizationService, $rules);

        $reflProperty = new \ReflectionProperty($controllerGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($controllerGuard));
    }

    public function controllerDataProvider()
    {
        return array(
            // Test simple guard with both policies
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'roles'      => 'admin'
                    )
                ),
                'controller'    => 'BlogController',
                'action'        => 'edit',
                'rolesToCreate' => array('admin'),
                'identityRole'  => 'admin',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'roles'      => 'admin'
                    )
                ),
                'controller'    => 'BlogController',
                'action'        => 'edit',
                'rolesToCreate' => array('admin'),
                'identityRole'  => 'admin',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_DENY
            ),

            // Test with multiple rules
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'actions'    => 'read',
                        'roles'      => 'admin'
                    ),
                    array(
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'admin'
                    )
                ),
                'controller'    => 'BlogController',
                'action'        => 'edit',
                'rolesToCreate' => array('admin'),
                'identityRole'  => 'admin',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'actions'    => 'read',
                        'roles'      => 'admin'
                    ),
                    array(
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'admin'
                    )
                ),
                'controller'    => 'BlogController',
                'action'        => 'edit',
                'rolesToCreate' => array('admin'),
                'identityRole'  => 'admin',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_DENY
            ),

            // Assert that policy can deny unspecified rules
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'roles'      => 'member'
                    ),
                ),
                'controller'    => 'CommentController',
                'action'        => 'edit',
                'rolesToCreate' => array('member'),
                'identityRole'  => 'member',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'roles'      => 'member'
                    ),
                ),
                'controller'    => 'CommentController',
                'action'        => 'edit',
                'rolesToCreate' => array('member'),
                'identityRole'  => 'member',
                'isGranted'     => false,
                'policy'        => GuardInterface::POLICY_DENY
            ),

            // Test assert policy can deny other actions from controller when only one is specified
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'action'     => 'edit',
                        'roles'      => 'member'
                    ),
                ),
                'controller'    => 'BlogController',
                'action'        => 'read',
                'rolesToCreate' => array('member'),
                'identityRole'  => 'member',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules' => array(
                    array(
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'member'
                    ),
                ),
                'controller'    => 'BlogController',
                'action'        => 'read',
                'rolesToCreate' => array('member'),
                'identityRole'  => 'member',
                'isGranted'     => false,
                'policy'        => GuardInterface::POLICY_DENY
            ),

            // Assert it can uses child-parent relationship
            array(
                'rules'            => array(
                    array(
                        'controller' => 'IndexController',
                        'actions'    => 'index',
                        'roles'      => 'guest'
                    )
                ),
                'controller'    => 'IndexController',
                'action'        => 'index',
                'rolesToCreate' => array('admin', 'guest' => 'admin'),
                'identityRole'  => 'admin',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array(
                    array(
                        'controller' => 'IndexController',
                        'actions'    => 'index',
                        'roles'      => 'guest'
                    )
                ),
                'controller'    => 'IndexController',
                'action'        => 'index',
                'rolesToCreate' => array('admin', 'guest' => 'admin'),
                'identityRole'  => 'admin',
                'isGranted'     => true,
                'policy'        => GuardInterface::POLICY_DENY
            ),
        );
    }

    /**
     * @dataProvider controllerDataProvider
     */
    public function testControllerGranted(
        array $rules,
        $controller,
        $action,
        array $rolesToCreate,
        $identityRole,
        $isGranted,
        $protectionPolicy
    ) {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(array());
        $routeMatch->setParam('controller', $controller);
        $routeMatch->setParam('action', $action);

        $event->setRouteMatch($routeMatch);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue($identityRole));

        $rbac = new Rbac();

        foreach ($rolesToCreate as $roleToCreate => $parent) {
            if (is_int($roleToCreate)) {
                $rbac->addRole($parent);
            } else {
                $rbac->addRole($roleToCreate, $parent);
            }
        }

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $controllerGuard = new ControllerGuard($authorizationService, $rules);
        $controllerGuard->setProtectionPolicy($protectionPolicy);

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

        $rbac = new Rbac();
        $rbac->addRole('member');

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $routeGuard = new ControllerGuard($authorizationService, array(
            array(
                'controller' => 'MyController',
                'actions'    => 'edit',
                'roles'      => 'member'
            )
        ));

        $routeGuard->onRoute($event);

        $this->assertEquals(ControllerGuard::GUARD_AUTHORIZED, $event->getParam('guard-result'));
        $this->assertNull($event->getParam('exception'));
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

        $rbac = new Rbac();
        $rbac->addRole('member');

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $routeGuard = new ControllerGuard($authorizationService, array(
            array(
                'controller' => 'MyController',
                'actions'    => 'edit',
                'roles'      => 'member'
            )
        ));

        $routeGuard->onRoute($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(ControllerGuard::GUARD_UNAUTHORIZED, $event->getParam('guard-result'));
        $this->assertEquals(ControllerGuard::GUARD_UNAUTHORIZED, $event->getError());
        $this->assertInstanceOf('ZfcRbac\Exception\UnauthorizedException', $event->getParam('exception'));
    }
}
 