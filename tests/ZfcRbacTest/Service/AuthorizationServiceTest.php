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

namespace ZfcRbacTest\Service;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Service\AuthorizationService;

/**
 * @covers \ZfcRbac\Service\AuthorizationService
 */
class AuthorizationServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend\Permissions\Rbac\Rbac|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rbac;

    /**
     * @var \Zend\Authentication\AuthenticationService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationService;

    /**
     * @var \ZfcRbac\Options\ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @var \ZfcRbac\Service\AuthorizationService
     */
    protected $authorizationService;

    public function setUp()
    {
        $this->rbac                  = $this->getMock('Zend\Permissions\Rbac\Rbac');
        $this->authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $this->moduleOptions         = new ModuleOptions();

        $this->authorizationService  = new AuthorizationService(
            $this->rbac,
            $this->authenticationService,
            $this->moduleOptions
        );
    }

    public function testReturnGuestRoleIfNoIdentityIsFound()
    {
        $this->authenticationService->expects($this->once())
                                    ->method('hasIdentity')
                                    ->will($this->returnValue(false));

        $this->moduleOptions->setGuestRole('guest');

        $this->assertEquals('guest', $this->authorizationService->getIdentityRoles());
    }

    public function testThrowExceptionIfWrongIdentityTypeIsReturned()
    {
        $this->authenticationService->expects($this->once())
                                    ->method('hasIdentity')
                                    ->will($this->returnValue(true));

        $this->authenticationService->expects($this->once())
                                    ->method('getIdentity')
                                    ->will($this->returnValue(new \stdClass));

        $this->setExpectedException('ZfcRbac\Exception\RuntimeException');

        $this->authorizationService->getIdentityRoles();
    }

    public function testCanReturnRolesFromIdentity()
    {
        $this->authenticationService->expects($this->once())
                                    ->method('hasIdentity')
                                    ->will($this->returnValue(true));

        $identity = $this->getMock('Zend\Permissions\Rbac\IdentityInterface');
        $identity->expects($this->once())
                 ->method('getRoles')
                 ->will($this->returnValue('myRole'));

        $this->authenticationService->expects($this->once())
                                    ->method('getIdentity')
                                    ->will($this->returnValue($identity));

        $this->assertEquals('myRole', $this->authorizationService->getIdentityRoles());
    }

    public function defaultRoleProvider()
    {
        return array(
            array(null),
            array(''),
            array(array())
        );
    }

    /**
     * @dataProvider defaultRoleProvider
     */
    public function testReturnDefaultRolesIfIdentityRolesAreEmpty($role)
    {
        $this->authenticationService->expects($this->once())
                                    ->method('hasIdentity')
                                    ->will($this->returnValue(true));

        $identity = $this->getMock('Zend\Permissions\Rbac\IdentityInterface');
        $identity->expects($this->once())
                 ->method('getRoles')
                 ->will($this->returnValue($role));

        $this->authenticationService->expects($this->once())
                                    ->method('getIdentity')
                                    ->will($this->returnValue($identity));

        $this->moduleOptions->setDefaultRole('defaultRole');

        $this->assertEquals('defaultRole', $this->authorizationService->getIdentityRoles());
    }
}