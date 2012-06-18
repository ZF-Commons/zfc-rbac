<?php

namespace SpiffySecurity\Service;

use InvalidArgumentException;
use SpiffySecurity\Firewall\FirewallInterface;
use SpiffySecurity\Options\SecurityOptions;
use SpiffySecurity\Provider\AbstractProvider;
use Zend\Acl\Acl;
use Zend\Acl\Role\GenericRole;
use Zend\Acl\Role\RoleInterface;

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
     * @var \Zend\Acl\Role\RoleInterface
     */
    protected $role;

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
     * Access to firewalls by name.
     *
     * @param string $name
     * @return \SpiffySecurity\Firewall\FirewallInterface
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
     * @param \SpiffySecurity\Firewall\FirewallInterface $firewall
     * @return \SpiffySecurity\Service\Security
     */
    public function addFirewall(FirewallInterface $firewall)
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
     * @param \SpiffySecurity\Provider\AbstractProvider $provider
     * @return \SpiffySecurity\Service\Security
     */
    public function addProvider(AbstractProvider $provider)
    {
        $this->reset();
        $this->providers[] = $provider;
        return $this;
    }

    /**
     * @return \Zend\Acl\Role\RoleInterface
     */
    public function getRole()
    {
        if (null === $this->role) {
            $this->role = new GenericRole($this->options()->getAnonymousRole());
        }
        return $this->role;
    }

    /**
     * @param \Zend\Acl\Role\RoleInterface $role
     * @return Security
     */
    public function setRole(RoleInterface $role)
    {
        $this->role = $role;
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
     * @return \SpiffySecurity\Options\SecurityOptions
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

        // Setup roles from the providers
        foreach($this->providers as $provider) {
            $roles  = $provider->getRoles();

            foreach($roles as $role => $parents) {
                if (is_int($role)) {
                    $role   = $parents;
                    $parent = array();
                }

                $this->createRoles($roles, $acl, $role, $parents);
            }
        }

        $this->acl = $acl;
    }

    /**
     * Recursive function to create roles and ensure parents are created.
     *
     * @param array $roles
     * @param \Zend\Acl\Acl $acl
     * @param string $role
     * @param array $parents
     */
    protected function createRoles($roles, $acl, $role, $parents)
    {
        if (!is_array($parents)) {
            $parents = array($parents);
        }

        foreach($parents as $key => $parent) {
            if (!$parent) {
                unset($parents[$key]);
                continue;
            }
            if (!$acl->hasResource($parent)) {
                if (isset($roles[$parent])) {
                    $this->createRoles($roles, $acl, $parent, $roles[$parent]);
                } else if (!$acl->hasRole($parent)) {
                    $acl->addRole($parent);
                }
            }
        }

        if (!$acl->hasRole($role)) {
            $acl->addRole($role, $parents);
        }
    }
}