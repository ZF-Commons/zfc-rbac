<?php

namespace ZfcRbac\Service;

use Zend\Stdlib\AbstractOptions;

class RbacOptions extends AbstractOptions
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
     * service locator and must implement \ZfcRbac\Identity\IdentityInterface.
     *
     * @var string
     */
    protected $identityProvider =  'Zend\Authentication\AuthenticationService';

    /**
     * flag: enable or disable the use of lazy-loading providers.
     *
     * @var bool
     */
    protected $enableLazyProviders = true;

    /**
     * Array of firewall rules.
     *
     * @var array
     */
    protected $firewalls = array();

    /**
     * Array of providers.
     *
     * @var array
     */
    protected $providers = array();

    /**
     * @param string $anonymousRole
     * @return RbacOptions
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
     * @return RbacOptions
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
     * @return RbacOptions
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
     * @return RbacOptions
     */
    public function setFirewalls(array $firewalls)
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
     * @return RbacOptions
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
     * @param array $providers
     * @return RbacOptions
     */
    public function setProviders(array $providers)
    {
        $this->providers = $providers;
        return $this;
    }

    /**
     * @return array
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param string $template
     * @return RbacOptions
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
    
    /**
     * @return bool
     */
    public function getEnableLazyProviders()
    {
    	return $this->enableLazyProviders;
    }

    /**
     * @param bool $value
     * @return RbacOptions
     */
    public function setEnableLazyProviders($value)
    {
    	$this->enableLazyProviders = (bool) $value;
    	return $this;
    }

}
