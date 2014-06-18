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
use ZfcRbac\Role\InMemoryRoleProvider;
use ZfcRbac\Service\RoleService;
use Rbac\Traversal\Strategy\RecursiveRoleIteratorStrategy;

/**
 * @covers \ZfcRbac\Guard\AbstractGuard
 * @covers \ZfcRbac\Guard\ControllerGuard
 */
class ControllerGuardTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachToRightEvent()
    {
        $roleService          = $this->getMock('ZfcRbac\Service\RoleService', [], [], '', false);
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationServiceInterface', [], [], '', false);
        $guard                = new ControllerGuard($roleService, $authorizationService);

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())
            ->method('attach')
            ->with(ControllerGuard::EVENT_NAME);

        $guard->attach($eventManager);
    }

    public function rulesConversionProvider()
    {
        return [
            // Without actions
            [
                'rules'    => [
                    [
                        'controller'  => 'MyController',
                        'roles'       => 'role1',
                        'permissions' => 'post.edit',
                    ],
                    [
                        'controller'  => 'MyController2',
                        'roles'       => ['role2', 'role3'],
                        'permissions' => ['post.edit', 'post.delete'],
                    ],
                    new \ArrayIterator([
                        'controller' => 'MyController3',
                        'roles'      => new \ArrayIterator(['role4'])
                    ])
                ],
                'expected' => [
                    'mycontroller'  => [
                        0 => [
                            'roles'       => ['role1'],
                            'permissions' => ['post.edit'],
                        ]
                    ],
                    'mycontroller2' => [
                        0 => [
                            'roles'       => ['role2', 'role3'],
                            'permissions' => ['post.edit', 'post.delete'],
                        ]
                    ],
                    'mycontroller3' => [
                        0 => [
                            'roles'       => ['role4'],
                            'permissions' => [],
                        ]
                    ]
                ]
            ],
            // With one action
            [
                'rules'    => [
                    [
                        'controller' => 'MyController',
                        'actions'    => 'DELETE',
                        'roles'      => 'role1'
                    ],
                    [
                        'controller' => 'MyController2',
                        'actions'    => ['delete'],
                        'roles'      => 'role2'
                    ],
                    new \ArrayIterator([
                        'controller' => 'MyController3',
                        'actions'    => new \ArrayIterator(['DELETE']),
                        'roles'      => new \ArrayIterator(['role3'])
                    ])
                ],
                'expected' => [
                    'mycontroller'  => [
                        'delete' => [
                            'roles'       => ['role1'],
                            'permissions' => [],
                        ],
                    ],
                    'mycontroller2' => [
                        'delete' => [
                            'roles'       => ['role2'],
                            'permissions' => [],
                        ],
                    ],
                    'mycontroller3' => [
                        'delete' => [
                            'roles'       => ['role3'],
                            'permissions' => [],
                        ],
                    ],
                ]
            ],
            // With multiple actions
            [
                'rules'    => [
                    [
                        'controller' => 'MyController',
                        'actions'    => ['EDIT', 'delete'],
                        'roles'      => 'role1'
                    ],
                    new \ArrayIterator([
                        'controller' => 'MyController2',
                        'actions'    => new \ArrayIterator(['edit', 'DELETE']),
                        'roles'      => new \ArrayIterator(['role2'])
                    ])
                ],
                'expected' => [
                    'mycontroller'  => [
                        'edit'   => [
                            'roles'       => ['role1'],
                            'permissions' => [],
                        ],
                        'delete' => [
                            'roles'       => ['role1'],
                            'permissions' => [],
                        ],
                    ],
                    'mycontroller2' => [
                        'edit'   => [
                            'roles'       => ['role2'],
                            'permissions' => [],
                        ],
                        'delete' => [
                            'roles'       => ['role2'],
                            'permissions' => [],
                        ],
                    ]
                ]
            ],
            // Test that that if a rule is set globally to the controller, it does not override any
            // action specific rule that may have been specified before
            [
                'rules'    => [
                    [
                        'controller' => 'MyController',
                        'actions'    => ['edit'],
                        'roles'      => 'role1'
                    ],
                    [
                        'controller' => 'MyController',
                        'roles'      => 'role2'
                    ]
                ],
                'expected' => [
                    'mycontroller' => [
                        'edit' => [
                            'roles'       => ['role1'],
                            'permissions' => [],
                        ],
                        0      => [
                            'roles'       => ['role2'],
                            'permissions' => [],
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider rulesConversionProvider
     */
    public function testRulesConversions(array $rules, array $expected)
    {
        $roleService          = $this->getMock('ZfcRbac\Service\RoleService', [], [], '', false);
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationServiceInterface', [], [], '', false);
        $controllerGuard      = new ControllerGuard($roleService, $authorizationService, $rules);

        $reflProperty = new \ReflectionProperty($controllerGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($controllerGuard));
    }

    public function controllerDataProvider()
    {
        return [
            // Test simple guard with both policies
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'roles'      => 'admin'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'roles'      => 'admin'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'permissions' => 'post.edit'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [['post.edit', null, true]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'permissions' => 'post.edit'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [['post.edit', null, false]],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            // Test roles prevails
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'roles'       => 'admin',
                        'permissions' => 'post.edit'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [['post.edit', null, false]],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'roles'       => 'admin',
                        'permissions' => 'post.edit'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'member',
                'identityPermissions' => [['post.edit', null, true]],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            // Test with multiple rules
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'actions'    => 'read',
                        'roles'      => 'admin'
                    ],
                    [
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'admin'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'actions'     => 'read',
                        'permissions' => 'post.read'
                    ],
                    [
                        'controller'  => 'BlogController',
                        'actions'     => 'edit',
                        'permissions' => 'post.edit'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => ['admin'],
                'identityRole'        => 'admin',
                'identityPermissions' => [
                    ['post.read', null, false],
                    ['post.edit', null, true],
                ],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'actions'     => 'read',
                        'permissions' => 'post.read'
                    ],
                    [
                        'controller'  => 'BlogController',
                        'actions'     => 'edit',
                        'permissions' => 'post.edit'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'read',
                'rolesConfig'         => ['admin'],
                'identityRole'        => 'admin',
                'identityPermissions' => [
                    ['post.read', null, false],
                    ['post.edit', null, true],
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'actions'    => 'read',
                        'roles'      => 'admin'
                    ],
                    [
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'admin'
                    ]
                ],
                'controller'          => 'BlogController',
                'action'              => 'edit',
                'rolesConfig'         => [
                    'admin'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert that policy can deny unspecified rules
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'roles'      => 'member'
                    ],
                ],
                'controller'          => 'CommentController',
                'action'              => 'edit',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'roles'      => 'member'
                    ],
                ],
                'controller'          => 'CommentController',
                'action'              => 'edit',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY,
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'permissions' => 'post.edit'
                    ],
                ],
                'controller'          => 'CommentController',
                'action'              => 'edit',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [
                    ['post.read', null, false],
                ],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'permissions' => 'post.edit'
                    ],
                ],
                'controller'          => 'CommentController',
                'action'              => 'edit',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [
                    ['post.read', null, false],
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY,
            ],
            // Test assert policy can deny other actions from controller when only one is specified
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'member'
                    ],
                ],
                'controller'          => 'BlogController',
                'action'              => 'read',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller' => 'BlogController',
                        'actions'    => 'edit',
                        'roles'      => 'member'
                    ],
                ],
                'controller'          => 'BlogController',
                'action'              => 'read',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'BlogController',
                        'actions'     => 'edit',
                        'permissions' => 'post.edit'
                    ],
                ],
                'controller'          => 'BlogController',
                'action'              => 'read',
                'rolesConfig'         => ['member'],
                'identityRole'        => 'member',
                'identityPermissions' => [
                    ['post.edit', null, true],
                ],
                'isGranted'           => false,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert it can uses parent-children relationship
            [
                'rules'               => [
                    [
                        'controller' => 'IndexController',
                        'actions'    => 'index',
                        'roles'      => 'guest'
                    ]
                ],
                'controller'          => 'IndexController',
                'action'              => 'index',
                'rolesConfig'         => [
                    'admin' => [
                        'children' => ['guest']
                    ],
                    'guest'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller' => 'IndexController',
                        'actions'    => 'index',
                        'roles'      => 'guest'
                    ]
                ],
                'controller'          => 'IndexController',
                'action'              => 'index',
                'rolesConfig'         => [
                    'admin' => [
                        'children' => ['guest']
                    ],
                    'guest'
                ],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert wildcard in roles
            [
                'rules'               => [
                    [
                        'controller' => 'IndexController',
                        'roles'      => '*'
                    ]
                ],
                'controller'          => 'IndexController',
                'action'              => 'index',
                'rolesConfig'         => ['admin'],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller' => 'IndexController',
                        'roles'      => '*'
                    ]
                ],
                'controller'          => 'IndexController',
                'action'              => 'index',
                'rolesConfig'         => ['admin'],
                'identityRole'        => 'admin',
                'identityPermissions' => [],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
            // Assert wildcard in permissions
            [
                'rules'               => [
                    [
                        'controller'  => 'IndexController',
                        'permissions' => '*'
                    ]
                ],
                'controller'          => 'IndexController',
                'action'              => 'index',
                'rolesConfig'         => ['admin'],
                'identityRole'        => 'admin',
                'identityPermissions' => [
                    ['post.read', null, false],
                ],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_ALLOW
            ],
            [
                'rules'               => [
                    [
                        'controller'  => 'IndexController',
                        'permissions' => '*'
                    ]
                ],
                'controller'          => 'IndexController',
                'action'              => 'index',
                'rolesConfig'         => ['admin'],
                'identityRole'        => 'admin',
                'identityPermissions' => [
                    ['post.read', null, false],
                ],
                'isGranted'           => true,
                'policy'              => GuardInterface::POLICY_DENY
            ],
        ];
    }

    /**
     * @dataProvider controllerDataProvider
     */
    public function testControllerGranted(
        array $rules,
        $controller,
        $action,
        array $rolesConfig,
        $identityRole,
        $identityPermissions,
        $isGranted,
        $protectionPolicy
    ) {
        $routeMatch = new RouteMatch([]);
        $routeMatch->setParam('controller', $controller);
        $routeMatch->setParam('action', $action);

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->any())->method('getRoles')->will($this->returnValue($identityRole));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($identity));

        $roleProvider = new InMemoryRoleProvider($rolesConfig);
        $roleService  = new RoleService($identityProvider, $roleProvider, new RecursiveRoleIteratorStrategy());

        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationServiceInterface', [], [], '', false);
        $authorizationService->expects($this->any())
            ->method('isGranted')
            ->will($this->returnValueMap($identityPermissions));

        $controllerGuard = new ControllerGuard($roleService, $authorizationService, $rules);
        $controllerGuard->setProtectionPolicy($protectionPolicy);

        $event = new MvcEvent();
        $event->setRouteMatch($routeMatch);

        $this->assertEquals($isGranted, $controllerGuard->isGranted($event));
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

        $routeMatch->setParam('controller', 'MyController');
        $routeMatch->setParam('action', 'edit');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->any())->method('getRoles')->will($this->returnValue(['member']));

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($identity));

        $roleProvider = new InMemoryRoleProvider([
            'member'
        ]);

        $roleService          = new RoleService($identityProvider, $roleProvider, new RecursiveRoleIteratorStrategy());
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationServiceInterface', [], [], '', false);

        $routeGuard = new ControllerGuard($roleService, $authorizationService, [
            [
                'controller' => 'MyController',
                'actions'    => 'edit',
                'roles'      => 'member'
            ]
        ]);

        $routeGuard->onResult($event);

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

        $routeMatch->setParam('controller', 'MyController');
        $routeMatch->setParam('action', 'delete');

        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
            ->method('getIdentityRoles')
            ->will($this->returnValue('member'));

        $roleProvider = new InMemoryRoleProvider([
            'member'
        ]);

        $roleService          = new RoleService($identityProvider, $roleProvider, new RecursiveRoleIteratorStrategy());
        $authorizationService = $this->getMock('ZfcRbac\Service\AuthorizationServiceInterface', [], [], '', false);

        $routeGuard = new ControllerGuard($roleService, $authorizationService, [
            [
                'controller' => 'MyController',
                'actions'    => 'edit',
                'roles'      => 'member'
            ]
        ]);

        $routeGuard->onResult($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(ControllerGuard::GUARD_UNAUTHORIZED, $event->getError());
        $this->assertInstanceOf('ZfcRbac\Exception\UnauthorizedException', $event->getParam('exception'));
    }
}
