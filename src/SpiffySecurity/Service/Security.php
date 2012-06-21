<?php

namespace SpiffySecurity\Service;

use InvalidArgumentException;
use SpiffySecurity\Firewall\AbstractFirewall;
use SpiffySecurity\Identity;
use SpiffySecurity\Provider\ProviderInterface;
use Zend\Acl\Acl;

class Security
{
    const ERROR_ROUTE_UNAUTHORIZED      = 'error-route-unauthorized';
    const ERROR_CONTROLLER_UNAUTHORIZED = 'error-controller-unauthorized';

    /**
     * @var \Zend\Acl\Acl
     */
    protected $acl;

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
     * Checks if access is granted to the specified role.
     *
     * @param string $role
     * @return bool
     */
    public function isGranted($role)
    {
        return in_array($role, $this->getIdentity()->getRoles());
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
     * @return \SpiffySecurity\Service\Security
     */
    public function addProvider(ProviderInterface $provider)
    {
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
     * @return \Zend\Acl\Acl
     */
    public function getAcl()
    {
        $this->load();

        return $this->acl;
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
        $this->acl    = null;
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

        $acl = new Acl;

        // The anonymous role should always be present
        $acl->addRole($this->options()->getAnonymousRole());

        // Add roles from providers
        foreach($this->providers as $provider) {
            foreach($provider->getRoles() as $role) {
                if (!$acl->hasRole($role)) {
                    $acl->addRole($role);
                }
            }
        }

        $this->acl = $acl;
    }
}