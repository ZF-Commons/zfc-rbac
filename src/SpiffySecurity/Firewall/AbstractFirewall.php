<?php

namespace SpiffySecurity\Firewall;

use SpiffySecurity\Service\Security;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractFirewall
{
    const SEPARATOR = ':';

    /**
     * @var Security
     */
    protected $security;

    /**
     * Get the firewall name.
     *
     * @abstract
     * @return string
     */
    abstract public function getName();

    /**
     * Checks if access is granted to resource for the role.
     *
     * @abstract
     * @param \SpiffySecurity\Identity\IdentityInterface $identity
     * @param string $resource
     * @return bool
     */
    abstract public function isGranted($resource);

    /**
     * @param \SpiffySecurity\Service\Security $security
     * @return AbstractFirewall
     */
    public function setSecurity($security)
    {
        $this->security = $security;
        return $this;
    }

    /**
     * Checks if any of the roles are owned by the identity.
     *
     * @param array $roles
     * @return bool
     */
    protected function checkRoles(array $roles)
    {
        foreach($roles as $role) {
            if ($this->security->hasRole($role)) {
                return true;
            }
        }
        return false;
    }
}