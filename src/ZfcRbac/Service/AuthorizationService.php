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

use Rbac\Rbac;
use ZfcRbac\Assertion\AssertionPluginManager;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Exception;

/**
 * Authorization service is a simple service that internally uses Rbac to check if identity is
 * granted a permission
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class AuthorizationService
{
    /**
     * @var Rbac
     */
    protected $rbac;

    /**
     * @var RoleService
     */
    protected $roleService;
    
    /**
     * @var AssertionPluginManager
     */
    protected $assertionPluginManager;
    
    /**
     * @var array
     */
    protected $assertionsMap;

    /**
     * Constructor
     *
     * @param RoleService $roleService
     */
    public function __construct(
        RoleService $roleService,
        AssertionPluginManager $assertionPluginManager,
        array $assertionsMap = []
    ) {
        $this->rbac                   = new Rbac();
        $this->roleService            = $roleService;
        $this->assertionPluginManager = $assertionPluginManager;
        $this->assertionsMap          = $assertionsMap;
    }

    /**
     * Check if the permission is granted to the current identity
     *
     * @param  string $permission
     * @param  mixed  $context
     * @return bool
     * @throws Exception\InvalidArgumentException If an invalid assertion is passed
     */
    public function isGranted($permission, $context = null)
    {
        $roles = $this->roleService->getIdentityRoles();

        if (empty($roles)) {
            return false;
        }

        /* @var \Rbac\Role\RoleInterface $role */
        foreach ($roles as $role) {
            // If we are granted, we also check the assertion as a second-pass
            if ($this->rbac->isGranted($role, $permission)) {
                $assertion = isset($this->assertionsMap[$permission]) ? $this->assertionsMap[$permission] : null;
                return $assertion ? $this->assert($assertion, $context) : true;
            }
        }

        return false;
    }

    /**
     * @param  string|callable|AssertionInterface $assertion
     * @param  mixed           $context
     * @return bool
     * @throws Exception\InvalidArgumentException
     */
    protected function assert($assertion, $context = null)
    {
        $identity  = $this->roleService->getIdentity();
        
        if (is_callable($assertion)) {
            return $assertion($identity, $context);
        } elseif ($assertion instanceof AssertionInterface) {
            return $assertion->assert($identity, $context);
        } elseif (is_string($assertion)) {
            $assertion = $this->assertionPluginManager->get($assertion);
            return $assertion->assert($identity, $context);
        }
        
        throw new Exception\InvalidArgumentException(sprintf(
            'Assertion must be callable, string or implement ZfcRbac\Assertion\AssertionInterface, "%s" given',
            is_object($assertion) ? get_class($assertion) : gettype($assertion)
        ));
    }
}
