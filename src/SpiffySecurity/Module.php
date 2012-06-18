<?php

namespace SpiffySecurity;

class Module
{
    public function onBootstrap($e)
    {
        $app      = $e->getTarget();
        $sm       = $app->getServiceManager();
        $security = $sm->get('SpiffySecurity\Service\Security');
        $strategy = $sm->get('SpiffySecurity\View\UnauthorizedStrategy');

        if ($security->options()->getFirewallRoute()) {
            $app->events()->attach('route', array('SpiffySecurity\Firewall\Listener\Route', 'onRoute'), -1000);
        }

        if ($security->options()->getFirewallController()) {
            $app->events()->attach('route', array('SpiffySecurity\Firewall\Listener\Controller', 'onRoute'), -1000);
        }

        $app->events()->attach($strategy);
    }

    public function getServiceConfiguration()
    {
        return array(
            'aliases' => array(
                'service.security' => 'SpiffySecurity\Service\Security',
            ),
            'factories' => array(
                'SpiffySecurity\View\UnauthorizedStrategy' => 'SpiffySecurity\Service\UnauthorizedStrategyFactory',
                'SpiffySecurity\Service\Security'          => 'SpiffySecurity\Service\SecurityFactory'
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}