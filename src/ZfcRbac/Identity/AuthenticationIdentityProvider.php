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

namespace ZfcRbac\Identity;

use Zend\Authentication\AuthenticationService;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Exception;

/**
 * This provider uses the Zend authentication service to fetch the identity
 */
class AuthenticationIdentityProvider implements IdentityProviderInterface
{
    /**
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var string
     */
    protected $guestRole;

    /**
     * Constructor
     *
     * @param AuthenticationService $authenticationService
     * @param string                $guestRole
     */
    public function __construct(AuthenticationService $authenticationService, $guestRole = '')
    {
        $this->authenticationService = $authenticationService;
        $this->guestRole             = (string) $guestRole;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        if (!$this->authenticationService->hasIdentity()) {
            return array($this->guestRole);
        }

        $identity = $this->authenticationService->getIdentity();

        if (!$identity instanceof IdentityInterface) {
            return $this->guestRole;
        }

        return (array) $identity->getRoles();
    }
}
