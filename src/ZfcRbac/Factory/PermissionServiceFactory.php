<?php

namespace ZfcRbac\Factory;

use Rbac\Rbac;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\PermissionService;
use ZfcRbac\Service\RoleService;

class PermissionServiceFactory implements FactoryInterface
{

    /**
     * @param  ServiceLocatorInterface $serviceLocator
     * @return PermissionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var RoleService $roleService */
        $roleService = $serviceLocator->get(RoleService::class);
        /** @var Rbac $rbac */
        $rbac = $serviceLocator->get(Rbac::class);

        return new PermissionService($roleService, $rbac);
    }

}