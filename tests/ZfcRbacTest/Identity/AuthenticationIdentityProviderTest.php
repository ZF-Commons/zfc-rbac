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

namespace ZfcRbacTest\Identity;

use ZfcRbac\Identity\AuthenticationIdentityProvider;

/**
 * @covers \ZfcRbac\Identity\AuthenticationIdentityProvider
 */
class AuthenticationIdentityProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuthenticationIdentityProvider
     */
    protected $identityProvider;

    /**
     * @var \Zend\Authentication\AuthenticationService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authenticationService;

    public function setUp()
    {
        $this->authenticationService = $this->getMock('Zend\Authentication\AuthenticationService');
        $this->identityProvider = new AuthenticationIdentityProvider($this->authenticationService, 'guest');
    }

    public function testReturnGuestRoleIfNoIdentityIsFound()
    {
        $this->authenticationService->expects($this->once())
                                    ->method('hasIdentity')
                                    ->will($this->returnValue(false));

        $this->assertEquals(array('guest'), $this->identityProvider->getIdentityRoles());
    }

    public function testCanReturnRolesFromIdentity()
    {
        $this->authenticationService->expects($this->once())
                                    ->method('hasIdentity')
                                    ->will($this->returnValue(true));

        $identity = $this->getMock('ZfcRbac\Identity\IdentityInterface');
        $identity->expects($this->once())
                 ->method('getRoles')
                 ->will($this->returnValue('myRole'));

        $this->authenticationService->expects($this->once())
                                    ->method('getIdentity')
                                    ->will($this->returnValue($identity));

        $this->assertEquals(array('myRole'), $this->identityProvider->getIdentityRoles());
    }
}
