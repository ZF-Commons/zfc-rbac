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

namespace ZfcRbac\Container;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use ZfcRbac\Exception\RuntimeException;
use ZfcRbac\Identity\IdentityProviderInterface;
use ZfcRbac\Options\ModuleOptions;
use ZfcRbac\Role\RoleProviderInterface;
use ZfcRbac\Role\RoleProviderPluginManager;
use ZfcRbac\Service\RoleService;

/**
 * Factory to create the role service
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class RoleServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return RoleService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /* @var ModuleOptions $moduleOptions */
        $moduleOptions = $container->get(ModuleOptions::class);

        /* @var IdentityProviderInterface $identityProvider */
        $identityProvider = $container->get($moduleOptions->getIdentityProvider());

        $roleProviderConfig = $moduleOptions->getRoleProvider();

        if (empty($roleProviderConfig)) {
            throw new RuntimeException('No role provider has been set for ZfcRbac');
        }

        /* @var RoleProviderPluginManager $pluginManager */
        $pluginManager = $container->get(RoleProviderPluginManager::class);

        /* @var RoleProviderInterface $roleProvider */
        $roleProvider = $pluginManager->get(key($roleProviderConfig), current($roleProviderConfig));

        $roleService = new RoleService($identityProvider, $roleProvider);
        $roleService->setGuestRole($moduleOptions->getGuestRole());

        return $roleService;
    }
}
