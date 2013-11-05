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

namespace ZfcRbac\Permission;

use ZfcRbac\Service\RbacEvent;

/**
 * A permission provider is an object that returns a list of permissions
 *
 * Permission provider must return permissions under the following formats:
 *      - an array that map permission name to a single role: array('permissionName' => 'role')
 *      - an array that map permission name to multiple roles:
 *          array(
 *              'permissionName' => array(
 *                  'roles' => array('role1', 'role2')
 *              )
 *          )
 *      - an array that map permission name to multiple roles with an assertion
 *          array(
 *              'permissionName' => array(
 *                  'roles'     => array('role1', 'role2'),
 *                  'assertion' => 'MyPermission' // either a string, callable or AssertionInterface
 *              )
 *          )
 */
interface PermissionProviderInterface
{
    /**
     * Get the permissions from the provider
     *
     * @param  RbacEvent $event
     * @return array
     */
    public function getPermissions(RbacEvent $event);
} 