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

namespace ZfcRbac\Service;

use Rbac\Role\RoleInterface;
use Traversable;
use ZfcRbac\Exception;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Role\RoleProviderInterface;

/**
 * Role service
 */
class RoleService
{
    /**
     * @var RoleProviderInterface
     */
    protected $roleProvider;

    /**
     * @var string
     */
    protected $guestRole;

    /**
     * Constructor
     *
     * @param string                $guestRole
     * @param RoleProviderInterface $roleProvider
     */
    public function __construct(RoleProviderInterface $roleProvider, $guestRole = '')
    {
        $this->roleProvider = $roleProvider;
        $this->guestRole    = $guestRole;
    }

    /**
     * Get the identity roles from the identity, applying some more logic
     *
     * @param  IdentityInterface|null $identity
     * @return RoleInterface[]
     * @throws Exception\RuntimeException
     */
    public function getIdentityRoles(IdentityInterface $identity = null)
    {
        if (null === $identity) {
            return $this->convertRoles([$this->guestRole]);
        }

        if (!$identity instanceof IdentityInterface) {
            throw new Exception\RuntimeException(sprintf(
                'ZfcRbac expects your identity to implement ZfcRbac\Identity\IdentityInterface, "%s" given',
                is_object($identity) ? get_class($identity) : gettype($identity)
            ));
        }

        return $this->convertRoles($identity->getRoles());
    }

    /**
     * Convert the roles (potentially strings) to concrete RoleInterface objects using role provider
     *
     * @param  array|Traversable $roles
     * @return RoleInterface[]
     */
    protected function convertRoles($roles)
    {
        if ($roles instanceof Traversable) {
            $roles = iterator_to_array($roles);
        }

        $collectedRoles = [];
        $toCollect      = [];

        foreach ($roles as $role) {
            // If it's already a RoleInterface, nothing to do as a RoleInterface contains everything already
            if ($role instanceof RoleInterface) {
                $collectedRoles[] = $role;
            }

            // Otherwise, it's a string and hence we need to collect it
            $toCollect[] = (string) $role;
        }

        // Nothing to collect, we don't even need to hit the (potentially) costly role provider
        if (empty($toCollect)) {
            return $collectedRoles;
        }

        return array_merge($collectedRoles, $this->roleProvider->getRoles($toCollect));
    }
}
