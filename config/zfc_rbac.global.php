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
    'zfc_rbac' => array(
        /**
         * Key that is used to fetch the identity provider
         *
         * Please note that when an identity is found, it MUST implements the ZfcRbac\Identity\IdentityInterface
         * interface, otherwise it will throw an exception.
         */
        // 'identity_provider' => 'ZfcRbac\Identity\AuthenticationIdentityProvider',

        /**
         * This option allows to specify if you want the Rbac container to automatically create roles inside the
         * container when it has not been added
         *
         * For instance, if you have a role that has a parent role that has not been added yet, if you set this
         * option to true, then the parent role will be created
         */
        // 'create_missing_roles' => true,

        /**
         * Set the guest role
         *
         * This role is used by the authorization service when the authentication service returns no identity
         */
        // 'guest_role' => 'guest',

        /**
         * Set the default role
         *
         * This role is used by the authorization service when the authentication service returns an identity,
         * but that no role is set
         */
        // 'default_role' => 'member',

        /**
         * Set the guards options
         */
        'guards' => array(
            /**
             * Specify your route rules. You can use regex as route names.
             *
             * Route rules have the following format:
             *      array('routeRegex' => array('role1', 'role2')
             *
             * Please note that the relationship between roles are taken into account here. For more information,
             * pleaser refer to the documentation about guards
             */
            // 'route_rules' => array(),

            /**
             * Specify your controller rules.
             *
             * Controller rules have the following format:
             *      array(
             *          array(
             *              'controller' => 'MyController',
             *              'actions'    => array() // optional
             *              'roles'      => array('role1', 'role2')
             *          )
             *      )
             */
            // 'controller_rules' => array(),

            /**
             * As soon as one rule for either route or controller is specified, a guard will be automatically
             * created and will start to hook into the MVC loop.
             *
             * If the protection policy is set to DENY (default), then any route/controller will be denied by
             * default UNLESS it is explicitly added as a rule. On the other hand, if it is set to ALLOW, then
             * not specified route/controller will be implicitly approved.
             *
             * DENY is the most secure way, but it is more work for the developer
             */
            // protection_policy = GuardInterface::DENY,
        )
    )
);