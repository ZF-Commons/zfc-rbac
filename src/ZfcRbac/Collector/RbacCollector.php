<?php

namespace ZfcRbac\Collector;

use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\CollectorInterface;
use ZfcRbac\Service\Rbac as RbacService;

class RbacCollector implements CollectorInterface
{
    /**
     * Collector priority
     */
    const PRIORITY = 10;

    public function __construct(RbacService $rbacService)
    {
    }

    /**
     * Collector Name.
     *
     * @return string
     */
    public function getName()
    {
        return 'zfcrbac';
    }

    /**
     * Collector Priority.
     *
     * @return integer
     */
    public function getPriority()
    {
        return self::PRIORITY;
    }

    /**
     * Collects data.
     *
     * @param MvcEvent $mvcEvent
     */
    public function collect(MvcEvent $mvcEvent)
    {}
}
