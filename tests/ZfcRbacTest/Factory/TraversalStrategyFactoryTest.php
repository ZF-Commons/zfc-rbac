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

use ZfcRbac\Factory\TraversalStrategyFactory;
use ZfcRbac\Options\ModuleOptions;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\ServiceManager;

/**
 * @covers \ZfcRbac\Factory\TraversalStrategyFactory
 */
class TraversalStrategyFactoryTest extends TestCase
{
    public function testReturnAlreadyInstantiatedStrategy()
    {
        $serviceManager = new ServiceManager();
        $instance       = $this->getMock('Rbac\Traversal\Strategy\TraversalStrategyInterface');

        $serviceManager->setService('ZfcRbac\Options\ModuleOptions', new ModuleOptions(['traversal_strategy' => $instance]));

        $factory  = new TraversalStrategyFactory();
        $strategy = $factory->createService($serviceManager);

        $this->assertSame($strategy, $instance);
    }

    public function testFetchStrategyFromServiceManager()
    {
        $serviceManager = new ServiceManager();
        $instance       = $this->getMock('Rbac\Traversal\Strategy\TraversalStrategyInterface');

        $serviceManager->setService('MyTraversalStrategy', $instance);
        $serviceManager->setService('ZfcRbac\Options\ModuleOptions', new ModuleOptions(['traversal_strategy' => 'MyTraversalStrategy']));

        $factory  = new TraversalStrategyFactory();
        $strategy = $factory->createService($serviceManager);

        $this->assertSame($strategy, $instance);
    }

    public function testDefaultStrategyBasedOnPhpVersion()
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ZfcRbac\Options\ModuleOptions', new ModuleOptions([]));

        $factory  = new TraversalStrategyFactory();
        $strategy = $factory->createService($serviceManager);

        if (version_compare(PHP_VERSION_ID, '5.5.0', '>=')) {
            $this->assertInstanceOf('Rbac\Traversal\Strategy\GeneratorStrategy', $strategy);
        } else {
            $this->assertInstanceOf('Rbac\Traversal\Strategy\RecursiveRoleIteratorStrategy', $strategy);
        }
    }
}
