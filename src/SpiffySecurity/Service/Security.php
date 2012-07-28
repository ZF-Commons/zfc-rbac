<?php

namespace SpiffySecurity\Service;

use InvalidArgumentException;
use RuntimeException;
use SpiffySecurity\Exception;
use SpiffySecurity\Firewall\AbstractFirewall;
use SpiffySecurity\Identity;
use SpiffySecurity\Provider\Event;
use SpiffySecurity\Provider\ProviderInterface;
use SpiffySecurity\Rbac\Rbac;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;

class Security
{
    const ERROR_ROUTE_UNAUTHORIZED      = 'error-route-unauthorized';
    const ERROR_CONTROLLER_UNAUTHORIZED = 'error-controller-unauthorized';

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var \SpiffySecurity\Rbac\Rbac
     */
    protected $rbac;

    /**
     * @var array
     */
    protected $firewalls = array();

    /**
     * @var Identity\IdentityInterface
     */
    protected $identity;

    /**
     * @var array
     */
    protected $providers = array();

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->options = new SecurityOptions($options);
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return Security
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(array(
            __CLASS__,
            get_called_class(),
        ));
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events) {
            $this->setEventManager(new EventManager());
        }
        return $this->events;
    }

    /**
     * Returns true if the user has the role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->getRbac()->hasRole($role);
    }

    /**
     * Returns true if the user has the permission.
     *
     * @param $permission
     */
    public function isGranted($permission)
    {
        $rbac = $this->getRbac();

        foreach($this->getIdentity()->getRoles() as $role) {
            $event = new Event;
            $event->setRole($role)
                  ->setPermission($permission)
                  ->setRbac($rbac);

            $response = $this->getEventManager()->trigger(Event::EVENT_IS_GRANTED, $event);
            foreach($response as $result) {
                if ($result === true) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Access to firewalls by name.
     *
     * @param string $name
     * @return \SpiffySecurity\Firewall\AbstractFirewall
     */
    public function getFirewall($name)
    {
        if (!isset($this->firewalls[$name])) {
            throw new InvalidArgumentException(sprintf(
                'No firewall with name "%s" is registered',
                $name
            ));
        }

        return $this->firewalls[$name];
    }

    /**
     * @param \SpiffySecurity\Firewall\AbstractFirewall $firewall
     * @return \SpiffySecurity\Service\Security
     */
    public function addFirewall(AbstractFirewall $firewall)
    {
        if (isset($this->firewalls[$firewall->getName()])) {
            throw new InvalidArgumentException(sprintf(
                'Firewall with name "%s" is already registered',
                $firewall->getName()
            ));
        }

        $firewall->setSecurity($this);
        $this->firewalls[$firewall->getName()] = $firewall;
        return $this;
    }

    /**
     * @param ProviderInterface $provider
     * @return \SpiffySecurity\Service\Security
     */
    public function addProvider(ProviderInterface $provider)
    {
        $provider->attachListeners($this->getEventManager());

        $this->providers[] = $provider;
        return $this;
    }

    /**
     * @return Identity\IdentityInterface
     */
    public function getIdentity()
    {
        if (null === $this->identity) {
            $this->setIdentity();
        }
        return $this->identity;
    }

    /**
     * @param string|null|\Identity\IdentityInterface $identity
     * @return \SpiffySecurity\Service\Security
     */
    public function setIdentity($identity = null)
    {
        if (is_string($identity)) {
            $identity = new Identity\StandardIdentity($identity);
        } else if (is_null($identity)) {
            $identity = new Identity\StandardIdentity($this->options()->getAnonymousRole());
        } else if (!$identity instanceof Identity\IdentityInterface) {
            throw new InvalidArgumentException(
                'Identity must be null, a string, or an instance of SpiffySecurity\Identity\IdentityInterface'
            );
        }

        $this->identity = $identity;
        return $this;
    }

    /**
     * @return \SpiffySecurity\Rbac\Rbac
     */
    public function getRbac()
    {
        if (null === $this->rbac) {
            $this->rbac = new Rbac;

            $event = new Event;
            $event->setRbac($this->rbac);

            $this->getEventManager()->trigger(Event::EVENT_ON_LOAD, $event);

            $this->getEventManager()->trigger(Event::EVENT_LOAD_ROLES, $event);
            $this->getEventManager()->trigger(Event::EVENT_LOAD_PERMISSIONS, $event);
        }
        echo '<pre>';
        print_r($this->rbac);
        echo '</pre>';
        exit;
        return $this->rbac;
    }

    /**
     * @return \SpiffySecurity\Service\SecurityOptions
     */
    public function options()
    {
        return $this->options;
    }
}