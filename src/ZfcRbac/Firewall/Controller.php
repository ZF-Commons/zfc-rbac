<?php

namespace ZfcRbac\Firewall;

class Controller extends AbstractFirewall
{
    /**
     * @var array
     */
    protected $rules = array();

    /**
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        foreach($rules as $rule) {
            if (!is_array($rule['roles'])) {
                $rule['roles'] = array($rule['roles']);
            }

            if (isset($rule['actions'])) {
                $rule['actions'] = (array) $rule['actions'];

                foreach ($rule['actions'] as $action) {
                    $this->rules[$rule['controller']][$action] = $rule['roles'];
                }
            } else {
                $this->rules[$rule['controller']] = $rule['roles'];
            }
        }
    }

    /**
     * Checks if access is granted to resource for the role.
     *
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
        } elseif (isset($this->rules[$controller])) {
            $roles = $this->rules[$controller];
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
