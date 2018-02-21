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

return [
    'dependencies' => [
        'factories' => [
            ZfcRbac\Assertion\AssertionPluginManager::class      => ZfcRbac\Container\AssertionPluginManagerFactory::class,
            ZfcRbac\Options\ModuleOptions::class                 => ZfcRbac\Container\ModuleOptionsFactory::class,
            ZfcRbac\Role\InMemoryRoleProvider::class             => ZfcRbac\Container\InMemoryRoleProviderFactory::class,
            ZfcRbac\Role\ObjectRepositoryRoleProvider::class     => ZfcRbac\Container\ObjectRepositoryRoleProviderFactory::class,
            ZfcRbac\Service\AuthorizationServiceInterface::class => ZfcRbac\Container\AuthorizationServiceFactory::class,
            ZfcRbac\Service\RoleServiceInterface::class          => ZfcRbac\Container\RoleServiceFactory::class,
            ZfcRbac\Rbac::class                                  => \Zend\ServiceManager\Factory\InvokableFactory::class,
        ],
    ],

    'zfc_rbac' => [
        // Role provider plugin manager
        'role_provider_manager' => [],

        // Assertion plugin manager
        'assertion_manager' => [],
    ],
];
