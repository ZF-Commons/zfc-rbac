<?php

namespace ZfcRbac\Provider\Generic\Permission;

use ZfcRbac\Provider\AbstractProvider;
use ZfcRbac\Provider\Event;
use ZfcRbac\Provider\ProviderInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class InMemory implements ProviderInterface
{
    protected $options;

    public function __construct(array $spec = array())
    {
        $this->options = new InMemoryOptions($spec);
    }

    /**
     * Attach to the listeners.
     *
     * @param \Zend\EventManager\EventManager $events
     * @return void
     */
    public function attachListeners(EventManager $events)
    {
        $events->attach(Event::EVENT_LOAD_PERMISSIONS, array($this, 'loadPermissions'));
    }

    /**
     * Load permissions into roles.
     *
     * @param Event $rbac
     */
    public function loadPermissions(Event $e)
    {
        $rbac = $e->getRbac();

        foreach((array) $this->options->getPermissions() as $role => $permissions) {
            foreach((array) $permissions as $permission) {
                $rbac->getRole($role)->addPermission($permission);
            }
        }
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return InMemory
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        return new InMemory($spec);
    }
}
