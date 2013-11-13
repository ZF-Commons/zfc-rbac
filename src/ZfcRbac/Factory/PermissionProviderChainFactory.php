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

namespace ZfcRbac\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Permission\PermissionProviderChain;

/**
 * Factory to create a permission provider chain
 */
class PermissionProviderChainFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return PermissionProviderChain
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        /* @var \ZfcRbac\Options\ModuleOptions $options */
        $options = $parentLocator->get('ZfcRbac\Options\ModuleOptions');
        $options = $options->getPermissionProviders();

        /* @var \ZfcRbac\Permission\PermissionProviderPluginManager $pluginManager */
        $pluginManager = $parentLocator->get('ZfcRbac\Permission\PermissionProviderPluginManager');

        $permissionProviders = [];

        foreach ($options as $type => $permissionProviderOptions) {
            $permissionProviders[] = $pluginManager->get($type, $permissionProviderOptions);
        }

        return new PermissionProviderChain($permissionProviders);
    }
}
