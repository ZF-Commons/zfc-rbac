<?php
namespace ZfcRbacTest\Role;

use ZfcRbac\Permission\PermissionProviderPluginManager;

class PermissionProviderPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidationOfPluginSucceedsIfPermissionProviderInterfaceIsImplemented()
    {
        $pluginMock    = $this->getMock('ZfcRbac\Permission\PermissionProviderInterface');
        $pluginManager = new PermissionProviderPluginManager();

        $this->assertNull($pluginManager->validatePlugin($pluginMock));
    }

    public function testValidationOfPluginFailsIfPermissionProviderInterfaceIsNotImplemented()
    {
        $this->setExpectedException('ZfcRbac\Exception\RuntimeException');

        $pluginManager  = new PermissionProviderPluginManager();
        $pluginManager->get('stdClass', array());
    }
}
 