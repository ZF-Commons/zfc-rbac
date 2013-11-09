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

namespace ZfcRbac\Collector;

use Serializable;
use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\CollectorInterface;

/**
 * RbacCollector
 */
class RbacCollector implements CollectorInterface, Serializable
{
    /**
     * Collector priority
     */
    const PRIORITY = 10;

    /**
     * @var array
     */
    protected $collectedGuards = array();

    /**
     * @var array
     */
    protected $collectedRoles = array();

    /**
     * @var array
     */
    protected $collectedPermissions = array();

    /**
     * @var array
     */
    protected $collectedOptions = array();

    /**
     * Collector Name.
     *
     * @return string
     */
    public function getName()
    {
        return 'zfc_rbac';
    }

    /**
     * Collector Priority.
     *
     * @return integer
     */
    public function getPriority()
    {
        return self::PRIORITY;
    }

    /**
     * Collects data.
     *
     * @param MvcEvent $mvcEvent
     */
    public function collect(MvcEvent $mvcEvent)
    {
        if (!$application = $mvcEvent->getApplication()) {
            return;
        }

        $serviceManager = $mvcEvent->getApplication()->getServiceManager();

        /* @var \ZfcRbac\Options\ModuleOptions $options */
        $options = $serviceManager->get('ZfcRbac\Options\ModuleOptions');

        /* @var \ZfcRbac\Service\AuthorizationService $authorizationService */
        $authorizationService = $serviceManager->get('ZfcRbac\Service\AuthorizationService');

        // Let's collect interesting options...
        $this->collectedOptions = array(
            'current_roles'     => $authorizationService->getIdentityProvider()->getIdentityRoles(),
            'guest_role'        => $options->getGuestRole(),
            'protection_policy' => $options->getProtectionPolicy()
        );

        // Now for guards
        $this->collectedGuards = array();
        foreach ($options->getGuards() as $type => $rules) {
            $this->collectedGuards[$type] = $rules;
        }

        /*


                $rbacConfig = $config['zfcrbac'];
                $this->collectedOptions = $rbacConfig;
                $identityProvider = $sm->get($rbacConfig['identity_provider']);
                $rbacService = $sm->get('ZfcRbac\Service\Rbac');
                if (method_exists($identityProvider, 'getIdentity') && method_exists($identityProvider, 'hasIdentity')) {
                    if ($identityProvider->hasIdentity()) {
                        $identity = $identityProvider->getIdentity();
                        $this->collectedRoles = $identity->getRoles();
                    }
                } else {
                    $rbac = $rbacService->getRbac();
                    $roles = array();
                    foreach ($rbac as $role) {
                        $roles[] = $role->getName();
                    }
                    $this->collectedRoles = $roles;
                }
                $rbacOptions = $rbacService->getOptions();
                $this->collectedFirewalls = $rbacOptions->firewalls;*/
    }

    /**
     * @return array|string[]
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize
        (
            array
            (
                'guards'      => $this->collectedGuards,
                'roles'       => $this->collectedRoles,
                'permissions' => $this->collectedPermissions,
                'options'     => $this->collectedOptions
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        $this->collection = unserialize($serialized);
    }
}
