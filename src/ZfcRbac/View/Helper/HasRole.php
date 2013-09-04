<?php

namespace ZfcRbac\View\Helper;

use ZfcRbac\Service\Rbac as RbacService;
use Zend\View\Helper\AbstractHelper;

class HasRole extends AbstractHelper
{
    /**
     * @var RbacService
     */
    protected $rbacService;

    /**
     * @param RbacService $rbacService
     */
    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * @param  $permission
     * @return bool
     */
    public function __invoke($role)
    {
        return $this->rbacService->hasRole($role);
    }
}