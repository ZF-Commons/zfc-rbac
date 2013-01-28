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

    public function initRoles()
    {
        $rbac = $this->getRbacService()->getRbac();

        $rbac->addRole('parent');
        $parent = $rbac->getRole('parent');

        $parent->addChild('child1');
        $parent->addChild('child2');
        $parent->addChild('child3');

        $child1 = $rbac->getRole('child1');
        $child1->addChild('subchild1');

        return $this;
    }

    public function initPermissions()
    {
        $this->initRoles();
        $rbac = $this->getRbacService()->getRbac();

        $parent = $rbac->getRole('parent');
        $child1 = $rbac->getRole('child1');

        $parent->addPermission('parent.permission');
        $child1->addPermission('child1.permission');

        return $this;
    }
}