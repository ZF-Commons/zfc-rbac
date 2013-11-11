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

use Zend\EventManager\EventManager;
use Zend\Permissions\Rbac\Rbac;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\AuthorizationService;

/**
 * Factory to create the authorization service
 */
class AuthorizationServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     * @return AuthorizationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var \ZfcRbac\Options\ModuleOptions $moduleOptions */
        $moduleOptions    = $serviceLocator->get('ZfcRbac\Options\ModuleOptions');

        /* @var \ZfcRbac\Identity\IdentityProviderInterface $identityProvider */
        $identityProvider = $serviceLocator->get($moduleOptions->getIdentityProvider());

        $rbac = new Rbac();
        $rbac->setCreateMissingRoles($moduleOptions->getCreateMissingRoles());

        // We need to register the guest role inside the container
        if ($guestRole = $moduleOptions->getGuestRole()) {
            $rbac->addRole($guestRole);
        }

        // Create the event manager and add some events
        $eventManager = new EventManager();
        $eventManager->attach($serviceLocator->get('ZfcRbac\Role\RoleLoaderListener'));
        $eventManager->attach($serviceLocator->get('ZfcRbac\Permission\PermissionLoaderListener'));

        $authorizationService = new AuthorizationService($rbac, $identityProvider);
        $authorizationService->setEventManager($eventManager);
        $authorizationService->setForceReload($moduleOptions->getForceReload());

        return $authorizationService;
    }
}
