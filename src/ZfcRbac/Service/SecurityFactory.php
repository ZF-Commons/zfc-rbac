<?php

namespace ZfcRbac\Service;

use InvalidArgumentException;
use RuntimeException;
use ZfcRbac\Service\Security;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class SecurityFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $config = $sl->get('Configuration');
        $config = $config['security'];

        $security = new Security($config);
        $options  = $security->options();

        foreach($options->getProviders() as $class => $config) {
            $security->addProvider($class::factory($sl, $config));
        }

        foreach($options->getFirewalls() as $class => $config) {
            $security->addFirewall(new $class($config));
        }

        $identity = $security->options()->getIdentityProvider();
        if (!$sl->has($identity)) {
            throw new RuntimeException(sprintf(
                'An identity provider with the name "%s" does not exist',
                $identity
            ));
        }

        try {
            $security->setIdentity($sl->get($identity));
        } catch (ServiceNotFoundException $e) {
            throw new RuntimeException(sprintf(
                'Unable to set your identity - are you sure the alias "%s" is correct?',
                $identity
            ));
        }

        return $security;
    }
}