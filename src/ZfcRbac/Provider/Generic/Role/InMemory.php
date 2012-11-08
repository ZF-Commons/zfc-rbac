<?php

namespace ZfcRbac\Provider\Generic\Role;

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
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $events->attach(Event::EVENT_LOAD_ROLES, array($this, 'loadRoles'));
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
     * Load roles into RBAC on load.
     *
     * @param Event $e
     */
    public function loadRoles(Event $e)
    {
        $rbac   = $e->getRbac();
        $roles  = $this->options->getRoles();
        $result = array();

        foreach($roles as $role => $parents) {
            if (is_numeric($role)) {
                $role    = $parents;
                $parents = array();
            }
            if (empty($parents)) {
                $result[0][] = $role;
            }
            foreach($parents as $parent) {
                $result[$parent][] = $role;
            }
        }

        $this->recursiveRoles($rbac, $result);
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return mixed
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        return new InMemory($spec);
    }
}
