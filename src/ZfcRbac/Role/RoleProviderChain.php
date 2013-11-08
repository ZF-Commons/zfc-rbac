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

use ZfcRbac\Service\RbacEvent;

/**
 * Simple implementation for a role provider chain
 */
class RoleProviderChain implements RoleProviderInterface
{
    /**
     * List of role providers
     *
     * @var RoleProviderInterface[]
     */
    private $roleProviders;

    /**
     * Constructor
     *
     * @param RoleProviderInterface[]|array $roleProviders
     */
    public function __construct(array $roleProviders = array())
    {
        $this->roleProviders = $roleProviders;
    }

    /**
     * Add a role provider in the chain
     *
     * @param  RoleProviderInterface $roleProvider
     * @return void
     */
    public function addRoleProvider(RoleProviderInterface $roleProvider)
    {
        $this->roleProviders[] = $roleProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles(RbacEvent $event)
    {
        $roles = array();

        foreach ($this->roleProviders as $roleProvider) {
            $roles = array_merge($roles, $roleProvider->getRoles($event));
        }

        return $roles;
    }
}
