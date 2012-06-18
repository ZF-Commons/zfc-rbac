<?php

namespace SpiffySecurity\Firewall;

use SpiffySecurity\Identity\IdentityInterface;
use SpiffySecurity\Service\Security;
use SpiffySecurity\Acl\Acl;

abstract class AbstractFirewall
{
    /**
     * @var \SpiffySecurity\Service\Security
     */
    protected $securityService;

    /**
     * Set the security instance.
     *
     * @param Security $security
     * @return mixed
     */
    public function setSecurityService(Security $securityService)
    {
        $this->securityService = $securityService;
        return $this;
    }

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
    abstract public function isAllowed(IdentityInterface $identity, $resource);
}