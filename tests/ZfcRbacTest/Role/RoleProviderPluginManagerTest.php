<?php
namespace ZfcRbacTest\Role;

use ZfcRbac\Role\RoleProviderPluginManager;

/**
 * @coversDefaultClass \ZfcRbac\Role\RoleProviderPluginManager
 */
class RoleProviderPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidationOfPluginSucceedsIfRoleProviderInterfaceIsImplemented()
    {
        $pluginMock    = $this->getMock('ZfcRbac\Role\RoleProviderInterface');
        $pluginManager = new RoleProviderPluginManager();

        $this->assertNull($pluginManager->validatePlugin($pluginMock));
    }

    public function testValidationOfPluginFailsIfRoleProviderInterfaceIsNotImplemented()
    {
        $this->setExpectedException('ZfcRbac\Exception\RuntimeException');

        $pluginManager  = new RoleProviderPluginManager();
        $pluginManager->get('stdClass', array());
    }
}
 