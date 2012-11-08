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
}