<?php

namespace SpiffySecurity\Provider;

use DomainException;
use Zend\ServiceManager\ServiceLocatorInterface;

class InMemory implements ProviderInterface
{
    /**
     * @var array
     */
    protected $roles;

    public function __construct(array $roles = array())
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
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
        return new \SpiffySecurity\Provider\InMemory($spec);
    }
}
