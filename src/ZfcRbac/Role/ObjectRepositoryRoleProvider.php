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

namespace ZfcRbac\Role;

use Doctrine\Common\Persistence\ObjectRepository;
use ZfcRbac\Service\RbacEvent;

/**
 * Role provider that uses Doctrine object repository to fetch roles
 *
 * This provider optionally accepts a role property name that allow to filter roles when retrieving them,
 * instead of retrieving them all in memory
 */
class ObjectRepositoryRoleProvider implements RoleProviderInterface
{
    /**
     * @var ObjectRepository
     */
    private $objectRepository;

    /**
     * @var string
     */
    private $roleNameProperty;

    /**
     * Constructor
     *
     * @param ObjectRepository $objectRepository
     * @param string           $roleNameProperty
     */
    public function __construct(ObjectRepository $objectRepository, $roleNameProperty = '')
    {
        $this->objectRepository = $objectRepository;
        $this->roleNameProperty = $roleNameProperty;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles(RbacEvent $event)
    {
        $roles = $event->getRoles();

        if (!empty($this->roleNameProperty) && !empty($roles)) {
            return $this->objectRepository->findBy([$this->roleNameProperty => $roles]);
        }

        return $this->objectRepository->findAll();
    }
}
