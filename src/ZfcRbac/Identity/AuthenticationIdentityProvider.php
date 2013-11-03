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
use Zend\Permissions\Rbac\IdentityInterface;
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
     * @var string
     */
    protected $defaultRole;

    /**
     * Constructor
     *
     * @param AuthenticationService $authenticationService
     * @param string                $guestRole
     * @param string                $defaultRole
     */
    public function __construct(AuthenticationService $authenticationService, $guestRole = '', $defaultRole = '')
    {
        $this->authenticationService = $authenticationService;
        $this->guestRole             = (string) $guestRole;
        $this->defaultRole           = (string) $defaultRole;
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentityRoles()
    {
        if (!$this->authenticationService->hasIdentity()) {
            return $this->guestRole;
        }

        $identity = $this->authenticationService->getIdentity();

        if (!$identity instanceof IdentityInterface) {
            throw new Exception\RuntimeException(sprintf(
                'ZfcRbac identities must implement ZfcRbac\Permissions\Rbac\IdentityInterface, "%s" given',
                is_object($identity) ? get_class($identity) : gettype($identity)
            ));
        }

        $roles = $identity->getRoles();

        if (empty($roles)) {
            return $this->defaultRole;
        }

        return $roles;
    }
}