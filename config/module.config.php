<?php
return array(
    'security' => array(
        'providers' => array(
            'SpiffySecurity\Provider\ZendDb' => array(
                'adapter' => 'Zend\Db\Adapter\Adapter', // alias to your db adapter
                'options' => array(
                    'table'       => 'role',
                    'name_column' => 'name',
                )
            )
        ),

        'firewalls' => array(
            'SpiffySecurity\Firewall\Controller' => array(
                array('controller' => 'home', 'roles' => array('guest', 'member'))
            ),
            'SpiffySecurity\Firewall\Route' => array(
                array('route' => '^events/*', 'roles' => 'member'),
                array('route' => '^profiles/*', 'roles' => 'member'),
                array('route' => '^teams/*', 'roles' => 'member'),
                array('route' => '^spiffyadmin/*', 'roles' => 'administrator')
            )
        ),
    ),
    'view_manager' => array(
        'helper_map' => array(
            'isGranted' => 'SpiffySecurity\View\Helper\IsGranted',
        ),
        'template_path_stack' => array(__DIR__ . '/../view'),
    )
);