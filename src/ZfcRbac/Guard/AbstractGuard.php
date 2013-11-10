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
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Mvc\MvcEvent;
use ZfcRbac\Exception;
use ZfcRbac\Service\AuthorizationService;

/**
 * Abstract guard that registers on the "onRoute" event
 */
abstract class AbstractGuard implements GuardInterface
{
    use ListenerAggregateTrait;

    /**
     * Event priority for the onRoute event
     */
    const EVENT_PRIORITY = -100;

    /**
     * @var AuthorizationService
     */
    protected $authorizationService;

    /**
     * @var string
     */
    protected $protectionPolicy = self::POLICY_DENY;

    /**
     * Constructor
     *
     * @param AuthorizationService $authorizationService
     */
    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), static::EVENT_PRIORITY);
    }

    /**
     * Set the protection policy
     *
     * @param  string $protectionPolicy
     * @return void
     */
    public function setProtectionPolicy($protectionPolicy)
    {
        $this->protectionPolicy = (string) $protectionPolicy;
    }

    /**
     * Get the protection policy
     *
     * @return string
     */
    public function getProtectionPolicy()
    {
        return $this->protectionPolicy;
    }

    /**
     * @private
     * @param  MvcEvent $event
     * @return void
     */
    public function onRoute(MvcEvent $event)
    {
        if ($this->isGranted($event)) {
            $event->setParam('guard-result', self::GUARD_AUTHORIZED);
            return;
        }

        $event->setParam('guard-result', self::GUARD_UNAUTHORIZED);
        $event->setParam('exception', new Exception\UnauthorizedException(
            'You are not authorized to access this resource'
        ));

        $event->stopPropagation(true);

        $application  = $event->getApplication();
        $eventManager = $application->getEventManager();

        $eventManager->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
    }

    /**
     * Load a rule inside the Rbac container
     *
     * This allows to only add one permission relative to the route guard/controller guard, instead
     * of adding permissions for all the rules
     *
     * @param  array  $roles
     * @param  string $permission
     * @return void
     */
    protected function loadRule(array $roles, $permission)
    {
        $rbac = $this->authorizationService->getRbac();

        foreach ($roles as $role) {
            $rbac->getRole($role)->addPermission($permission);
        }
    }
}
