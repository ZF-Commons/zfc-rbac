<?php

namespace SpiffySecurity\Firewall;

class Controller implements FirewallInterface
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

    public function isAllowed($role, $resource)
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

        return in_array($role, $roles);
    }

    public function getName()
    {
        return 'controller';
    }
}
