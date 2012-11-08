<?php

namespace ZfcRbacTest\Service\Asset\Assertion;

use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Service\Rbac as RbacService;

class SimpleTrueAssertion implements AssertionInterface
{
    /**
     * Dynamic assertion.
     *
     * @param RbacService $rbacService
     * @return boolean
     */
    public function assert(RbacService $rbacService)
    {
        return true;
    }
}