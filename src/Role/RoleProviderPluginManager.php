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

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use ZfcRbac\Container\ObjectRepositoryRoleProviderFactory;

/**
 * Plugin manager to create role providers
 *
 * @method RoleProviderInterface get($name)
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @license MIT
 */
class RoleProviderPluginManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    protected $invokableClasses = [
        InMemoryRoleProvider::class => InMemoryRoleProvider::class
    ];

    /**
     * @var array
     */
    protected $factories = [
        ObjectRepositoryRoleProvider::class => ObjectRepositoryRoleProviderFactory::class
    ];

    /**
     * {@inheritDoc}
     */
    public function validate($instance): void
    {
        if ($instance instanceof RoleProviderInterface) {
            return; // we're okay
        }

        throw new InvalidServiceException(sprintf(
            'Role provider must implement "%s", but "%s" was given',
            RoleProviderInterface::class,
            is_object($instance) ? get_class($instance) : gettype($instance)
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin): void
    {
        $this->validate($plugin);
    }

    /**
     * {@inheritDoc}
     */
    protected function canonicalizeName($name)
    {
        return $name;
    }
}
