<?php

namespace ZfcRbacTest\Identity;

use PHPUnit_Framework_TestCase;
use ZfcRbac\Identity\StandardIdentity;

class StandardIdentityTest extends PHPUnit_Framework_TestCase
{
    public function testIdentityAcceptsArrays()
    {
        $identity = new StandardIdentity(array('role1', 'role2'));
        $this->assertEquals(array('role1', 'role2'), $identity->getRoles());
    }

    public function testIdentityAcceptsStrings()
    {
        $identity = new StandardIdentity('role');
        $this->assertEquals(array('role'), $identity->getRoles());
    }

    public function testIdentityThrowsExceptionOnInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException', 'StandardIdentity only accepts strings or arrays');
        new StandardIdentity(false);
    }
}