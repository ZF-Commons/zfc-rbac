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
use ZfcRbac\Factory\CacheFactory;
use ZfcRbac\Options\ModuleOptions;

/**
 * @covers \ZfcRbac\Factory\CacheFactory
 */
class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function cacheProvider()
    {
        return [
            ['my_cache'],
            [
                [
                    'adapter' => [
                        'name' => 'memory'
                    ]
                ]
            ],
            [new \stdClass()]
        ];
    }

    /**
     * @dataProvider cacheProvider
     */
    public function testFactory($cacheConfig)
    {
        $moduleOptions = new ModuleOptions([
            'cache' => $cacheConfig
        ]);

        $serviceManager = new ServiceManager();
        $serviceManager->setService('ZfcRbac\Options\ModuleOptions', $moduleOptions);

        if (is_string($cacheConfig)) {
            $serviceManager->setService($cacheConfig, $this->getMock('Zend\Cache\Storage\StorageInterface'));
        }

        $factory = new CacheFactory();
        $cache   = $factory->createService($serviceManager);

        $this->assertInstanceOf('Zend\Cache\Storage\StorageInterface', $cache);
    }
}
