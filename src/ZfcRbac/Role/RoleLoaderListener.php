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
use Zend\Permissions\Rbac\Role;
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

        foreach ($roles as $key => $value) {
            if ($value instanceof RoleInterface) {
                $rbac->addRole($value);
                continue;
            }

            $roleName    = $key;
            $children    = isset($value['children']) ? $value['children'] : [];
            $permissions = isset($value['permissions']) ? $value['permissions'] : [];

            if ($rbac->hasRole($roleName)) {
                // @TODO: throw exception
            }

            $role = new Role($roleName);
            $rbac->addRole($role);

            foreach ($children as $child) {
                if (!$rbac->hasRole($child)) {
                    $rbac->addRole($child);
                }

                $role->addChild($rbac->getRole($child));
            }

            foreach ($permissions as $permission) {
                $role->addPermission($permission);
            }
        }
    }
}
