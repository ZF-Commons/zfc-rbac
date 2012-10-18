<?php

namespace ZfcRbac\View\Helper;

use ZfcRbac\Service\Security as SecurityService;
use Zend\View\Helper\AbstractHelper;

class IsGranted extends AbstractHelper
{
    /**
     * @var \ZfcRbac\Service\Security
     */
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function __invoke($permission)
    {
        return $this->securityService->isGranted($permission);
    }
}