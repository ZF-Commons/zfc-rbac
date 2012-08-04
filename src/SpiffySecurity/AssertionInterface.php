<?php

namespace SpiffySecurity;

use SpiffySecurity\Service\Security;

interface AssertionInterface
{
    public function assert(Security $security);
}