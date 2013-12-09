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

namespace ZfcRbac\Role;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Permissions\Rbac\RoleInterface;
use ZfcRbac\Service\RbacEvent;

/**
 * Simple listener that is used to load roles
 */
class RoleLoaderListener extends AbstractListenerAggregate
{
    /**
     * @var RoleProviderInterface
     */
    protected $roleProvider;

    /**
     * Constructor
     *
     * @param RoleProviderInterface $roleProvider
     */
    public function __construct(RoleProviderInterface $roleProvider)
    {
        $this->roleProvider = $roleProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(RbacEvent::EVENT_LOAD_ROLES, [$this, 'onLoadRoles']);
    }

    /**
     * Inject the loaded roles inside the Rbac container
     *
     * @private
     * @param  RbacEvent $event
     * @return void
     */
    public function onLoadRoles(RbacEvent $event)
    {
        $rbac  = $event->getRbac();
        $roles = $this->roleProvider->getRoles($event);

        // NOTE: as you can see, even if we have a RoleInterface, we create a new role from it instead
        // of adding it. The reason is because this may be a Doctrine entity, and it can create a lot
        // of edge cases that are hard to debug

        foreach ($roles as $key => $value) {
            $parent = null;

            if ($value instanceof RoleInterface) {
                $role   = $value->getName();
                $parent = $value->getParent()->getName();
            } elseif (is_int($key)) {
                $role = $value;
            } else {
                $role   = $key;
                $parent = $value;
            }

            // Because multiple providers may have the same role, we first need to check if it exists
            // before adding it
            if (!$rbac->hasRole($role)) {
                $rbac->addRole($role, $parent);
            }
        }
    }
}
