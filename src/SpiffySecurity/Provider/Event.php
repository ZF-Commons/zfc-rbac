<?php

namespace SpiffySecurity\Provider;

use SpiffySecurity\Rbac\Rbac;
use Zend\EventManager\Event as BaseEvent;

class Event extends BaseEvent
{
    const EVENT_IS_GRANTED       = 'is.granted';
    const EVENT_LOAD_ROLES       = 'load.roles';
    const EVENT_LOAD_PERMISSIONS = 'load.permissions';
    const EVENT_ON_LOAD          = 'on.load';

    /**
     * @var Rbac
     */
    protected $rbac;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $permission;

    /**
     * @param string $permission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param \SpiffySecurity\Rbac\Rbac $rbac
     */
    public function setRbac(Rbac $rbac)
    {
        $this->rbac = $rbac;
        return $this;
    }

    /**
     * @return \SpiffySecurity\Rbac\Rbac
     */
    public function getRbac()
    {
        return $this->rbac;
    }

    /**
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }
}