<?php

namespace ZfcRbac;

use ZfcRbac\Service\Rbac;

interface AssertionInterface
{
    public function assert(Rbac $rbac);
}