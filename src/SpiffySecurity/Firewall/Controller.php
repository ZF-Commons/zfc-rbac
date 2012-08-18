<?php

namespace SpiffySecurity\Firewall;

class Controller extends AbstractFirewall
{
    protected $rules = array();

    public function __construct(array $rules)
    {
        foreach($rules as $rule) {
            if (!is_array($rule['roles'])) {
                $rule['roles'] = array($rule['roles']);
            }
            if (isset($rule['action'])) {
                $this->rules[$rule['controller']][$rule['action']] = $rule['roles'];
            } else {
                $this->rules[$rule['controller']] = $rule['roles'];
            }
        }
    }

    /**
     * Checks if access is granted to resource for the role.
     *
     * @param \SpiffySecurity\Identity\IdentityInterface $identity
     * @param string $resource
     * @return bool
     */
    public function isGranted($resource)
    {
        $resource   = explode(':', $resource);
        $controller = $resource[0];
        $action     = isset($resource[1]) ? $resource[1] : null;

        // Check action first
        if (isset($this->rules[$controller][$action])) {
            $roles = $this->rules[$controller][$action];
        } else if (isset($this->rules[$controller])) {
            $roles = $this->rules[$controller];
        } else {
            return true;
        }

        return $this->security->hasRole($roles);
    }

    /**
     * Get the firewall name.
     *
     * @return string
     */
    public function getName()
    {
        return 'controller';
    }
}
