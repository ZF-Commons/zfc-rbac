<?php

namespace SpiffySecurity\Rbac;

use InvalidArgumentException;

abstract class AbstractRole extends AbstractIterator
{
    protected $name;
    protected $permissions = array();

    /**
     * Get the name of the role.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add permission to the role.
     *
     * @param $name
     * @return AbstractRole
     */
    public function addPermission($name)
    {
        // todo: permissions are stored via name-index which should be faster than in_array
        // micro-optimization unnecessary?
        $this->permissions[$name] = true;
        return $this;
    }

    /**
     * Checks if a permission exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasPermission($name)
    {
        return isset($this->permissions[$name]);
    }

    /**
     * Add a child.
     *
     * @param AbstractRole $child
     * @return Role
     */
    public function addChild(AbstractRole $child)
    {
        $this->children[] = $child;
        return $this;
    }
}