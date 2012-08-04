<?php

namespace SpiffySecurity\Provider\NestedSet\Lazy;

use Zend\Stdlib\AbstractOptions;

class DoctrineDbalOptions extends AbstractOptions
{
    /**
     * The name of the table the permissions are stored in.
     *
     * @var string
     */
    protected $permissionTable = 'permission';

    /**
     * The name of the table the permissions are stored in.
     *
     * @var string
     */
    protected $roleTable = 'role';

    /**
     * The name of the table used to join roles to permissions.
     *
     * @var string
     */
    protected $roleJoinTable = 'role_permission';

    /**
     * The id column of the permission table.
     *
     * @var string
     */
    protected $permissionIdColumn = 'id';

    /**
     * The join table permission id column.
     *
     * @var string
     */
    protected $permissionJoinColumn = 'permission_id';

    /**
     * The id column of the role table.
     *
     * @var string
     */
    protected $roleIdColumn = 'id';

    /**
     * The join table role id column.
     *
     * @var string
     */
    protected $roleJoinColumn = 'role_id';

    /**
     * The name column of the permission table.
     *
     * @var string
     */
    protected $permissionNameColumn = 'name';

    /**
     * The name column of the role table.
     *
     * @var string
     */
    protected $roleNameColumn = 'name';

    /**
     * @param string $permissionIdColumn
     */
    public function setPermissionIdColumn($permissionIdColumn)
    {
        $this->permissionIdColumn = $permissionIdColumn;
    }

    /**
     * @return string
     */
    public function getPermissionIdColumn()
    {
        return $this->permissionIdColumn;
    }

    /**
     * @param string $permissionJoinColumn
     */
    public function setPermissionJoinColumn($permissionJoinColumn)
    {
        $this->permissionJoinColumn = $permissionJoinColumn;
    }

    /**
     * @return string
     */
    public function getPermissionJoinColumn()
    {
        return $this->permissionJoinColumn;
    }

    /**
     * @param string $permissionNameColumn
     */
    public function setPermissionNameColumn($permissionNameColumn)
    {
        $this->permissionNameColumn = $permissionNameColumn;
    }

    /**
     * @return string
     */
    public function getPermissionNameColumn()
    {
        return $this->permissionNameColumn;
    }

    /**
     * @param string $permissionTable
     */
    public function setPermissionTable($permissionTable)
    {
        $this->permissionTable = $permissionTable;
    }

    /**
     * @return string
     */
    public function getPermissionTable()
    {
        return $this->permissionTable;
    }

    /**
     * @param string $roleIdColumn
     */
    public function setRoleIdColumn($roleIdColumn)
    {
        $this->roleIdColumn = $roleIdColumn;
    }

    /**
     * @return string
     */
    public function getRoleIdColumn()
    {
        return $this->roleIdColumn;
    }

    /**
     * @param string $roleJoinColumn
     */
    public function setRoleJoinColumn($roleJoinColumn)
    {
        $this->roleJoinColumn = $roleJoinColumn;
    }

    /**
     * @return string
     */
    public function getRoleJoinColumn()
    {
        return $this->roleJoinColumn;
    }

    /**
     * @param string $roleJoinTable
     */
    public function setRoleJoinTable($roleJoinTable)
    {
        $this->roleJoinTable = $roleJoinTable;
    }

    /**
     * @return string
     */
    public function getRoleJoinTable()
    {
        return $this->roleJoinTable;
    }

    /**
     * @param string $roleNameColumn
     */
    public function setRoleNameColumn($roleNameColumn)
    {
        $this->roleNameColumn = $roleNameColumn;
    }

    /**
     * @return string
     */
    public function getRoleNameColumn()
    {
        return $this->roleNameColumn;
    }

    /**
     * @param string $roleTable
     */
    public function setRoleTable($roleTable)
    {
        $this->roleTable = $roleTable;
    }

    /**
     * @return string
     */
    public function getRoleTable()
    {
        return $this->roleTable;
    }
}