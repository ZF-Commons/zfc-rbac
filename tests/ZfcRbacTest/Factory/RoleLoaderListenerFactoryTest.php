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

namespace ZfcRbacTest\Factory;

use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Factory\RoleLoaderListenerFactory;
use ZfcRbac\Options\ModuleOptions;

/**
 * @covers \ZfcRbac\Factory\RoleLoaderListenerFactory
 */
class RoleLoaderListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $pluginManager = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $pluginManager->expects($this->once())
                      ->method('get')
                      ->with('ZfcRbac\Role\RoleProviderChain')
                      ->will($this->returnValue($this->getMock('ZfcRbac\Role\RoleProviderInterface')));

        $serviceManager = new ServiceManager();
        $serviceManager->setService('ZfcRbac\Role\RoleProviderPluginManager', $pluginManager);

        $factory  = new RoleLoaderListenerFactory();
        $listener = $factory->createService($serviceManager);

        $this->assertInstanceOf('ZfcRbac\Role\RoleLoaderListener', $listener);
    }
}
