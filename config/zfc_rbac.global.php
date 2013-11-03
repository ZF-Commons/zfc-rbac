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
         * Please note that when an identity is found, it MUST implements the Zend\Permissions\Rbac\IdentityInterface
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
        // 'create_missing_roles' => false,

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
         *
         * You can set route guards using the "route_rules" sub key, or the controller guards using the
         * "controller_rules" sub key. Please refer to the documentation for the exact syntax
         */
        // 'guards' => array()
    )
);