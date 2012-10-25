<?php

namespace ZfcRbac\Identity;

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
        if (!is_array($roles)) {
            $roles = array($roles);
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