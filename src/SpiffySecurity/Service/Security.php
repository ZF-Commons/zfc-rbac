<?php

namespace SpiffySecurity\Service;

use SpiffySecurity\Firewall\AbstractFirewall;
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

    public function __construct(array $options = array())
    {
        $this->options = new SecurityOptions($options);
    }

    public function addFirewall(AbstractFirewall $firewall)
    {
        $this->reset();
        $this->firewalls[] = $firewall;
        return $this;
    }

    public function addProvider(AbstractProvider $provider)
    {
        $this->reset();
        $this->providers[] = $provider;
        return $this;
    }

    public function getRole()
    {
        if (null === $this->role) {
            $this->role = new GenericRole($this->options()->getAnonymousRole());
        }
        return $this->role;
    }

    public function setRole(RoleInterface $role)
    {
        $this->role = $role;
        return $this;
    }

    public function getAcl()
    {
        $this->load();

        return $this->acl;
    }

    public function options()
    {
        return $this->options;
    }

    protected function reset()
    {
        $this->acl    = null;
        $this->loaded = false;
    }

    protected function load()
    {
        if ($this->loaded) {
            return;
        }

        $acl = new Acl;

        $acl->addRole($this->options()->getAnonymousRole());

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

        foreach($this->firewalls as $firewall) {
            foreach($firewall->getRules() as $map) {
                if (!$acl->hasResource($map['resource'])) {
                    $acl->addResource($map['resource']);
                }

                $acl->allow($map['roles'], $map['resource']);
            }
        }

        $this->acl = $acl;
    }

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