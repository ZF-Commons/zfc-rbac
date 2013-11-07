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

namespace ZfcRbacTest;

use ZfcRbac\Guard\GuardInterface;
use ZfcRbac\Options\GuardsOptions;

/**
 * @covers \ZfcRbac\Options\GuardsOptions
 */
class GuardsOptionsTest extends \PHPUnit_Framework_TestCase
{
    public function testSettersAndGetters()
    {
        $routeRules = array(
            'admin/*' => 'admin'
        );

        $controllerRules = array(
            array(
                'controller' => 'MyController',
                'actions'    => 'index',
                'roles'      => 'admin'
            )
        );

        $guardsOptions = new GuardsOptions(array(
            'protection_policy' => GuardInterface::POLICY_DENY,
            'route_rules'       => $routeRules,
            'controller_rules'  => $controllerRules
        ));

        $this->assertEquals(GuardInterface::POLICY_DENY, $guardsOptions->getProtectionPolicy());
        $this->assertEquals($routeRules, $guardsOptions->getRouteRules());
        $this->assertEquals($controllerRules, $guardsOptions->getControllerRules());
    }

    public function testThrowExceptionForInvalidPolicy()
    {
        $this->setExpectedException('ZfcRbac\Exception\RuntimeException');

        $guardsOptions = new GuardsOptions();
        $guardsOptions->setProtectionPolicy('invalid');
    }
}
