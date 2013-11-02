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

use Zend\Authentication\AuthenticationService;
use Zend\Permissions\Rbac\IdentityInterface;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\RoleInterface;
use ZfcRbac\Exception;
use ZfcRbac\Options\ModuleOptions;

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
     * @var AuthenticationService
     */
    protected $authenticationService;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * Constructor
     *
     * @param Rbac                  $rbac
     * @param AuthenticationService $authenticationService
     * @param ModuleOptions         $options
     */
    public function __construct(Rbac $rbac, AuthenticationService $authenticationService, ModuleOptions $options)
    {
        $this->rbac                  = $rbac;
        $this->authenticationService = $authenticationService;
        $this->moduleOptions         = $options;
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
     * Get the roles of the current identity
     *
     * @return string|string[]|RoleInterface|RoleInterface[]
     * @throws Exception\RuntimeException If the authentication service does not return a valid identity object
     */
    public function getIdentityRoles()
    {
        if (!$this->authenticationService->hasIdentity()) {
            return $this->moduleOptions->getGuestRole();
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
            return $this->moduleOptions->getDefaultRole();
        }

        return $roles;
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
        $roles = (array) $this->getIdentityRoles();

        foreach ($roles as $role) {
            if ($this->rbac->isGranted($role, $permission, $assertion)) {
                return true;
            }
        }

        return false;
    }
}
