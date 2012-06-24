<?php

namespace SpiffySecurity\Service;

use Zend\Stdlib\Options;

class SecurityOptions extends Options
{
    /**
     * The default role that is used if no role is found from the
     * role provider.
     *
     * @var string
     */
    protected $anonymousRole = 'anonymous';

    /**
     * Flag: enable or disable the routing firewall.
     *
     * @var bool
     */
    protected $firewallRoute = false;

    /**
     * Flag: enable or disable the controller firewall.
     *
     * @var bool
     */
    protected $firewallController = true;

    /**
     * Set the view template to use on a 403 error.
     *
     * @var string
     */
    protected $template = 'error/403';

    /**
     * Set the identity provider to use. The identity provider must be retrievable from the
     * service locator and must implement \SpiffySecurity\Identity\IdentityInterface.
     *
     * @var string
     */
    protected $identityProvider =  'my_identity_provider';

    /**
     * Array of firewall rules.
     *
     * @var array
     */
    protected $firewalls = array();

    /**
     * Array of role providers.
     *
     * @var array
     */
    protected $roleProviders = array();

    /**
     * Array of permission providers.
     *
     * @var array
     */
    protected $permissionProviders = array();

    /**
     * @param string $anonymousRole
     * @return SecurityOptions
     */
    public function setAnonymousRole($anonymousRole)
    {
        $this->anonymousRole = $anonymousRole;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnonymousRole()
    {
        return $this->anonymousRole;
    }

    /**
     * @param boolean $firewallController
     * @return SecurityOptions
     */
    public function setFirewallController($firewallController)
    {
        $this->firewallController = $firewallController;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getFirewallController()
    {
        return $this->firewallController;
    }

    /**
     * @param boolean $firewallRoute
     * @return SecurityOptions
     */
    public function setFirewallRoute($firewallRoute)
    {
        $this->firewallRoute = $firewallRoute;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getFirewallRoute()
    {
        return $this->firewallRoute;
    }

    /**
     * @param array $firewalls
     * @return SecurityOptions
     */
    public function setFirewalls($firewalls)
    {
        $this->firewalls = $firewalls;
        return $this;
    }

    /**
     * @return array
     */
    public function getFirewalls()
    {
        return $this->firewalls;
    }

    /**
     * @param string $identityProvider
     * @return SecurityOptions
     */
    public function setIdentityProvider($identityProvider)
    {
        $this->identityProvider = $identityProvider;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentityProvider()
    {
        return $this->identityProvider;
    }

    /**
     * @param array $permissionProviders
     * @return SecurityOptions
     */
    public function setPermissionProviders($permissionProviders)
    {
        $this->permissionProviders = $permissionProviders;
        return $this;
    }

    /**
     * @return array
     */
    public function getPermissionProviders()
    {
        return $this->permissionProviders;
    }

    /**
     * @param array $roleProviders
     * @return SecurityOptions
     */
    public function setRoleProviders($roleProviders)
    {
        $this->roleProviders = $roleProviders;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoleProviders()
    {
        return $this->roleProviders;
    }

    /**
     * @param string $template
     * @return SecurityOptions
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}