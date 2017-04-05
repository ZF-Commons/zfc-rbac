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

namespace ZfcRbacTest\Assertion;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Exception\InvalidServiceException;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Assertion\AssertionPluginManager;

/**
 * @covers \ZfcRbac\Assertion\AssertionPluginManager
 */
class AssertionPluginManagerTest extends TestCase
{
    public function testValidationOfPluginSucceedsIfAssertionInterfaceIsImplemented()
    {
        $containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $pluginMock    = $this->getMockBuilder(AssertionInterface::class)->getMock();
        $pluginManager = new AssertionPluginManager($containerMock);

        $this->assertNull($pluginManager->validatePlugin($pluginMock));
    }

    public function testValidationOfPluginFailsIfAssertionInterfaceIsNotImplemented()
    {
        $this->expectException(InvalidServiceException::class);
        $containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $pluginManager = new AssertionPluginManager($containerMock);

        $plugin = new \stdClass();
        $pluginManager->validatePlugin($plugin);
    }
}
