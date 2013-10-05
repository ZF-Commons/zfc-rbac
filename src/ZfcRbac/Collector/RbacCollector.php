<?php
namespace ZfcRbac\Collector;

use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\CollectorInterface;
use ZfcRbac\Service\Rbac as RbacService;

class RbacCollector implements CollectorInterface, \Serializable
{

    /**
     * Collector priority
     */
    const PRIORITY = 10;

    protected $name = 'zfcrbac';

    protected $rbacService;

    protected $roles = array();

    public function __construct(RbacService $rbacService)
    {
        $this->rbacService = $rbacService;
    }

    /**
     * Collector Name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
        if (! $this->rbacService) {
            return;
        }

        $rbac = $this->rbacService->getRbac();
        $this->roles = array();

        foreach ($rbac as $role) {

            $this->roles[] = $role;
        }
    }

    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            'name' => $this->name,
            'roles' => $this->roles
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->name = $data['name'];
        $this->roles = $data['roles'];
    }
}
