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

return [
    'dependencies' => [
        'factories' => [
            /* Factories that do not map to a class */
            'ZfcRbac\Guards'                                              => ZfcRbac\Container\GuardsFactory::class,

            /* Factories that map to a class */
            ZfcRbac\Middleware\ZendAuthenticationServiceMiddleware::class => \ZfcRbac\Container\ZendAuthenticationServiceMiddlewareFactory::class,
            ZfcRbac\Assertion\AssertionPluginManager::class               => ZfcRbac\Container\AssertionPluginManagerFactory::class,
            ZfcRbac\Identity\AuthenticationIdentityProvider::class        => ZfcRbac\Container\AuthenticationIdentityProviderFactory::class,
            ZfcRbac\Options\ModuleOptions::class                          => ZfcRbac\Container\ModuleOptionsFactory::class,
            ZfcRbac\Role\RoleProviderPluginManager::class                 => ZfcRbac\Container\RoleProviderPluginManagerFactory::class,
            ZfcRbac\Service\AuthorizationService::class                   => ZfcRbac\Container\AuthorizationServiceFactory::class,
            ZfcRbac\Service\RoleService::class                            => ZfcRbac\Container\RoleServiceFactory::class,
            ZfcRbac\Helper\IsGranted::class                               => ZfcRbac\Container\IsGrantedHelperFactory::class,
            ZfcRbac\Helper\HasRole::class                                 => ZfcRbac\Container\HasRoleHelperFactory::class
        ],
    ],

    'zfc_rbac' => [
        // Guard plugin manager
        'guard_manager'         => [],

        // Role provider plugin manager
        'role_provider_manager' => [],

        // Assertion plugin manager
        'assertion_manager'     => []
    ]
];