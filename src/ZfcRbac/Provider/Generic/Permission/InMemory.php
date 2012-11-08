<?php

namespace ZfcRbac\Provider\Generic\Permission;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Provider\AbstractProvider;
use ZfcRbac\Provider\Event;

class InMemory extends AbstractProvider
{
    /**
     * @var InMemoryOptions
     */
    protected $options;

    /**
     * @param array $spec
     */
    public function __construct(array $spec = array())
    {
        $this->options = new InMemoryOptions($spec);
    }

    /**
     * Attach to the listeners.
     *
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(Event::EVENT_LOAD_PERMISSIONS, array($this, 'loadPermissions'));
    }

    /**
     * @param EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        $events->detach($this);
    }

    /**
     * Load permissions into roles.
     *
     * @param  Event $e
     * @return void
     */
    public function loadPermissions(Event $e)
    {
        $rbac = $e->getRbac();

        foreach($this->options->getPermissions() as $role => $permissions) {
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
