<?php

namespace SpiffySecurity\Provider;

use Zend\Permissions\Rbac\Rbac;
use Zend\EventManager\Event as BaseEvent;

class Event extends BaseEvent
{
    // Primarily for lazy-loading roles/permission
    const EVENT_HAS_ROLE         = 'has.role';
    const EVENT_IS_GRANTED       = 'is.granted';

    // Primarily for pre-loading roles/permissions
    const EVENT_LOAD_ROLES       = 'load.roles';
    const EVENT_LOAD_PERMISSIONS = 'load.permissions';

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
     * @param \Zend\Permissions\Rbac\Rbac $rbac
     */
    public function setRbac(Rbac $rbac)
    {
        $this->rbac = $rbac;
        return $this;
    }

    /**
     * @return \Zend\Permissions\Rbac\Rbac
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