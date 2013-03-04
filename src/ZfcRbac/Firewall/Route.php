<?php

namespace ZfcRbac\Firewall;

class Route extends AbstractFirewall
{
    /**
     * @var array
     */
    protected $roles = array();

    /**
     * @var array
     */
    protected $permissions = array();

    /**
     * @var string
     */
    protected $ruleRegex;

    /**
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $regex = array();
        foreach($rules as $rule) {
            if (isset($rule['roles'])) {
                if (!is_array($rule['roles'])) {
                    $rule['roles'] = array($rule['roles']);
                }
            }

            $this->roles[] = isset($rule['roles']) ? $rule['roles'] : array();

            if (isset($rule['permissions'])) {
                if (!is_array($rule['permissions'])) {
                    $rule['permissions'] = array($rule['permissions']);
                }
            }

            $this->permissions[] = isset($rule['permissions']) ? $rule['permissions'] : array();

            $regex[] = str_replace('/', '\/', '(' . $rule['route'] . ')');
        }

        $this->ruleRegex = sprintf('/%s/', implode('|', $regex));
    }

    /**
     * Checks if access is granted.
     *
     * @param string $resource
     * @return bool
     */
    public function isGranted($resource)
    {
        // No rules, automatically allow
        if (empty($this->roles) && empty($this->permissions)) {
            return true;
        }

        // If no rule exists for this resource allow it.
        $result = (bool) preg_match($this->ruleRegex, $resource, $matches);
        if (false === $result) {
            return true;
        }

        // This is either slick, or stupid.
        // Take the matches, find the first non-empty string (excluding the start), and use that as the
        // key to find the proper role list.
        $roles = array();
        $permissions = array();
        foreach($matches as $key => $value) {
            if ($key === 0) {
                continue;
            }
            if ($value !== '') {
                $roles = $this->roles[$key-1];
                $permissions = $this->permissions[$key-1];
            }
        }

        $result = true;

        if (!empty($roles)) {
            $result = $this->rbac->hasRole($roles);
        }

        if ($result && !empty($permissions)) {
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
        return 'route';
    }
}
