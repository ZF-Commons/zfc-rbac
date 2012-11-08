<?php

namespace ZfcRbac\Identity;

interface IdentityInterface
{
    /**
     * @return array
     */
    public function getRoles();
}