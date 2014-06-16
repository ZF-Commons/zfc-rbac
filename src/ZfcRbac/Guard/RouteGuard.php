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

namespace ZfcRbac\Guard;

use Zend\Mvc\MvcEvent;
use ZfcRbac\Exception;
use ZfcRbac\Service\AuthorizationServiceInterface;
use ZfcRbac\Service\RoleService;

/**
 * A route guard can protect a route or a hierarchy of routes (using simple wildcard pattern)
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @author  JM Leroux <jmleroux.pro@gmail.com>
 * @licence MIT
 */
class RouteGuard extends AbstractGuard
{
    use ProtectionPolicyTrait;

    /**
     * @var RoleService
     */
    protected $roleService;

    /**
     * @var AuthorizationServiceInterface
     */
    protected $authorizationService;

    /**
     * Route guard rules
     *
     * Those rules are an associative array that map a rule with one or multiple roles
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Constructor
     *
     * @param RoleService $roleService
     * @param AuthorizationServiceInterface $authorizationService
     * @param array $rules
     */
    public function __construct(
        RoleService $roleService,
        AuthorizationServiceInterface $authorizationService,
        array $rules = []
    ) {
        $this->roleService = $roleService;
        $this->authorizationService = $authorizationService;
        $this->setRules($rules);
    }

    /**
     * Set the rules (it overrides any existing rules)
     *
     * @param  array $rules
     * @return void
     */
    public function setRules(array $rules)
    {
        $this->rules = [];

        foreach ($rules as $key => $value) {
            $result = $this->parseOneRule($key, $value);

            $routePattern                              = $result['routePattern'];
            $this->rules[$routePattern]['roles']       = $result['roles'];
            $this->rules[$routePattern]['permissions'] = $result['permissions'];
        }
    }

    /**
     * @param string $key
     * @param string|array $value
     * @throws \InvalidArgumentException
     * @return string[]
     */
    private function parseOneRule($key, $value)
    {
        if (is_int($key)) {
            $routePattern = $value;
            $roles        = [];
            $permissions  = [];
        } else {
            $routePattern = $key;
            $roles        = [];
            $permissions  = [];
            if (isset($value['roles']) && isset($value['permissions'])) {
                throw new \InvalidArgumentException("You cannot use roles AND permissions for a route.");
            }
            if (!isset($value['roles']) && !isset($value['permissions'])) {
                $roles       = (array)$value;
                $permissions = [];
            } else {
                if (isset($value['roles'])) {
                    $roles = (array)$value['roles'];
                }
                if (isset($value['permissions'])) {
                    $permissions = (array)$value['permissions'];
                }
            }
        }

        return [
            'routePattern' => $routePattern,
            'roles'        => $roles,
            'permissions'  => $permissions,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();

        // check roles first
        $allowedRoles = $this->getAllowedRoles($matchedRouteName);

        if (in_array('*', (array)$allowedRoles)) {
            return true;
        }

        if (!empty($allowedRoles)) {
            return $this->roleService->matchIdentityRoles($allowedRoles);
        }

        // if no roles in rule, check permissions
        $allowedPermissions = $this->getAllowedPermissions($matchedRouteName);

        // If no rules apply, it is considered as granted or not based on the protection policy
        if (null === $allowedPermissions) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        if (in_array('*', (array)$allowedPermissions)) {
            return true;
        }

        foreach ($allowedPermissions as $permission) {
            if (!$this->authorizationService->isGranted($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $matchedRouteName
     * @return array
     */
    private function getAllowedRoles($matchedRouteName)
    {
        $allowedRoles = null;

        foreach (array_keys($this->rules) as $routeRule) {
            if (fnmatch($routeRule, $matchedRouteName, FNM_CASEFOLD)) {
                $allowedRoles = $this->rules[$routeRule]['roles'];
                break;
            }
        }

        return $allowedRoles;
    }

    /**
     * @param string $matchedRouteName
     * @return array
     */
    private function getAllowedPermissions($matchedRouteName)
    {
        $allowedPermissions = null;

        foreach (array_keys($this->rules) as $routeRule) {
            if (fnmatch($routeRule, $matchedRouteName, FNM_CASEFOLD)) {
                $allowedPermissions = $this->rules[$routeRule]['permissions'];
                break;
            }
        }

        return $allowedPermissions;
    }
}
