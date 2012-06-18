<?php

namespace SpiffySecurity\Provider;

use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractProvider
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param array $options
     */
    public function __construct(ServiceLocatorInterface $serviceLocator, array $options)
    {
        $this->options        = $options;
        $this->serviceLocator = $serviceLocator;
    }

    abstract public function getRoles();
}