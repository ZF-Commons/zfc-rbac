<?php

namespace ZfcRbac\Provider;

use Zend\EventManager\Event as BaseEvent;
use Zend\Permissions\Rbac\Rbac;

class Event extends BaseEvent
{
    // Primarily for lazy-loading roles/permission
    const EVENT_HAS_ROLE         = 'hasRole';
    const EVENT_IS_GRANTED       = 'isGranted';

    // Primarily for pre-loading roles/permissions
    const EVENT_LOAD_ROLES       = 'loadRoles';
    const EVENT_LOAD_PERMISSIONS = 'loadPermissions';

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
     * @return Event
     */
    public function setPermission($permission)
    {
        $this->permission = (string) $permission;
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
     * @param  Rbac $rbac
     * @return Event
     */
    public function setRbac(Rbac $rbac)
    {
        $this->rbac = $rbac;
        return $this;
    }

    /**
     * @return Rbac
     */
    public function getRbac()
    {
        return $this->rbac;
    }

    /**
     * @param string $role
     * @return Event
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