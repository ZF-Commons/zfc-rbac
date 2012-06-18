<?php

namespace SpiffySecurity\Service;

use RuntimeException;
use SpiffySecurity\Service\Security;
use Zend\Acl\Acl;
use Zend\Acl\Role\RoleInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SecurityFactory implements FactoryInterface
{
    protected $firewallMap = array(
        'controller' => 'SpiffySecurity\Firewall\Controller',
        'route'      => 'SpiffySecurity\Firewall\Route'
    );

    protected $providerMap = array(
        'doctrine_dbal' => 'SpiffySecurity\Provider\DoctrineDBAL',
        'in_memory'     => 'SpiffySecurity\Provider\InMemory',
        'pdo'           => 'SpiffySecurity\Provider\PDO',
    );

    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get('Configuration');
        $config = $config['security'];

        if (!isset($config['role'])) {
            throw new RuntimeException('No role was specified');
        }

        $security = new Security($config);
        $options  = $security->options();

        foreach($options->getProvider() as $type => $provider) {
            $class = null;
            if (isset($this->providerMap[$type])) {
                $class = $this->providerMap[$type];
            } else if (class_exists($type)) {
                $class = $type;
            }

            $security->addProvider(new $class($sl, $provider));
        }

        foreach($options->getFirewall() as $type => $firewall) {
            $class = null;
            if (isset($this->firewallMap[$type])) {
                $class = $this->firewallMap[$type];
            } else if (class_exists($type)) {
                $class = $type;
            }

            $security->addFirewall(new $class($firewall));
        }

        $role = $options->getRole();
        if (is_string($role)) {
            if ($sl->has($role)) {
                $role = $sl->get($role);
            } else {
                $role = new \Zend\Acl\Role\GenericRole($role);
            }
        }

        if (is_object($role) && method_exists($role, 'getIdentity')) {
            $role = $role->getIdentity();
        }

        if ($role instanceof RoleInterface) {
            $security->setRole($role);
        }

        return $security;
    }
}