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

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use ZfcRbac\Service\AuthorizationService;

/**
 * A controller guard can protect a controller and a set of actions
 */
class ControllerGuard extends AbstractGuard
{
    use ProtectionPolicyTrait;

    /**
     * Controller guard rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Constructor
     *
     * @param AuthorizationService $authorizationService
     * @param array                $rules
     */
    public function __construct(AuthorizationService $authorizationService, array $rules = [])
    {
        $this->authorizationService = $authorizationService;
        $this->setRules($rules);
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        // We can't actually listen to the EVENT_ROUTE event, because user can call the "forward" method
        // on the controller, and this bypasses the routing mechanism

        $sharedManager = $events->getSharedManager();
        $sharedManager->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, [$this, 'onResult'], 5);
    }


    /**
     * Set the rules
     *
     * A controller rule is made the following way:
     *
     * [
     *     'controller' => 'ControllerName',
     *     'actions'    => []/string
     *     'roles'      => []/string
     * ]
     *
     * @param  array $rules
     * @return void
     */
    public function setRules(array $rules)
    {
        $this->rules = [];

        foreach ($rules as $rule) {
            $controller = strtolower($rule['controller']);
            $actions    = isset($rule['actions']) ? (array) $rule['actions'] : [];
            $roles      = (array) $rule['roles'];

            if (empty($actions)) {
                $this->rules[$controller][0] = $roles;
                continue;
            }

            foreach ($actions as $action) {
                $this->rules[$controller][strtolower($action)] = $roles;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event)
    {
        $routeMatch = $event->getRouteMatch();
        $controller = strtolower($routeMatch->getParam('controller'));
        $action     = strtolower($routeMatch->getParam('action'));

        // If no rules apply, it is considered as granted or not based on the protection policy
        if (!isset($this->rules[$controller])) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        // Algorithm is as follow: we first check if there is an exact match (controller + action), if not
        // we check if there are rules set globally for the whole controllers (see the index "0"), and finally
        // if nothing is matched, we fallback to the protection policy logic

        if (isset($this->rules[$controller][$action])) {
            $allowedRoles = $this->rules[$controller][$action];
        } elseif (isset($this->rules[$controller][0])) {
            $allowedRoles = $this->rules[$controller][0];
        } else {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        if (in_array('*', $allowedRoles)) {
            return true;
        }

        return $this->authorizationService->satisfyIdentityRoles($allowedRoles);
    }
}
