<?php
namespace ZfcRbac\Service;

use ZfcRbac\Service\Rbac as RbacService;

interface RbacAwareInterface
{
    /**
     * Set RBAC service
     *
     * @param RbacService $rbacService
     */
    public function setRbac(RbacService $rbacService);
}
