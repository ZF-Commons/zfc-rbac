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
use Zend\Permissions\Rbac\RoleInterface;
use ZfcRbac\Exception;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * A route guard can protect a route or a hierarchy of routes (using regexes)
 */
class RouteGuard implements GuardInterface
{
    /**
     * Authorization service that is used to fetch the current roles
     *
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * Route guard rules
     *
     * Those rules are an associative array that map a regex rule with one or multiple roles
     *
     * @var array
     */
    protected $rules = array();

    /**
     * Constructor
     *
     * @param IdentityProviderInterface $identityProvider
     * @param array                     $rules
     */
    public function __construct(IdentityProviderInterface $identityProvider, array $rules = array())
    {
        $this->identityProvider = $identityProvider;

        if (!empty($rules)) {
            $this->setRules($rules);
        }
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
            $routeRegex = is_int($key) ? $value : $key;
            $roles      = is_int($key) ? array() : (array) $value;

            $this->rules[$routeRegex] = $roles;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event)
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();
        $roles            = (array) $this->identityProvider->getIdentityRoles();

        $allowedRoles = array();
        $found        = false;

        foreach (array_keys($this->rules) as $routeRegex) {
            $result = preg_match('/' . preg_quote($routeRegex, '/') . '/', $matchedRouteName);

            if (false === $result) {
                throw new Exception\RuntimeException(sprintf(
                    'Unable to test regex: "%s"',
                    $routeRegex
                ));
            } elseif ($result) {
                $allowedRoles = $this->rules[$routeRegex];
                $found        = true;

                break;
            }
        }

        // If no rules apply, it is considered as valid
        if (!$found) {
            return true;
        }

        // Iterate through each roles of the identity, and check if we have a match
        foreach ($roles as $role) {
            $role = $role instanceof RoleInterface ? $role->getName() : (string) $role;

            if (in_array($role, $allowedRoles)) {
                return true;
            }
        }

        return false;
    }
}