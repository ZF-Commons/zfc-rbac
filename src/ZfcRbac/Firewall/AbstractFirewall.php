<?php

namespace ZfcRbac\Firewall;

use ZfcRbac\Service\Rbac;

abstract class AbstractFirewall
{
    const SEPARATOR = ':';

    /**
     * @var Rbac
     */
    protected $rbac;

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
     * @param \ZfcRbac\Service\Rbac $rbac
     * @return AbstractFirewall
     */
    public function setRbac($rbac)
    {
        $this->rbac = $rbac;
        return $this;
    }
}