<?php

namespace ZfcRbacTest\Firewall;

use PHPUnit_Framework_TestCase;
use ZfcRbac\Firewall\Controller as ControllerFirewall;

class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function getControllerFirewallParameters()
    {
        return array(
            array(
                array(
                    'rules' => array(
                        'controller' => 'IndexController',
                        'actions'    => 'foo',
                        'roles'      => 'guest'
                    )
                ),
                array(
                    array(
                        'resource' => 'IndexController:foo',
                        'result'   => true
                    ),
                    array(
                        'resource' => 'IndexController:bar',
                        'result'   => false
                    ),
                )
            ),

            array(
                array(
                    'rules' => array(
                        'controller' => 'IndexController',
                        'actions'    => array('foo', 'bar'),
                        'roles'      => 'guest'
                    )
                ),
                array(
                    array(
                        'resource' => 'IndexController:foo',
                        'result'   => true
                    ),
                    array(
                        'resource' => 'IndexController:bar',
                        'result'   => true
                    ),
                    array(
                        'resource' => 'IndexController:baz',
                        'result'   => false
                    ),
                )
            ),

            array(
                array(
                    'rules' => array(
                        'controller' => 'IndexController',
                        'actions'    => array('foo', 'bar'),
                        'roles'      => array('guest')
                    )
                ),
                array(
                    array(
                        'resource' => 'IndexController:foo',
                        'result'   => true
                    ),
                    array(
                        'resource' => 'IndexController:bar',
                        'result'   => true
                    ),
                    array(
                        'resource' => 'IndexController:baz',
                        'result'   => false
                    ),
                )
            )
        );
    }

    /**
     * @dataProvider getControllerFirewallParameters
     */
    public function testControllerFirewall($rules, $checks)
    {
        $firewall = new ControllerFirewall($rules);
        $mockRbac = $this->getMock('ZfcRbac\Service\Rbac');
        $mockRbac->expects($this->any())
                 ->method('hasRole')
                 ->will($this->returnCallback(function($val) {
            if ($val === array('guest')) {
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
