<?php
namespace ZfcRbacTest\Role;

use ZfcRbac\Permission\PermissionProviderPluginManager;

class PermissionProviderPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test to see the the PluginManager throws the correct exception when
     * a Service is requested that doesn't implement \ZfcRbac\Permission\PermissionProviderInterface
     */
    public function testThrowExceptionIfNoObjectManagerNorObjectRepositoryIsSet()
    {
        $this->setExpectedException('ZfcRbac\Exception\RuntimeException');

        $pluginManager  = new PermissionProviderPluginManager();
        $pluginManager->get('stdClass', array());
    }
}
 