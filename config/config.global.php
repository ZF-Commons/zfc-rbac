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

/**
 * Copy-paste this file to your config/autoload folder (don't forget to remove the .dist extension!)
 */

return [
    'zfc_rbac' => [

        /**
         * Set the guest role
         *
         * This role is used by the authorization service when the authentication service returns no identity
         */
        // 'guest_role' => 'guest',

        /**
         * Configuration for role provider
         *
         * It must be an array that contains configuration for the role provider. The provider config
         * must follow the following format:
         *
         *      'ZfcRbac\Role\InMemoryRoleProvider' => [
         *          'role1' => [
         *              'children'    => ['children1', 'children2'], // OPTIONAL
         *              'permissions' => ['edit', 'read'] // OPTIONAL
         *          ]
         *      ]
         *
         * Supported options depend of the role provider, so please refer to the official documentation
         */
        'role_provider' => [],
    ]
];