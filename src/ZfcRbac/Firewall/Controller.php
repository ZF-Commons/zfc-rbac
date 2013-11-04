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
            $roles = isset($rule['roles']) ? (array) $rule['roles'] : array();
            $permissions = isset($rule['permissions']) ? (array) $rule['permissions'] : array();

            if (isset($rule['actions'])) {
                $rule['actions'] = (array) $rule['actions'];

                foreach ($rule['actions'] as $action) {
                    $this->rules[$rule['controller']]['actions'][$action]['roles'] = $roles;
                    $this->rules[$rule['controller']]['actions'][$action]['permissions'] = $permissions;
                }
            } else {
                // no action in the rule, it's a global right
                $this->rules[$rule['controller']]['global']['roles'] = $roles;
                $this->rules[$rule['controller']]['global']['permissions'] = $permissions;
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

        // No rules, automatically allow
        if (!isset($this->rules[$controller])) {
            return true;
        }

        $roles = array();
        $permissions = array();

        // Check action first
        if (isset($this->rules[$controller]['actions'][$action])) {
            $roles = $this->rules[$controller]['actions'][$action]['roles'];
            $permissions = $this->rules[$controller]['actions'][$action]['permissions'];
        }
        // then check global permissions
        elseif (isset($this->rules[$controller]['global'])) {
            $roles = $this->rules[$controller]['global']['roles'];
            $permissions = $this->rules[$controller]['global']['permissions'];
        }
        // no global permission and this action is not in a rule
        // Note : if a rule is set for an action, you must set a rule for the other actions
        // or a global rule for this controller
        else {
            return false;
        }

        // global wildcard role
        if (in_array('*', $roles)) {
            return true;
        }

        if (!empty($roles)) {
            $result = $this->rbac->hasRole($roles);
        } else {
            $result = false;
        }

        if (!$result && !empty($permissions)) {
            $granted = false;
            foreach ($permissions as $permission) {
                if ($this->rbac->isGranted($permission)) {
                    $granted = true;
                }
            }
            $result = $granted;
        }

        return $result;
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
