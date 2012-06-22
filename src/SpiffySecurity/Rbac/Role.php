<?php

namespace SpiffySecurity\Rbac;

class Role extends AbstractRole
{
    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }
}