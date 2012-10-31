<?php

namespace ZfcRbac\Firewall;

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
                $this->rules[$rule['namespace']][$rule['controller']][$rule['action']] = $rule['roles'];
            } else {
                $this->rules[$rule['namespace']][$rule['controller']] = $rule['roles'];
            }
        }
    }

    /**
     * Checks if access is granted to resource for the role.
     *
     * @param \ZfcRbac\Identity\IdentityInterface $identity
     * @param string $resource
     * @return bool
     */
    public function isGranted($resource)
    {
        $resource   = explode(':', $resource);
        $namespace  = $resource[0];
        $controller = $resource[1];
        $action     = isset($resource[1]) ? $resource[1] : null;

        // Check action first
        if (isset($this->rules[$namespace][$controller][$action])) {
            $roles = $this->rules[$namespace][$controller][$action];
        } else if (isset($this->rules[$namespace][$controller])) {
            $roles = $this->rules[$namespace][$controller];
        } else {
            return true;
        }

        return $this->rbac->hasRole($roles);
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
