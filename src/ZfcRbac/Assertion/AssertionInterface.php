<?php

namespace ZfcRbac\Assertion;

use ZfcRbac\Service\Rbac;

interface AssertionInterface
{
    /**
     * Dynamic assertion.
     *
     * @param \ZfcRbac\Service\Rbac $rbac
     * @return boolean
     */
    public function assert(Rbac $rbac);
}