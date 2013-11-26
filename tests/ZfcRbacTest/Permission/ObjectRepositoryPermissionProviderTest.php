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

namespace ZfcRbacTest\Permission;

use Doctrine\ORM\Tools\SchemaTool;
use Zend\ServiceManager\ServiceManager;
use ZfcRbac\Permission\ObjectRepositoryPermissionProvider;
use ZfcRbacTest\Asset\Permission;
use ZfcRbacTest\Asset\Role;
use ZfcRbacTest\Util\ServiceManagerFactory;

/**
 * @covers \ZfcRbac\Permission\ObjectRepositoryPermissionProvider
 */
class ObjectRepositoryPermissionProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    public function testObjectRepositoryProvider()
    {
        $this->serviceManager = ServiceManagerFactory::getServiceManager();
        $objectManager        = $this->getObjectManager();

        // Let's add some permissions
        $deletePermission = new Permission();
        $deletePermission->setName('delete');
        $objectManager->persist($deletePermission);

        $readPermission = new Permission();
        $readPermission->setName('read');
        $objectManager->persist($readPermission);

        $emptyPermission = new Permission();
        $emptyPermission->setName('empty');
        $objectManager->persist($emptyPermission);

        $objectManager->flush();

        // Let's add some roles
        $adminRole = new Role();
        $adminRole->setName('admin');
        $adminRole->addPermission($deletePermission);
        $objectManager->persist($adminRole);
        $objectManager->flush();

        $memberRole = new Role();
        $memberRole->setName('member');
        $memberRole->setParent($adminRole);
        $memberRole->addPermission($readPermission);
        $objectManager->persist($memberRole);
        $objectManager->flush();

        $objectRepository = $objectManager->getRepository('ZfcRbacTest\Asset\Permission');

        $objectRepositoryPermissionProvider = new ObjectRepositoryPermissionProvider($objectRepository);
        $rbacEvent                          = $this->getMock('ZfcRbac\Service\RbacEvent', [], [], '', false);

        $permissions = $objectRepositoryPermissionProvider->getPermissions($rbacEvent);

        $this->assertCount(3, $permissions);

        foreach ($permissions as $permission) {
            $this->assertInstanceOf('ZfcRbacTest\Asset\Permission', $permission);
        }
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getObjectManager()
    {
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $this->serviceManager->get('Doctrine\\ORM\\EntityManager');
        $schemaTool    = new SchemaTool($entityManager);

        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        return $entityManager;
    }
}
 