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

return array(
    'service_manager' => array(
        'factories' => array(
            /* Factories that do not map to a class */
            'ZfcRbac\Cache'                                   => 'ZfcRbac\Factory\CacheFactory',
            'ZfcRbac\Guards'                                  => 'ZfcRbac\Factory\GuardsFactory',

            /* Factories that map to a class */
            'ZfcRbac\Guard\ControllerGuard'                   => 'ZfcRbac\Factory\ControllerGuardFactory',
            'ZfcRbac\Guard\GuardPluginManager'                => 'ZfcRbac\Factory\GuardPluginManagerFactory',
            'ZfcRbac\Guard\RouteGuard'                        => 'ZfcRbac\Factory\RouteGuardFactory',
            'ZfcRbac\Identity\AuthenticationIdentityProvider' => 'ZfcRbac\Factory\AuthenticationIdentityProviderFactory',
            'ZfcRbac\Options\ModuleOptions'                   => 'ZfcRbac\Factory\ModuleOptionsFactory',
            'ZfcRbac\Permission\PermissionLoaderListener'     => 'ZfcRbac\Factory\PermissionLoaderListenerFactory',
            'ZfcRbac\Permission\PermissionProviderChain'      => 'ZfcRbac\Factory\PermissionProviderChainFactory',
            'ZfcRbac\Role\RoleLoaderListener'                 => 'ZfcRbac\Factory\RoleLoaderListenerFactory',
            'ZfcRbac\Role\RoleProviderChain'                  => 'ZfcRbac\Factory\RoleProviderChainFactory',
            'ZfcRbac\Service\AuthorizationService'            => 'ZfcRbac\Factory\AuthorizationServiceFactory'
        )
    ),

    'view_helpers' => array(
        'factories' => array(
            'ZfcRbac\View\Helper\IsGranted' => 'ZfcRbac\Factory\IsGrantedViewHelperFactory'
        ),
        'aliases' => array(
            'isGranted' => 'ZfcRbac\View\Helper\IsGranted'
        )
    ),

    'controller_plugins' => array(
        'factories' => array(
            'ZfcRbac\Mvc\Controller\Plugin\IsGranted' => 'ZfcRbac\Factory\IsGrantedPluginFactory'
        ),
        'aliases' => array(
            'isGranted' => 'ZfcRbac\Mvc\Controller\Plugin\IsGranted'
        )
    ),

    'zfc_rbac' => array(
        'unauthorized_strategy' => array(),
        'redirect_strategy'     => array(),

        // Plugin managers
        'guard_manager' => array()
    )
);
