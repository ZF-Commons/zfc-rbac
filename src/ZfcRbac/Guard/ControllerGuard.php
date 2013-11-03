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

use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use Zend\Permissions\Rbac\RoleInterface;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * A controller guard can protect a controller and a set of actions
 */
class ControllerGuard extends AbstractGuard
{
    /**
     * Authorization service that is used to fetch the current roles
     *
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * Controller guard rules
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
     * Add controller rules
     *
     * A controller rule is made the following way:
     *
     * array(
     *      'controller' => 'ControllerName',
     *      'actions'    => array()/string
     *      'roles'      => array()/string
     * )
     *
     * @param  array $rules
     * @return void
     */
    public function addRules(array $rules)
    {
        foreach ($rules as $rule) {
            $controller = $rule['controller'];
            $actions    = isset($rule['actions']) ? (array) $rule['actions'] : array();
            $roles      = (array) $rule['roles'];

            if (empty($actions)) {
                $this->rules[$controller] = $roles;
                continue;
            }

            foreach ($actions as $action) {
                $this->rules[$controller][$action] = $roles;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event)
    {
        $controller = $event->getRouteMatch()->getParam('controller');
        $action     = $event->getRouteMatch()->getParam('action');

        // Authorize if controller is not in the list
        if (!isset($this->rules[$controller])) {
            return true;
        }

        if (isset($this->rules[$controller][$action])) {
            $allowedRoles = $this->rules[$controller][$action];
        } else {
            $allowedRoles = $this->rules[$controller];
        }

        $roles = (array) $this->identityProvider->getIdentityRoles();

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