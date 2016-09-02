<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfcRbacTest\Role;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\InvalidServiceException;
use ZfcRbac\Role\RoleProviderInterface;
use ZfcRbac\Role\RoleProviderPluginManager;

/**
 * @covers \ZfcRbac\Role\RoleProviderPluginManager
 */
class RoleProviderPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testValidationOfPluginSucceedsIfRoleProviderInterfaceIsImplemented()
    {
        $containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $pluginMock    = $this->getMockBuilder(RoleProviderInterface::class)->getMock();
        $pluginManager = new RoleProviderPluginManager($containerMock);

        $this->assertNull($pluginManager->validatePlugin($pluginMock));
    }

    public function testValidationOfPluginFailsIfRoleProviderInterfaceIsNotImplemented()
    {
        $this->setExpectedException(InvalidServiceException::class);
        $containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $pluginManager = new RoleProviderPluginManager($containerMock);

        $plugin = new \stdClass();
        $pluginManager->validatePlugin($plugin);
    }
}
