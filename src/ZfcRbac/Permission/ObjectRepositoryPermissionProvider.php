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

namespace ZfcRbac\Permission;

use Doctrine\Common\Persistence\ObjectRepository;
use Zend\Permissions\Rbac\RoleInterface;
use ZfcRbac\Service\RbacEvent;

/**
 * Permission provider that uses Doctrine repository to fetch permissions
 *
 * This is ideal for small websites with few permissions
 */
class ObjectRepositoryPermissionProvider implements PermissionProviderInterface
{
    /**
     * @var ObjectRepository
     */
    protected $objectRepository;

    /**
     * Constructor
     *
     * @param ObjectRepository $objectRepository
     */
    public function __construct(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissions(RbacEvent $event)
    {
        $permissions        = $this->objectRepository->findAll();
        $cleanedPermissions = array();

        foreach ($permissions as $permission) {
            // @TODO: enforce permission type once ZF2 has a PermissionInterface
            $permissionName = $permission->getName();
            $roles          = $permission->getRoles();

            foreach ($roles as $role) {
                if ($role instanceof RoleInterface) {
                    $cleanedPermissions[$permissionName][] = $role->getName();
                } else {
                    $cleanedPermissions[$permissionName][] = $role;
                }
            }
        }

        return $cleanedPermissions;
    }
}