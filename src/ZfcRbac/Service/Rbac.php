<?php

namespace ZfcRbac\Service;

use Closure;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Permissions\Rbac\Rbac as ZendRbac;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Exception;
use ZfcRbac\Firewall\AbstractFirewall;
use ZfcRbac\Identity;
use ZfcRbac\Provider\Event;
use ZfcRbac\Provider\AbstractProvider;

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
     * @var RbacOptions
     */
    protected $options;


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
     * @param null|Closure|AssertionInterface $assert
     * @throws InvalidArgumentException
     * @return bool
     */
    public function isGranted($permission, $assert = null)
    {
        if (!is_string($permission)) {
            throw new InvalidArgumentException('isGranted() expects a string for permission');
        }

        $rbac = $this->getRbac();

        if ($assert) {
            if ($assert instanceof AssertionInterface) {
                if (!$assert->assert($this)) {
                    return false;
                }
            } elseif (is_callable($assert)) {
                if (!$assert($this)) {
                    return false;
                }
            } else {
                throw new InvalidArgumentException(
                    'Assertions must be a Callable or an instance of ZfcRbac\AssertionInterface'
                );
            }
        }

        foreach($this->getIdentity()->getRoles() as $role) {
            if (!$this->hasRole($role)) {
                continue;
            }

            $event = new Event;
            $event->setRole($role)
                  ->setPermission($permission)
                  ->setRbac($rbac);

            $this->getEventManager()->trigger(Event::EVENT_IS_GRANTED, $event);
            if ($rbac->isGranted($role, $permission)) {
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
     * @param AbstractProvider $provider
     * @return Rbac
     */
    public function addProvider(AbstractProvider $provider)
    {
        $provider->attach($this->getEventManager());
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
     * @param  string|null|AuthenticationService|Identity\IdentityInterface $identity
     * @throws InvalidArgumentException
     * @return Rbac
     */
    public function setIdentity($identity = null)
    {
        if ($identity instanceof AuthenticationService) {
            $identity = $identity->getIdentity();
        }

        if (is_string($identity)) {
            $identity = new Identity\StandardIdentity($identity);
        } elseif (is_null($identity)) {
            $identity = new Identity\StandardIdentity($this->getOptions()->getAnonymousRole());
        } elseif (!$identity instanceof Identity\IdentityInterface) {
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
    public function getOptions()
    {
        return $this->options;
    }
}