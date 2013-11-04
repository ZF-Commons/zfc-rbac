<?php

namespace ZfcRbacTest\Firewall;

use PHPUnit_Framework_TestCase;
use ZfcRbac\Firewall\Route as RouteFirewall;

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function getRouteFirewallParameters()
    {
        return array(
            array(
                array(
                    array(
                        'route' => 'application/private/*',
                        'roles' => 'user'
                    ),
                    array(
                        'route' => 'application/other/*',
                        'roles' => 'user'
                    ),
                ),
                array(
                    array(
                        'resource' => 'application/private',
                        'result' => false
                    ),
                    array(
                        'resource' => 'application',
                        'result' => true
                    ),
                )
            ),

            array(
                array(
                    array(
                        'route' => 'application/private/*',
                        'permissions' => 'PRIVATE_PERMISSION',
                    ),
                    array(
                        'route' => 'application/secret/*',
                        'permissions' => 'SECRET_PERMISSION',
                    )
                ),
                array(
                    array(
                        'resource' => 'application/private',
                        'result' => false
                    ),
                    array(
                        'resource' => 'application/secret',
                        'result' => true
                    ),
                )
            )
        );
    }

    /**
     * @dataProvider getRouteFirewallParameters
     */
    public function testRouteFirewall($rules, $checks)
    {
        $firewall = new RouteFirewall($rules);
        $mockRbac = $this->getMock('ZfcRbac\Service\Rbac');
        $mockRbac->expects($this->any())
            ->method('hasRole')
            ->will($this->returnCallback(function ($val) {
                if ($val === array('guest')) {
                    return true;
                }

                return false;
            }));
        $mockRbac->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function ($val) {
                if ($val === 'SECRET_PERMISSION') {
                    return true;
                }

                return false;
            }));

        $firewall->setRbac($mockRbac);

        foreach ($checks as $check) {
            $this->assertEquals($check['result'], $firewall->isGranted($check['resource']));
        }
    }

    public function testRouteFirewallMatchOnlyChildRoutes()
    {
        $rules = array(
            array(
                'route' => 'user-route',
                'roles' => 'user'
            ),
        );

        $firewall = new RouteFirewall($rules);

        $mockRbac = $this->getMock('ZfcRbac\Service\Rbac');
        $mockRbac->expects($this->any())
            ->method('hasRole')
            ->will($this->returnCallback(function ($val) {
                if ($val === array('guest')) {
                    return true;
                }

                return false;
            }));
        $mockRbac->expects($this->any())
            ->method('isGranted')
            ->will($this->returnCallback(function ($val) {
                if ($val === 'SECRET_PERMISSION') {
                    return true;
                }

                return false;
            }));

        $firewall->setRbac($mockRbac);

        $checks = array(
            array(
                'resource' => 'user-route',
                'result' => false
            ),
            array(
                'resource' => 'guest-route',
                'result' => true
            ),
            array(
                'resource' => 'user-route/foo',
                'result' => false
            ),
            array(
                'resource' => 'foo/user-route',
                'result' => true
            ),
            array(
                'resource' => 'myuser-route',
                'result' => true
            ),
        );

        foreach ($checks as $check) {
            $this->assertEquals($check['result'], $firewall->isGranted($check['resource']));
        }
    }
}
