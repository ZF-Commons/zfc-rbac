<?php

namespace ZfcRbac;

class Module
{
    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $sm       = $app->getServiceManager();
        $security = $sm->get('ZfcRbac\Service\Rbac');
        $strategy = $sm->get('ZfcRbac\View\UnauthorizedStrategy');

        if ($security->options()->getFirewallRoute()) {
            $app->getEventManager()->attach('route', array('ZfcRbac\Firewall\Listener\Route', 'onRoute'), -1000);
        }

        if ($security->options()->getFirewallController()) {
            $app->getEventManager()->attach('route', array('ZfcRbac\Firewall\Listener\Controller', 'onRoute'), -1000);
        }

        $app->getEventManager()->attach($strategy);
    }

    public function getServiceConfig()
    {
        return array(
            'aliases' => array(
                'service.security' => 'ZfcRbac\Service\Rbac',
            ),
            'invokables' => array(
                'isGranted' => 'ZfcRbac\Controller\Plugin\IsGranted',
            ),
            'factories' => array(
                'ZfcRbac\Controller\Plugin\IsGranted' => function($sm) {
                    return new \ZfcRbac\Controller\Plugin\IsGranted(
                        $sm->get('ZfcRbac\Service\Rbac')
                    );
                },
                'ZfcRbac\View\UnauthorizedStrategy' => 'ZfcRbac\Service\UnauthorizedStrategyFactory',
                'ZfcRbac\Service\Rbac'              => 'ZfcRbac\Service\RbacFactory'
            )
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'ZfcRbac\View\Helper\IsGranted' => function($sm) {
                    $sl = $sm->getServiceLocator();
                    return new \ZfcRbac\View\Helper\IsGranted(
                        $sl->get('ZfcRbac\Service\Rbac')
                    );
                },
            )
        );
    }
    
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }
}