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

namespace ZfcRbac\Service;

use Zend\Permissions\Rbac\Rbac;
use ZfcRbac\Exception;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * Authorization service is a simple service that internally uses a Rbac container
 */
class AuthorizationService 
{
    /**
     * @var Rbac
     */
    protected $rbac;

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * Constructor
     *
     * @param Rbac                      $rbac
     * @param IdentityProviderInterface $identityProvider
     */
    public function __construct(Rbac $rbac, IdentityProviderInterface $identityProvider)
    {
        $this->rbac             = $rbac;
        $this->identityProvider = $identityProvider;
    }

    /**
     * Get the Rbac container
     *
     * @return Rbac
     */
    public function getRbac()
    {
        return $this->rbac;
    }

    /**
     * Get the identity provider
     *
     * @return IdentityProviderInterface
     */
    public function getIdentityProvider()
    {
        return $this->identityProvider;
    }

    /**
     * Check if the permission is granted to the current identity
     *
     * @param  string                                                  $permission
     * @param  callable|\Zend\Permissions\Rbac\AssertionInterface|null $assertion
     * @return bool
     */
    public function isGranted($permission, $assertion = null)
    {
        $roles = (array) $this->identityProvider->getIdentityRoles();

        foreach ($roles as $role) {
            // @TODO: what happen if an identity has multiple role, and that one role tells "yes", and the other
            // tells "no" ? What is the standard behaviour for this?
            if ($this->rbac->isGranted($role, $permission, $assertion)) {
                return true;
            }
        }

        return false;
    }
}
