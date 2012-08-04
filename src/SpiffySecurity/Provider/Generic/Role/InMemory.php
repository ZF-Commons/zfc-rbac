<?php

namespace SpiffySecurity\Provider\Generic\Role;

use SpiffySecurity\Provider\AbstractProvider;
use SpiffySecurity\Provider\Event;
use SpiffySecurity\Provider\ProviderInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class InMemory extends AbstractProvider implements ProviderInterface
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
        $events->attach(Event::EVENT_LOAD_ROLES, array($this, 'loadRoles'));
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

        foreach((array) $roles as $role => $parents) {
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
