<?php

namespace SpiffySecurity\Provider\Role;

use Zend\Stdlib\AbstractOptions;

class InMemoryOptions extends AbstractOptions
{
    /**
     * The array map of roles keyed with parents and having values of
     * an array of children. Non-parented children should live in an array
     * with no key (0).
     *
     * @var array
     */
    protected $roles;

    /**
     * @param array $roles
     * @return InMemoryOptions
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }
}