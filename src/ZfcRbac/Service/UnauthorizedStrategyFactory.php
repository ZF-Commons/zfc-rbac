<?php

namespace ZfcRbac\Service;

use ZfcRbac\View\UnauthorizedStrategy;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UnauthorizedStrategyFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $security = $sl->get('ZfcRbac\Service\Rbac');

        $strategy = new UnauthorizedStrategy;
        $strategy->setUnauthorizedTemplate($security->options()->getTemplate());

        return $strategy;
    }
}