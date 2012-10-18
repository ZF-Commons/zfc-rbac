<?php

namespace ZfcRbac\Controller\Plugin;

use RuntimeException;
use ZfcRbac\Service\Security as SecurityService;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class IsGranted extends AbstractPlugin
{
    public function __invoke($permission)
    {
        $controller = $this->getController();
        if (!$controller instanceof ServiceLocatorAwareInterface) {
            throw new RuntimeException('Controller must implement ServiceLocatorAwareInterface to use this plugin');
        }

        return $this->getController()
                    ->getServiceLocator()
                    ->get('ZfcRbac\Service\Security')
                    ->isGranted($permission);
    }
}