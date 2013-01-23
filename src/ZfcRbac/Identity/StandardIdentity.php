<?php

namespace ZfcRbac\Identity;

use InvalidArgumentException;

use ZfcRbac\Identity\IdentityInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface,
    Zend\ServiceManager\ServiceLocatorInterface,
    Zend\Permissions\Rbac\Role;


class StandardIdentity implements IdentityInterface, ServiceLocatorAwareInterface
{
    /**
     * Array of roles.
     *
     * @var array
     */
    protected $roles;

    /** @var ServiceLocatorInterface */
    protected $_sm;

    public function setServiceLocator(ServiceLocatorInterface $sm)
    {
        $this->_sm = $sm;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->_sm;
    }

    /**
     * @param $roles
     */
    public function __construct($roles)
    {
        if (is_string($roles)) {
            $roles = (array) $roles;
        }

        if (!is_array($roles)) {
            throw new InvalidArgumentException('StandardIdentity only accepts strings or arrays');
        }

        $this->roles = $roles;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Check if an identity has a role
     * @param string $role Role to check
     * @return bool
     */
    public function hasRole($rolename)
    {
        $zfcRbac = $this->_sm->get('ZfcRbac\Service\Rbac');
        $rbac = $zfcRbac->getRbac();

        foreach($this->getRoles() as $idRole) {
            $role = $rbac->getRole($idRole);
            $result = $this->_checkRole($role, $rolename);
            if ($result) {
                break;
            }
        }

        return $result;
    }

    /**
     * Check rolename against the role hierarchy
     * @param \Zend\Permissions\Rbac\Role $role
     * @param string $rolename
     * @return boolean
     */
    protected function _checkRole(Role $role, $rolename)
    {
        if ($role->getName() == $rolename) {
            return true;
        }
        else {
            $it = new \RecursiveIteratorIterator($role, \RecursiveIteratorIterator::SELF_FIRST);
            foreach($it as $leaf) {
                if ($leaf->getName() == $rolename) {
                    return true;
                }
            }
        }
        return false;
    }


}
