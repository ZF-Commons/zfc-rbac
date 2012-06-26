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
     * @param AbstractRole|string $child
     * @return Role
     */
    public function addChild($child)
    {
        if (is_string($child)) {
            $child = new Role($child);
        }
        if (!$child instanceof AbstractRole) {
            throw new InvalidArgumentException(
                'Child must be a string or instance of SpiffySecurity\Role\AbstractRole'
            );
        }

        $this->children[] = $child;
        return $this;
    }
}