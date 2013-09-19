<?php

namespace ZfcRbac\Firewall;

class ControllerRules extends Controller
{
    /**
     * @var array
     */
    protected $rules = array();

    /**
     * @param array $rules
     */
    public function __construct(array $rules, $controller)
    {
        foreach($rules as $rule) {
            $roles = isset($rule['roles']) ? (array) $rule['roles'] : array();
            $permissions = isset($rule['permissions']) ? (array) $rule['permissions'] : array();

            if (isset($rule['actions'])) {
                $rule['actions'] = (array) $rule['actions'];

                foreach ($rule['actions'] as $action) {
                    $this->rules[$controller]['actions'][$action]['roles'] = $roles;
                    $this->rules[$controller]['actions'][$action]['permissions'] = $permissions;
                }
            } else {
                // no action in the rule, it's a global right
                $this->rules[$controller]['global']['roles'] = $roles;
                $this->rules[$controller]['global']['permissions'] = $permissions;
            }
        }
    }

    /**
     * Get the firewall name.
     *
     * @return string
     */
    public function getName()
    {
        return 'controllerRules';
    }
}
