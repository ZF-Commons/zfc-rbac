<?php

namespace ZfcRbac\Service;

use Closure;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use ZfcRbac\Exception;
use ZfcRbac\Firewall\AbstractFirewall;
use ZfcRbac\Identity;
use ZfcRbac\Provider\Event;
use ZfcRbac\Provider\ProviderInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Permissions\Rbac\AssertionInterface;
use Zend\Permissions\Rbac\Rbac as ZendRbac;

class Rbac
{
    const ERROR_ROUTE_UNAUTHORIZED      = 'error-route-unauthorized';
    const ERROR_CONTROLLER_UNAUTHORIZED = 'error-controller-unauthorized';

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * @var \Zend\Permissions\Rbac\Rbac
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
        $this->options = new RbacOptions($options);
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return Rbac
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
     * Returns true if the user has the role (can pass an array).
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
        if (!$this->getIdentity()) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = array($roles);
        }

        $rbac = $this->getRbac();

        // Have to iterate and load roles to verify that parents are loaded.
        // If it wasn't for inheritance we could just check the getIdentity()->getRoles() method.
        foreach($roles as $role) {
            foreach((array) $this->getIdentity()->getRoles() as $userRole) {
                $event = new Event;
                $event->setRole($userRole)
                      ->setRbac($rbac);

                $this->getEventManager()->trigger(Event::EVENT_HAS_ROLE, $event);

                if (!$this->getRbac()->hasRole($role)) {
                    continue;
                }

                // Fastest - do they match directly?
                if ($userRole == $role) {
                    return true;
                }

                // Last resort - check children from rbac.
                $it = new RecursiveIteratorIterator($rbac->getRole($userRole), RecursiveIteratorIterator::CHILD_FIRST);
                foreach($it as $leaf) {
                    if ($leaf->getName() == $role) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Returns true if the user has the permission.
     *
     * @param string                          $permission
     * @param null|Closure|AssertionInterface $assertion
     * @throws InvalidArgumentException
     * @return bool
     */
    public function isGranted($permission, $assertion = null)
    {
        $rbac = $this->getRbac();

        if ($assertion) {
            if ($assertion instanceof AssertionInterface) {
                if (!$assertion->assert($this)) {
                    return false;
                }
            } else if (is_callable($assertion)) {
                if (!$assertion($this)) {
                    return false;
                }
            } else {
                throw new InvalidArgumentException(
                    'Assertions must be a Closure or an instance of ZfcRbac\AssertionInterface'
                );
            }
        }

        foreach($this->getIdentity()->getRoles() as $role) {
            $event = new Event;
            $event->setRole($role)
                  ->setPermission($permission)
                  ->setRbac($rbac);

            $this->getEventManager()->trigger(Event::EVENT_IS_GRANTED, $event);
            if ($rbac->getRole($role)->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Access to firewalls by name.
     *
     * @param string $name
     * @throws InvalidArgumentException
     * @return AbstractFirewall
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
     * @param  AbstractFirewall $firewall
     * @throws InvalidArgumentException
     * @return Rbac
     */
    public function addFirewall(AbstractFirewall $firewall)
    {
        if (isset($this->firewalls[$firewall->getName()])) {
            throw new InvalidArgumentException(sprintf(
                'Firewall with name "%s" is already registered',
                $firewall->getName()
            ));
        }

        $firewall->setRbac($this);
        $this->firewalls[$firewall->getName()] = $firewall;

        return $this;
    }

    /**
     * @param ProviderInterface $provider
     * @return Rbac
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
     * @param  string|null|Identity\IdentityInterface $identity
     * @return Rbac
     */
    public function setIdentity($identity = null)
    {
        if (is_string($identity)) {
            $identity = new Identity\StandardIdentity($identity);
        } else if (is_null($identity)) {
            $identity = new Identity\StandardIdentity($this->options()->getAnonymousRole());
        } else if (!$identity instanceof Identity\IdentityInterface) {
            throw new InvalidArgumentException(
                'Identity must be null, a string, or an instance of ZfcRbac\Identity\IdentityInterface'
            );
        }

        $this->identity = $identity;
        return $this;
    }

    /**
     * @return ZendRbac
     */
    public function getRbac()
    {
        if (null === $this->rbac) {
            $this->rbac = new ZendRbac();

            $event = new Event;
            $event->setRbac($this->rbac);

            $this->getEventManager()->trigger(Event::EVENT_LOAD_ROLES, $event);
            $this->getEventManager()->trigger(Event::EVENT_LOAD_PERMISSIONS, $event);
        }
        return $this->rbac;
    }

    /**
     * @return RbacOptions
     */
    public function options()
    {
        return $this->options;
    }
}