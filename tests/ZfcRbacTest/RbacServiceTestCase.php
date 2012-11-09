<?php

namespace ZfcRbacTest;

use PHPUnit_Framework_TestCase;
use ZfcRbac\Service\Rbac as RbacService;

class RbacServiceTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ZfcRbac\Service\Rbac
     */
    private static $rbacService;

    /**
     * @param \ZfcRbac\Service\Rbac $rbacService
     */
    public static function setRbacService(RbacService $rbacService)
    {
        self::$rbacService = $rbacService;
    }

    /**
     * @return \ZfcRbac\Service\Rbac
     */
    public function getRbacService()
    {
        return self::$rbacService;
    }
}