<?php

namespace SpiffySecurity\Provider\Role;

use SpiffySecurity\Rbac\Rbac;
use Zend\ServiceManager\ServiceLocatorInterface;

class InMemory implements RoleInterface
{
    protected $options;

    public function __construct(array $spec = array())
    {
        $this->options = new InMemoryOptions($spec);
    }

    /**
     * Load permissions into roles.
     *
     * @abstract
     * @param Rbac $rbac
     * @return mixed
     */
    public function load(Rbac $rbac)
    {
        $roles  = $this->options->getRoles();
        $result = array();
        foreach($roles as $role => $parents) {
            if (is_numeric($role)) {
                $role    = $parents;
                $parents = array();
            }
            if (empty($parents)) {
                $result[0][] = $role;
            }
            foreach($parents as $parent) {
                $result[$parent][] = $role;
            }
        }
        return $result;
    }

    /**
     * Factory to create the provider.
     *
     * @static
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     * @param mixed $spec
     * @return mixed
     */
    public static function factory(ServiceLocatorInterface $sl, array $spec)
    {
        return new \SpiffySecurity\Provider\Role\InMemory($spec);
    }
}
