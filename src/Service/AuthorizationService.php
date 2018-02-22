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

declare(strict_types=1);

namespace ZfcRbac\Service;

use ZfcRbac\Assertion\AssertionPluginManager;
use ZfcRbac\Assertion\AssertionSet;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Rbac;

/**
 * Authorization service is a simple service that internally uses Rbac to check if identity is
 * granted a permission
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
final class AuthorizationService implements AuthorizationServiceInterface
{
    /**
     * @var Rbac
     */
    private $rbac;

    /**
     * @var RoleServiceInterface
     */
    private $roleService;

    /**
     * @var AssertionPluginManager
     */
    private $assertionPluginManager;

    /**
     * @var array
     */
    private $assertions;

    public function __construct(
        Rbac $rbac,
        RoleServiceInterface $roleService,
        AssertionPluginManager $assertionPluginManager,
        array $assertions = []
    ) {
        $this->rbac = $rbac;
        $this->roleService = $roleService;
        $this->assertionPluginManager = $assertionPluginManager;
        $this->assertions = $assertions;
    }

    public function isGranted(?IdentityInterface $identity, string $permission, $context = null): bool
    {
        $roles = $this->roleService->getIdentityRoles($identity, $context);

        if (empty($roles)) {
            return false;
        }

        if (! $this->rbac->isGranted($roles, $permission)) {
            return false;
        }

        if (! empty($this->assertions[$permission])) {
            $assertionSet = new AssertionSet($this->assertionPluginManager, $this->assertions);

            return $assertionSet->assert($permission, $identity, $context);
        }

        return true;
    }
}
