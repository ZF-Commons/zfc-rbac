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
use ZfcRbac\Guard\RouteGuard;

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
        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $routeGuard       = new RouteGuard($identityProvider, $rules);

        $reflProperty = new \ReflectionProperty($routeGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($routeGuard));
    }

    public function routeDataProvider()
    {
        return array(
            array(
                'rules'            => array('admin' => 'member'),
                'matchedRouteName' => 'admin',
                'role'             => 'member',
                'isGranted'        => true
            ),
            array(
                'rules'            => array('admin*' => 'member'),
                'matchedRouteName' => 'admin/bar',
                'role'             => 'member',
                'isGranted'        => true
            ),
            array(
                'rules'            => array('something' => 'member'),
                'matchedRouteName' => 'admin',
                'role'             => 'member',
                'isGranted'        => true
            ),
            array(
                'rules'            => array('admin' => 'member'),
                'matchedRouteName' => 'admin',
                'role'             => 'guest',
                'isGranted'        => false
            ),
            array(
                'rules'            => array(
                    'users/edit'   => array('member'),
                    'users/delete' => array('admin')
                ),
                'matchedRouteName' => 'users/edit',
                'role'             => 'admin',
                'isGranted'        => false
            ),
            array(
                'rules'            => array('users/delete' => array('member', 'admin')),
                'matchedRouteName' => 'users/delete',
                'role'             => 'guest',
                'isGranted'        => false
            ),
        );
    }

    /**
     * @dataProvider routeDataProvider
     */
    public function testRouteGranted(array $rules, $matchedRouteName, $role, $isGranted)
    {
        $event      = new MvcEvent();
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName($matchedRouteName);

        $event->setRouteMatch($routeMatch);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue($role));

        $routeGuard = new RouteGuard($identityProvider, $rules);

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

        $routeMatch->setMatchedRouteName('admin');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $routeGuard = new RouteGuard($identityProvider, array(
            'admin' => 'member'
        ));

        $routeGuard->onRoute($event);

        $this->assertEquals(RouteGuard::GUARD_AUTHORIZED, $event->getParam('guard-result'));
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

        $routeMatch->setMatchedRouteName('admin');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identityProvider = $this->getMock('ZfcRbac\Identity\IdentityProviderInterface');
        $identityProvider->expects($this->any())
                         ->method('getIdentityRoles')
                         ->will($this->returnValue('member'));

        $routeGuard = new RouteGuard($identityProvider, array(
            'admin' => 'guest'
        ));

        $routeGuard->onRoute($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(RouteGuard::GUARD_UNAUTHORIZED, $event->getParam('guard-result'));
    }
}
 