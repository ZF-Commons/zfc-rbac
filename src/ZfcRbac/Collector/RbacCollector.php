<?php

namespace ZfcRbac\Collector;

use Serializable;
use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\CollectorInterface;

class RbacCollector implements CollectorInterface, Serializable {
    /**
     * Collector priority
     */

    const PRIORITY = 10;

    /**
     * @var array|null
     */
    protected $collectedRoles = array();
    /**
     * @var array|null
     */
    protected $collectedFirewalls = array();
    /**
     * @var array|null
     */
    protected $collectedOptions = array();

    /**
     * Collector Name.
     *
     * @return string
     */
    public function getName() {
        return 'zfcrbac';
    }

    /**
     * Collector Priority.
     *
     * @return integer
     */
    public function getPriority() {
        return self::PRIORITY;
    }

    /**
     * Collects data.
     *
     * @param MvcEvent $mvcEvent
     */
    public function collect(MvcEvent $mvcEvent) {
        if (!$application = $mvcEvent->getApplication()) {
            return;
        }
        $sm = $mvcEvent->getApplication()->getServiceManager();
        $config = $sm->get('Config');
        $rbacConfig = $config['zfcrbac'];
        $this->collectedOptions = $rbacConfig;
        $identityProvider = $sm->get($rbacConfig['identity_provider']);
        $rbacService = $sm->get('ZfcRbac\Service\Rbac');
        if (method_exists($identityProvider,'getIdentity')) {
            $identity = $identityProvider->getIdentity();
            $this->collectedRoles = $identity->getRoles();
        }else{
            $rbac = $rbacService->getRbac();
            $roles = array();
            foreach ($rbac as $role){
                $roles[] = $role->getName();
            }
            $this->collectedRoles = $roles;
        }
        $rbacOptions = $rbacService->getOptions();
        $this->collectedFirewalls = $rbacOptions->firewalls;
    }

    /**
     * @return array|string[]
     */
    public function getCollection() {
        return $this->collection;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize() {
        return serialize(
                array(
                    'roles' => $this->collectedRoles,
                    'firewalls' => $this->collectedFirewalls,
                    'options' => $this->collectedOptions
                )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized) {
        $this->collection = unserialize($serialized);
    }

}
