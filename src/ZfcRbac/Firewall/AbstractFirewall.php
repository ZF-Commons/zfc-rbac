<?php

namespace ZfcRbac\Firewall;

use ZfcRbac\Service\Security;
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
     * @param string $resource
     * @return bool
     */
    abstract public function isGranted($resource);

    /**
     * @param \ZfcRbac\Service\Security $security
     * @return AbstractFirewall
     */
    public function setSecurity($security)
    {
        $this->security = $security;
        return $this;
    }
}