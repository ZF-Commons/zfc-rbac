<?php

namespace SpiffySecurity\Options;

use Zend\Stdlib\Options;

class SecurityOptions extends Options
{
    protected $anonymousRole = 'anonymous';

    protected $firewallRoute = false;

    protected $firewallController = true;

    protected $template = 'error/403';

    protected $role = 'guest';

    protected $firewall = array();

    protected $provider = array();

    public function setFirewallController($firewallController)
    {
        $this->firewallController = $firewallController;
        return $this;
    }

    public function getFirewallController()
    {
        return $this->firewallController;
    }

    public function setFirewallRoute($firewallRoute)
    {
        $this->firewallRoute = $firewallRoute;
        return $this;
    }

    public function getFirewallRoute()
    {
        return $this->firewallRoute;
    }

    public function setFirewall($firewall)
    {
        $this->firewall = $firewall;
        return $this;
    }

    public function getFirewall()
    {
        return $this->firewall;
    }

    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setAnonymousRole($anonymousRole)
    {
        $this->anonymousRole = $anonymousRole;
        return $this;
    }

    public function getAnonymousRole()
    {
        return $this->anonymousRole;
    }
}