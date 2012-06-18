<?php

namespace SpiffySecurity\View\Helper;

use SpiffySecurity\Service\Security as SecurityService;
use Zend\View\Helper\AbstractHelper;

class IsGranted extends AbstractHelper
{
    /**
     * @var \SpiffySecurity\Service\Security
     */
    protected $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function __invoke($roles)
    {
        return $this->securityService->isGranted($roles);
    }
}