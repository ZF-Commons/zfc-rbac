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
use ZfcRbac\Guard\GuardInterface;
use ZfcRbac\Guard\RouteGuard;
use ZfcRbac\Service\AuthorizationService;

/**
 * @covers \ZfcRbac\Guard\RouteGuard
 */
class RouteGuardTest extends \PHPUnit_Framework_TestCase
{
    public function rulesConversionProvider()
    {
        return array(
            // Simple string to array conversion
            array(
                'rules' => array(
                    'route' => 'role1'
                ),
                'expected' => array(
                    'route' => array('role1')
                )
            ),

            // Array to array
            array(
                'rules' => array(
                    'route' => array('role1', 'role2')
                ),
                'expected' => array(
                    'route' => array('role1', 'role2')
                )
            ),

            // Traversable to array
            array(
                'rules' => array(
                    'route' => new \ArrayIterator(array('role1', 'role2'))
                ),
                'expected' => array(
                    'route' => array('role1', 'role2')
                )
            ),

            // Block a route for everyone
            array(
                'rules' => array(
                    'route'
                ),
                'expected' => array(
                    'route' => array()
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
        $routeGuard           = new RouteGuard($authorizationService, $rules);

        $reflProperty = new \ReflectionProperty($routeGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($routeGuard));
    }

    public function routeDataProvider()
    {
        return array(
            // Assert basic one-to-one mapping with both policies
            array(
                'rules'            => array('adminRoute' => 'admin'),
                'matchedRouteName' => 'adminRoute',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('adminRoute' => 'admin'),
                'matchedRouteName' => 'adminRoute',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert that policy changes result for non-specified route guards
            array(
                'rules'            => array('route' => 'member'),
                'matchedRouteName' => 'anotherRoute',
                'rolesToCreate'    => array('member'),
                'identityRole'     => 'member',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('route' => 'member'),
                'matchedRouteName' => 'anotherRoute',
                'rolesToCreate'    => array('member'),
                'identityRole'     => 'member',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert that composed route work for both policies
            array(
                'rules'            => array('admin/dashboard' => 'admin'),
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('admin/dashboard' => 'admin'),
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert that wildcard route work for both policies
            array(
                'rules'            => array('admin/*' => 'admin'),
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('admin/*' => 'admin'),
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert that wildcard route does match (or not depending on the policy) if rules is after matched route name
            array(
                'rules'            => array('fooBar/*' => 'admin'),
                'matchedRouteName' => 'admin/fooBar/baz',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('fooBar/*' => 'admin'),
                'matchedRouteName' => 'admin/fooBar/baz',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert that it can grant access with multiple rules
            array(
                'rules'            => array(
                    'route1' => 'admin',
                    'route2' => 'admin'
                ),
                'matchedRouteName' => 'route1',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array(
                    'route1' => 'admin',
                    'route2' => 'admin'
                ),
                'matchedRouteName' => 'route1',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert that it can grant/deny access with multiple rules based on the policy
            array(
                'rules'            => array(
                    'route1' => 'admin',
                    'route2' => 'admin'
                ),
                'matchedRouteName' => 'route3',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array(
                    'route1' => 'admin',
                    'route2' => 'admin'
                ),
                'matchedRouteName' => 'route3',
                'rolesToCreate'    => array('admin'),
                'identityRole'     => 'admin',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert it can deny access if a role does not have access
            array(
                'rules'            => array('route' => 'admin'),
                'matchedRouteName' => 'route',
                'rolesToCreate'    => array('admin', 'guest'),
                'identityRole'     => 'guest',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('route' => 'admin'),
                'matchedRouteName' => 'route',
                'rolesToCreate'    => array('admin', 'guest'),
                'identityRole'     => 'guest',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ),

            // Assert it can grant access using child-parent relationship between roles
            array(
                'rules'            => array('home' => 'guest'),
                'matchedRouteName' => 'home',
                'rolesToCreate'    => array('admin', 'guest' => 'admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ),
            array(
                'rules'            => array('home' => 'guest'),
                'matchedRouteName' => 'home',
                'rolesToCreate'    => array('admin', 'guest' => 'admin'),
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ),
        );
    }

    /**
     * @dataProvider routeDataProvider
     */
    public function testRouteGranted(
        array $rules,
        $matchedRouteName,
        array $rolesToCreate,
        $identityRole,
        $isGranted,
        $protectionPolicy
    ) {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName($matchedRouteName);

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

        $routeGuard = new RouteGuard($authorizationService, $rules);
        $routeGuard->setProtectionPolicy($protectionPolicy);

        $this->assertEquals($isGranted, $routeGuard->isGranted($event));
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

        $routeMatch->setMatchedRouteName('adminRoute');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $rbac = new Rbac();
        $rbac->addRole('member');

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $routeGuard = new RouteGuard($authorizationService, array(
            'adminRoute' => 'member'
        ));

        $routeGuard->onRoute($event);

        $this->assertEquals(RouteGuard::GUARD_AUTHORIZED, $event->getParam('guard-result'));
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

        $routeMatch->setMatchedRouteName('adminRoute');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $rbac = new Rbac();
        $rbac->addRole('member');
        $rbac->addRole('guest');

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $routeGuard = new RouteGuard($authorizationService, array(
            'adminRoute' => 'guest'
        ));

        $routeGuard->onRoute($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(RouteGuard::GUARD_UNAUTHORIZED, $event->getParam('guard-result'));
        $this->assertEquals(RouteGuard::GUARD_UNAUTHORIZED, $event->getError());
        $this->assertInstanceOf('ZfcRbac\Exception\UnauthorizedException', $event->getParam('exception'));
    }
}
 