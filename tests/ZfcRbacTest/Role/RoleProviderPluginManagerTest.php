<?php
namespace ZfcRbacTest\Role;

use ZfcRbac\Role\RoleProviderPluginManager;

class RoleProviderPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test to see the the PluginManager throws the correct exception when
     * a Service is requested that doesn't implement \ZfcRbac\Role\RoleProviderInterface
     */
    public function testThrowExceptionIfNoObjectManagerNorObjectRepositoryIsSet()
    {
        $this->setExpectedException('ZfcRbac\Exception\RuntimeException');

        $pluginManager  = new RoleProviderPluginManager();
        $pluginManager->get('stdClass', array());
    }
}
 