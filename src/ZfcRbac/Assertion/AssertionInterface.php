<?php

namespace ZfcRbac\Assertion;

use ZfcRbac\Service\Rbac;

interface AssertionInterface
{
    public function assert(Rbac $rbac);
}