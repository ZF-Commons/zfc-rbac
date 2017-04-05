<?php

declare(strict_types=1);
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

use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Assertion\AssertionPluginManager;
use ZfcRbac\Exception;
use ZfcRbac\Identity\IdentityInterface;
use ZfcRbac\Rbac\Rbac;

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
    private $assertions = [];

    public function __construct(
        Rbac $rbac,
        RoleServiceInterface $roleService,
        AssertionPluginManager $assertionPluginManager
    ) {
        $this->rbac                   = $rbac;
        $this->roleService            = $roleService;
        $this->assertionPluginManager = $assertionPluginManager;
    }

    /**
     * Set an assertion
     *
     * @param string                             $permission
     * @param string|callable|AssertionInterface $assertion
     * @return void
     */
    public function setAssertion(string $permission, $assertion): void
    {
        $this->assertions[$permission] = $assertion;
    }

    public function setAssertions(array $assertions): void
    {
        $this->assertions = $assertions;
    }

    public function hasAssertion(string $permission): bool
    {
        return isset($this->assertions[$permission]);
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(IdentityInterface $identity = null, string $permission, $context = null): bool
    {
        $roles = $this->roleService->getIdentityRoles($identity, $context);

        if (empty($roles)) {
            return false;
        }

        if (! $this->rbac->isGranted($roles, $permission)) {
            return false;
        }

        if ($this->hasAssertion($permission)) {
            return $this->assert($this->assertions[(string) $permission], $permission, $identity, $context);
        }

        return true;
    }

    /**
     * @param string|callable|AssertionInterface $assertion
     * @param string                             $permission
     * @param IdentityInterface                  $identity
     * @param mixed                              $context
     * @return bool
     */
    private function assert($assertion, string $permission, IdentityInterface $identity = null, $context = null): bool
    {
        if (is_callable($assertion)) {
            return $assertion($permission, $identity, $context);
        }

        if ($assertion instanceof AssertionInterface) {
            return $assertion->assert($permission, $identity, $context);
        }

        if (is_string($assertion)) {
            $assertion = $this->assertionPluginManager->get($assertion);

            return $assertion->assert($permission, $identity, $context);
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Assertion must be callable, string or implement ZfcRbac\Assertion\AssertionInterface, "%s" given',
            is_object($assertion) ? get_class($assertion) : gettype($assertion)
        ));
    }
}
