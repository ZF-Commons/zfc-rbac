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

namespace ZfcRbacTest\Collector;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Rbac\Rbac;
use Zend\Permissions\Rbac\Role;
use ZfcRbac\Collector\RbacCollector;
use ZfcRbac\Guard\GuardInterface;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Role\InMemoryRoleProvider;
use ZfcRbac\Service\RoleService;

/**
 * @covers \ZfcRbac\Collector\RbacCollector
 */
class RbacCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultGetterReturnValues()
    {
        $collector = new RbacCollector();

        $this->assertSame(-100, $collector->getPriority());
        $this->assertSame('zfc_rbac', $collector->getName());
    }

    public function testSerialize()
    {
        $collector  = new RbacCollector();
        $serialized = $collector->serialize();

        $this->assertInternalType('string', $serialized);

        $unserialized = unserialize($serialized);

        $this->assertSame([], $unserialized['guards']);
        $this->assertSame([], $unserialized['roles']);
        $this->assertSame([], $unserialized['options']);
    }

    public function testUnserialize()
    {
        $collector    = new RbacCollector();
        $unserialized = [
            'guards'      => ['foo' => 'bar'],
            'roles'       => ['foo' => 'bar'],
            'options'     => ['foo' => 'bar']
        ];
        $serialized   = serialize($unserialized);

        $collector->unserialize($serialized);

        $collection = $collector->getCollection();

        $this->assertInternalType('array', $collection);
        $this->assertSame(['foo' => 'bar'], $collection['guards']);
        $this->assertSame(['foo' => 'bar'], $collection['roles']);
        $this->assertSame(['foo' => 'bar'], $collection['options']);
    }

    public function testCollectNothingIfNoApplicationIsSet()
    {
        $mvcEvent  = new MvcEvent();
        $collector = new RbacCollector();

        $this->assertNull($collector->collect($mvcEvent));
    }
}
