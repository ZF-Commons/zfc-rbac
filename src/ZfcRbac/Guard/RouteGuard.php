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
use ZfcRbac\Service\AuthorizationService;

/**
 * A route guard can protect a route or a hierarchy of routes (using simple wildcard pattern)
 */
class RouteGuard extends AbstractGuard
{
    /**
     * Rule prefix that is used to avoid conflicts in the Rbac container
     *
     * Rules will be added to the Rbac container using the following syntax: __route__.$routeRule
     */
    const RULE_PREFIX = '__route__';

    /**
     * Route guard rules
     *
     * Those rules are an associative array that map a rule with one or multiple roles
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Constructor
     *
     * @param AuthorizationService $authorizationService
     * @param array                $rules
     */
    public function __construct(AuthorizationService $authorizationService, array $rules = array())
    {
        parent::__construct($authorizationService);
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
        $this->rules = array();
        $this->addRules($rules);
    }

    /**
     * Add route rules
     *
     * @param  array $rules
     * @return void
     */
    public function addRules(array $rules)
    {
        foreach ($rules as $key => $value) {
            if (is_int($key)) {
                $routeRegex = $value;
                $roles      = array();
            } else {
                $routeRegex = $key;
                $roles      = (array) $value;
            }

            $this->rules[$routeRegex] = $roles;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();

        $allowedRoles = array();
        $permission   = null;

        foreach (array_keys($this->rules) as $routeRule) {
            if (fnmatch($routeRule, $matchedRouteName, FNM_CASEFOLD)) {
                $allowedRoles = $this->rules[$routeRule];
                $permission   = self::RULE_PREFIX . '.' . $routeRule;

                break;
            }
        }

        if (in_array('*', $allowedRoles)) {
            return true;
        }

        // If no rules apply, it is considered as granted or not based on the protection policy
        if (empty($permission)) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        // Load the needed permission inside the container
        $this->loadRule($allowedRoles, $permission);

        return $this->authorizationService->isGranted($permission);
    }
}
