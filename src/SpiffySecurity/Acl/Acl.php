<?php

namespace SpiffySecurity\Acl;

use InvalidArgumentException;
use SpiffySecurity\Provider\AbstractProvider;
use Zend\Acl\Acl as ZendAcl;
use Zend\Acl\Role\Registry;

class Acl extends ZendAcl
{
    /**
     * Sanitizes and sets roles from providers.
     *
     * @param array $providers
     * @throws InvalidArgumentException
     */
    public function setRolesFromProviders(array $providers)
    {
        $roles = array();
        foreach($providers as $provider) {
            if (!$provider instanceof AbstractProvider) {
                throw new InvalidArgumentException(
                    'Provider must be an instance of SpiffySecurity\Provider\AbstractProvider'
                );
            }

            foreach($provider->getRoles() as $role => $parents) {
                if (is_numeric($role)) {
                    $role    = $parents;
                    $parents = array();
                }
                if (!is_array($parents)) {
                    $parents = array($parents);
                }

                $roles[$role] = array();
                foreach($parents as $parent) {
                    if (empty($parent)) {
                        continue;
                    }
                    $roles[$role][] = $parent;
                }
            }
        }

        foreach($roles as $role => $parents) {
            $this->recursiveCreateRoles($roles, $role, $parents);
        }
    }

    /**
     * Recursive function to create roles and ensure parents are created.
     *
     * @param array $roles
     * @param \SpiffySecurity\Acl\Acl $acl
     * @param string $role
     * @param array $parents
     */
    protected function recursiveCreateRoles(array $roles, $role, array $parents)
    {
        foreach($parents as $key => $parent) {
            if (!$this->hasResource($parent)) {
                if (isset($roles[$parent])) {
                    $this->recursiveCreateRoles($roles, $parent, $roles[$parent]);
                } else if (!$this->hasRole($parent)) {
                    $this->addRole($parent);
                }
            }
        }

        if (!$this->hasRole($role)) {
            $this->addRole($role, $parents);
        }
    }
}