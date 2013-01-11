<?php

namespace ZfcRbac\Identity;

use InvalidArgumentException;

class StandardIdentity implements IdentityInterface
{
    /**
     * Array of roles.
     *
     * @var array
     */
    protected $roles;

    /**
     * @param $roles
     */
    public function __construct($roles)
    {
        if (is_string($roles)) {
            $roles = (array) $roles;
        }

        if (!is_array($roles)) {
            throw new InvalidArgumentException('StandardIdentity only accepts strings or arrays');
        }

        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Check if an identity has a role
     * @param string $role Role to check
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }
}