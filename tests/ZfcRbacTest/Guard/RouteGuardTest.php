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
use ZfcRbac\Guard\RouteGuard;
use ZfcRbac\Service\AuthorizationService;

/**
 * @covers \ZfcRbac\Guard\AbstractGuard
 * @covers \ZfcRbac\Guard\RouteGuard
 */
class RouteGuardTest extends \PHPUnit_Framework_TestCase
{
    public function rulesConversionProvider()
    {
        return [
            // Simple string to array conversion
            [
                'rules' => [
                    'route' => 'role1'
                ],
                'expected' => [
                    'route' => ['role1']
                ]
            ],

            // Array to array
            [
                'rules' => [
                    'route' => ['role1', 'role2']
                ],
                'expected' => [
                    'route' => ['role1', 'role2']
                ]
            ],

            // Traversable to array
            [
                'rules' => [
                    'route' => new \ArrayIterator(['role1', 'role2'])
                ],
                'expected' => [
                    'route' => ['role1', 'role2']
                ]
            ],

            // Block a route for everyone
            [
                'rules' => [
                    'route'
                ],
                'expected' => [
                    'route' => []
                ]
            ],
        ];
    }

    /**
     * @dataProvider rulesConversionProvider
     */
    public function testRulesConversions(array $rules, array $expected)
    {
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $routeGuard           = new RouteGuard($authorizationService, $rules);

        $reflProperty = new \ReflectionProperty($routeGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($routeGuard));
    }

    public function routeDataProvider()
    {
        return [
            // Assert basic one-to-one mapping with both policies
            [
                'rules'            => ['adminRoute' => 'admin'],
                'matchedRouteName' => 'adminRoute',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['adminRoute' => 'admin'],
                'matchedRouteName' => 'adminRoute',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert that policy changes result for non-specified route guards
            [
                'rules'            => ['route' => 'member'],
                'matchedRouteName' => 'anotherRoute',
                'rolesToCreate'    => ['member'],
                'identityRole'     => 'member',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['route' => 'member'],
                'matchedRouteName' => 'anotherRoute',
                'rolesToCreate'    => ['member'],
                'identityRole'     => 'member',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert that composed route work for both policies
            [
                'rules'            => ['admin/dashboard' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['admin/dashboard' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert that wildcard route work for both policies
            [
                'rules'            => ['admin/*' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['admin/*' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert that wildcard route does match (or not depending on the policy) if rules is after matched route name
            [
                'rules'            => ['fooBar/*' => 'admin'],
                'matchedRouteName' => 'admin/fooBar/baz',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['fooBar/*' => 'admin'],
                'matchedRouteName' => 'admin/fooBar/baz',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert that it can grant access with multiple rules
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName' => 'route1',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName' => 'route1',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert that it can grant/deny access with multiple rules based on the policy
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName' => 'route3',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName' => 'route3',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert it can deny access if a role does not have access
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesToCreate'    => ['admin', 'guest'],
                'identityRole'     => 'guest',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesToCreate'    => ['admin', 'guest'],
                'identityRole'     => 'guest',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert it can grant access using child-parent relationship between roles
            [
                'rules'            => ['home' => 'guest'],
                'matchedRouteName' => 'home',
                'rolesToCreate'    => ['admin', 'login' => 'admin', 'guest' => 'login'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['home' => 'guest'],
                'matchedRouteName' => 'home',
                'rolesToCreate'    => ['admin', 'login' => 'admin', 'guest' => 'login'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert it can deny access although using child-parent relationship between roles (just to be sure)
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesToCreate'    => ['admin', 'login' => 'admin'],
                'identityRole'     => 'login',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesToCreate'    => ['admin', 'login' => 'admin'],
                'identityRole'     => 'login',
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY
            ],

            // Assert wildcard in role
            [
                'rules'            => ['home' => '*'],
                'matchedRouteName' => 'home',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'            => ['home' => '*'],
                'matchedRouteName' => 'home',
                'rolesToCreate'    => ['admin'],
                'identityRole'     => 'admin',
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY
            ],
        ];
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
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName($matchedRouteName);

        $event->setRouteMatch($routeMatch);

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->any())->method('getRoles')->will($this->returnValue($identityRole));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

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

    public function testProperlyFillEventOnAuthorization()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch([]);

        $application  = $this->getMock('Zend\Mvc\Application', [], [], '', false);
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $application->expects($this->never())
                    ->method('getEventManager')
                    ->will($this->returnValue($eventManager));

        $routeMatch->setMatchedRouteName('adminRoute');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->any())->method('getRoles')->will($this->returnValue(['member']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentity')
                         ->will($this->returnValue($identity));

        $rbac = new Rbac();
        $rbac->addRole('member');

        $authorizationService = new AuthorizationService($rbac, $identityProvider);

        $routeGuard = new RouteGuard($authorizationService, [
            'adminRoute' => 'member'
        ]);

        $routeGuard->onDispatch($event);

        $this->assertEmpty($event->getError());
        $this->assertNull($event->getParam('exception'));
    }

    public function testProperlySetUnauthorizedAndTriggerEventOnUnauthorization()
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch([]);

        $application  = $this->getMock('Zend\Mvc\Application', [], [], '', false);
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

        $routeGuard = new RouteGuard($authorizationService, [
            'adminRoute' => 'guest'
        ]);

        $routeGuard->onDispatch($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(RouteGuard::GUARD_UNAUTHORIZED, $event->getError());
        $this->assertInstanceOf('ZfcRbac\Exception\UnauthorizedException', $event->getParam('exception'));
    }

    public function testAssertRoutePriorityIsHigherThanControllerPriority()
    {
        $this->assertTrue(RouteGuard::EVENT_PRIORITY > ControllerGuard::EVENT_PRIORITY);
    }

    public function testDefaultProtectionPolicyIsInherited()
    {
        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $rbac                 = new Rbac();
        $authorizationService = new AuthorizationService($rbac, $identityProvider);
        $routeGuard           = new RouteGuard($authorizationService, []);

        $this->assertSame(GuardInterface::POLICY_DENY, $routeGuard->getProtectionPolicy());
    }
}
