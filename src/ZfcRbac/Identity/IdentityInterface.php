<?php

namespace ZfcRbac\Identity;

interface IdentityInterface
{
    /**
     * @return array
     */
    public function getRoles();

    /**
     * Check if an identity has a role
     * @param string $role Role to check
     * @return bool
     */
    public function hasRole($role);
}