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
                    'rules' => array(
                        'route'       => 'application/private/*',
                        'roles'       => 'user'
                    )
                ),
                array(
                    array(
                        'resource' => 'application/private',
                        'result'   => false
                    ),
                    array(
                        'resource' => 'application',
                        'result'   => true
                    ),
                )
            ),

            array(
                array(
                    array(
                        'route'       => 'application/private/*',
                        'permissions' => 'PRIVATE_PERMISSION',
                    ),
                    array(
                        'route'       => 'application/secret/*',
                        'permissions' => 'SECRET_PERMISSION',
                    )
                ),
                array(
                    array(
                        'resource' => 'application/private',
                        'result'   => false
                    ),
                    array(
                        'resource' => 'application/secret',
                        'result'   => true
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
                 ->will($this->returnCallback(function($val) {
            if ($val === array('guest')) {
                return true;
            }

            return false;
        }));
        $mockRbac->expects($this->any())
                 ->method('isGranted')
                 ->will($this->returnCallback(function($val) {
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
}
