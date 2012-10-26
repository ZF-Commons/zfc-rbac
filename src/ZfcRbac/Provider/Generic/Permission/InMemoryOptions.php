<?php

namespace ZfcRbac\Provider\Generic\Permission;

use Zend\Stdlib\AbstractOptions;

class InMemoryOptions extends AbstractOptions
{
    /**
     * The array map of permissions keyed with role name and having values of
     * an array of permissions.
     *
     * @var array
     */
    protected $permissions = array();

    /**
     * @param array $permissions
     * @return InMemoryOptions
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}