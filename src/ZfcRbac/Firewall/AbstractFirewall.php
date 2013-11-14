<?php

namespace ZfcRbac\Firewall;

use ZfcRbac\Service\Rbac;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractFirewall
{
    
    
    const SEPARATOR = ':';

    /**
     * @var Rbac
     */
    protected $rbac;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator = NULL)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param  Rbac $rbac
     * @return AbstractFirewall
     */
    public function setRbac(Rbac $rbac)
    {
        $this->rbac = $rbac;
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
     * @param string $resource
     * @return bool
     */
    abstract public function isGranted($resource);
}
