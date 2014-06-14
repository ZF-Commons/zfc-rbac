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
use ZfcRbac\Guard\GuardInterface;
use ZfcRbac\Guard\RouteGuard;
use ZfcRbac\Guard\RoutePermissionsGuard;
use ZfcRbac\Role\InMemoryRoleProvider;
use ZfcRbac\Service\RoleService;
use Rbac\Traversal\Strategy\RecursiveRoleIteratorStrategy;

/**
 * @covers \ZfcRbac\Guard\AbstractGuard
 * @covers \ZfcRbac\Guard\RoutePermissionsGuard
 */
class RoutePermissionsGuardTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachToRightEvent()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())
            ->method('attach')
            ->with(RouteGuard::EVENT_NAME);

        $guard = new RoutePermissionsGuard($this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false));
        $guard->attach($eventManager);
    }

    /**
     * We want to ensure an order for guards
     */
    public function testAssertRouteGuardPriorityIsHigherThanControllerGuardPriority()
    {
        $this->assertTrue(RouteGuard::EVENT_PRIORITY > ControllerGuard::EVENT_PRIORITY);
    }

    public function rulesConversionProvider()
    {
        return [
            // Simple string to array conversion
            [
                'rules'    => [
                    'route' => 'permission1'
                ],
                'expected' => [
                    'route' => ['permission1']
                ]
            ],
            // Array to array
            [
                'rules'    => [
                    'route' => ['permission1', 'permission2']
                ],
                'expected' => [
                    'route' => ['permission1', 'permission2']
                ]
            ],
            // Traversable to array
            [
                'rules'    => [
                    'route' => new \ArrayIterator(['permission1', 'permission2'])
                ],
                'expected' => [
                    'route' => ['permission1', 'permission2']
                ]
            ],
            // Block a route for everyone
            [
                'rules'    => [
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
        $roleService  = $this->getMock('ZfcRbac\Service\AuthorizationService', [], [], '', false);
        $routeGuard   = new RoutePermissionsGuard($roleService, $rules);
        $reflProperty = new \ReflectionProperty($routeGuard, 'rules');
        $reflProperty->setAccessible(true);
        $this->assertEquals($expected, $reflProperty->getValue($routeGuard));
    }

    public function routeDataProvider()
    {
        return [
            // Assert basic one-to-one mapping with both policies
            [
                'rules'               => ['adminRoute' => 'admin'],
                'matchedRouteName'    => 'adminRoute',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['adminRoute' => 'admin'],
                'matchedRouteName'    => 'adminRoute',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that policy changes result for non-specified route guards
            [
                'rules'               => ['route' => 'admin'],
                'matchedRouteName'    => 'anotherRoute',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['route' => 'admin'],
                'matchedRouteName'    => 'anotherRoute',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that composed route work for both policies
            [
                'rules'               => ['admin/dashboard' => 'admin'],
                'matchedRouteName'    => 'admin/dashboard',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['admin/dashboard' => 'admin'],
                'matchedRouteName'    => 'admin/dashboard',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that wildcard route work for both policies
            [
                'rules'               => ['admin/*' => 'admin'],
                'matchedRouteName'    => 'admin/dashboard',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['admin/*' => 'admin'],
                'matchedRouteName'    => 'admin/dashboard',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that wildcard route does match (or not depending on the policy) if rules is after matched route name
            [
                'rules'               => ['fooBar/*' => 'admin'],
                'matchedRouteName'    => 'admin/fooBar/baz',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['fooBar/*' => 'admin'],
                'matchedRouteName'    => 'admin/fooBar/baz',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that it can grant access with multiple rules
            [
                'rules'               => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName'    => 'route1',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName'    => 'route2',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName'    => 'route1',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            [
                'rules'               => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName'    => 'route2',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that it can grant/deny access with multiple rules based on the policy
            [
                'rules'               => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName'    => 'route3',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    'route1' => 'admin',
                    'route2' => 'admin'
                ],
                'matchedRouteName'    => 'route3',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert it can deny access if a permission does not have access
            [
                'rules'               => ['route' => 'admin'],
                'matchedRouteName'    => 'route',
                'identityPermissions' => [
                    ['admin', null, false],
                    ['guest', null, true]
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['route' => 'admin'],
                'matchedRouteName'    => 'route',
                'identityPermissions' => [
                    ['admin', null, false],
                    ['guest', null, true]
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert it can deny access if one of a permission does not have access
            [
                'rules'               => ['route' => ['admin', 'guest']],
                'matchedRouteName'    => 'route',
                'identityPermissions' => [
                    ['admin', null, true],
                    ['guest', null, true]
                ],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['route' => ['admin', 'guest']],
                'matchedRouteName'    => 'route',
                'identityPermissions' => [
                    ['admin', null, true],
                    ['guest', null, false]
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['route' => ['admin', 'guest']],
                'matchedRouteName'    => 'route',
                'identityPermissions' => [
                    ['admin', null, false],
                    ['guest', null, true]
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            // Assert wildcard in permission
            [
                'rules'               => ['home' => '*'],
                'matchedRouteName'    => 'home',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['home' => '*'],
                'matchedRouteName'    => 'home',
                'identityPermissions' => [['admin', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert wildcard wins all
            [
                'rules'               => ['home' => ['*', 'admin']],
                'matchedRouteName'    => 'home',
                'identityPermissions' => [['admin', null, false]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => ['home' => ['*', 'admin']],
                'matchedRouteName'    => 'home',
                'identityPermissions' => [['admin', null, false]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
        ];
    }

    /**
     * @dataProvider routeDataProvider
     */
    public function testRouteGranted(
        array $rules,
        $matchedRouteName,
        array $identityPermissions,
        $isGranted,
        $protectionPolicy
    ) {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName($matchedRouteName);

        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationServiceInterface', [], [], '', false);
        $authorizationService->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValueMap($identityPermissions));

        $routeGuard = new RoutePermissionsGuard($authorizationService, $rules);
        $routeGuard->setProtectionPolicy($protectionPolicy);

        $this->assertEquals($isGranted, $routeGuard->isGranted($event));
    }

    public function testProperlyFillEventOnAuthorization()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $application = $this->getMock('Zend\Mvc\Application', [], [], '', false);
        $application->expects($this->never())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('adminRoute');

        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->any())->method('getRoles')->will($this->returnValue(['member']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())->method('getIdentity')->will($this->returnValue($identity));

        $roleProvider = new InMemoryRoleProvider(['member']);
        $roleService  = new RoleService($identityProvider, $roleProvider, new RecursiveRoleIteratorStrategy());

        $routeGuard = new RouteGuard($roleService, [
            'adminRoute' => 'member'
        ]);
        $routeGuard->onResult($event);

        $this->assertEmpty($event->getError());
        $this->assertNull($event->getParam('exception'));
    }

    public function testProperlySetUnauthorizedAndTriggerEventOnUnauthorization()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())
            ->method('trigger')
            ->with(MvcEvent::EVENT_DISPATCH_ERROR);

        $application = $this->getMock('Zend\Mvc\Application', [], [], '', false);
        $application->expects($this->once())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));

        $routeMatch = new RouteMatch([]);
        $routeMatch->setMatchedRouteName('adminRoute');

        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())->method('getIdentityRoles')->will($this->returnValue('member'));

        $roleProvider = new InMemoryRoleProvider(['member', 'guest']);
        $roleService  = new RoleService($identityProvider, $roleProvider, new RecursiveRoleIteratorStrategy());

        $routeGuard = new RouteGuard($roleService, [
            'adminRoute' => 'guest'
        ]);
        $routeGuard->onResult($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(RouteGuard::GUARD_UNAUTHORIZED, $event->getError());
        $this->assertInstanceOf('ZfcRbac\Exception\UnauthorizedException', $event->getParam('exception'));
    }
}
