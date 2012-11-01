<?php

namespace ZfcRbac\Service;

use RuntimeException;
use ZfcRbac\Service\Rbac;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class RbacFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get('Configuration');
        $config = $config['zfcrbac'];

        $rbac    = new Rbac($config);
        $options = $rbac->getOptions();

        foreach($options->getProviders() as $class => $config) {
            $rbac->addProvider($class::factory($sl, $config));
        }

        foreach($options->getFirewalls() as $class => $config) {
            $rbac->addFirewall(new $class($config));
        }

        $identity = $rbac->getOptions()->getIdentityProvider();
        if (!$sl->has($identity)) {
            throw new RuntimeException(sprintf(
                'An identity provider with the name "%s" does not exist',
                $identity
            ));
        }

        try {
            $rbac->setIdentity($sl->get($identity));
        } catch (ServiceNotFoundException $e) {
            throw new RuntimeException(sprintf(
                'Unable to set your identity - are you sure the alias "%s" is correct?',
                $identity
            ));
        }

        return $rbac;
    }
}