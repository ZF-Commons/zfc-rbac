<?php

namespace ZfcRbac\View\Helper;

use ZfcRbac\Service\Rbac as SecurityService;
use Zend\View\Helper\AbstractHelper;

class IsGranted extends AbstractHelper
{
    /**
     * @var SecurityService
     */
    protected $securityService;

    /**
     * @param SecurityService $securityService
     */
    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * @param  $permission
     * @return bool
     */
    public function __invoke($permission)
    {
        return $this->securityService->isGranted($permission);
    }
}