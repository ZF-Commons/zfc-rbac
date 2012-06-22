<?php

namespace SpiffySecurity\Service;

use InvalidArgumentException;
use RuntimeException;
use SpiffySecurity\Firewall\AbstractFirewall;
use SpiffySecurity\Identity;
use SpiffySecurity\Provider\ProviderInterface;
use SpiffySecurity\Rbac\Rbac;

class Security
{
    const ERROR_ROUTE_UNAUTHORIZED      = 'error-route-unauthorized';
    const ERROR_CONTROLLER_UNAUTHORIZED = 'error-controller-unauthorized';

    /**
     * @var \SpiffySecurity\Rbac\Rbac
     */
    protected $rbac;

    /**
     * @var array
     */
    protected $firewalls = array();

    /**
     * @var \SpiffySecurity\Identity\IdentityInterface
     */
    protected $identity;

    /**
     * @var bool
     */
    protected $loaded = false;

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
     * Returns true if the user has the role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getIdentity()->getRoles());
    }

    /**
     * Returns true if the user has the permission.
     *
     * @param $permission
     */
    public function isGranted($permission)
    {
        foreach($this->getIdentity()->getRoles() as $role) {
            if ($this->getRbac()->isGranted($role, $permission)) {
                return true;
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
        $this->firewalls[$firewall->getName()] = $firewall;
        return $this;
    }

    /**
     * @param \SpiffySecurity\Provider\ProviderInterface $provider
     * @param bool $force
     * @return \SpiffySecurity\Service\Security
     */
    public function addProvider(ProviderInterface $provider, $force = false)
    {
        if ($this->loaded && !$force) {
            throw new RuntimeException(
                'Adding new providers after initialization has been disabled. You can override this' .
                'behaviour by setting $force = true when you add a provider.'
            );
        }
        $this->providers[] = $provider;
        return $this;
    }

    /**
     * @return \SpiffySecurity\Identity\IdentityInterface
     */
    public function getIdentity()
    {
        if (null === $this->identity) {
            $this->setIdentity();
        }
        return $this->identity;
    }

    /**
     * @param string|null|\SpiffySecurity\Identity\IdentityInterface $identity
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
        $this->load();

        return $this->rbac;
    }

    /**
     * @return \SpiffySecurity\Service\SecurityOptions
     */
    public function options()
    {
        return $this->options;
    }

    /**
     * Reset to original state.
     */
    protected function reset()
    {
        $this->rbac    = null;
        $this->loaded = false;
    }

    /**
     * Load acl.
     *
     * @return void
     */
    protected function load()
    {
        if ($this->loaded) {
            return;
        }

        $rbac = new Rbac;

        // add roles from providers
        foreach($this->providers as $provider) {
            /** @var $provider ProviderInterface */
            $provider->load($rbac);
        }

        $this->rbac = $rbac;
    }
}