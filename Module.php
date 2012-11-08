<?php

namespace ZfcRbac;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use ZfcRbac\Collector\RbacCollector;

class Module implements
    BootstrapListenerInterface,
    ConfigProviderInterface,
    ServiceProviderInterface,
    ViewHelperProviderInterface
{
    /**
     * @param  EventInterface $e
     * @return array|void
     */
    public function onBootstrap(EventInterface $e)
    {
        $app         = $e->getTarget();
        $sm          = $app->getServiceManager();
        $rbacService = $sm->get('ZfcRbac\Service\Rbac');
        $strategy    = $sm->get('ZfcRbac\View\UnauthorizedStrategy');

        if ($rbacService->getOptions()->getFirewallRoute()) {
            $app->getEventManager()->attach('route', array('ZfcRbac\Firewall\Listener\Route', 'onRoute'), -1000);
        }

        if ($rbacService->getOptions()->getFirewallController()) {
            $app->getEventManager()->attach('route', array('ZfcRbac\Firewall\Listener\Controller', 'onRoute'), -1000);
        }

        $app->getEventManager()->attach($strategy);
    }

    /**
     * @return array|\Zend\ServiceManager\Config
     */
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
                    return new Controller\Plugin\IsGranted($sm->get('ZfcRbac\Service\Rbac'));
                },
                'ZfcRbac\Collector\RbacCollector' => function($sm) {
                    return new RbacCollector($sm->get('ZfcRbac\Service\Rbac'));
                },
                'ZfcRbac\View\UnauthorizedStrategy' => 'ZfcRbac\Service\UnauthorizedStrategyFactory',
                'ZfcRbac\Service\Rbac' => 'ZfcRbac\Service\RbacFactory'
            )
        );
    }

    /**
     * @return array|\Zend\ServiceManager\Config
     */
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'ZfcRbac\View\Helper\IsGranted' => function($sm) {
                    $sl = $sm->getServiceLocator();
                    return new View\Helper\IsGranted($sl->get('ZfcRbac\Service\Rbac'));
                },
            )
        );
    }

    /**
     * @return array|mixed|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}