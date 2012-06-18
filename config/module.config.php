<?php
return array(
    'security' => array(
        'firewall' => array(
            /*
            'controller' => array(
                array('controller' => 'profiles', 'action' => 'index', 'roles' => 'member')
            ),
            'route' => array(
                array('route' => 'profiles', 'roles' => 'member'),
                array('route' => 'admin', 'roles' => 'administrator')
            ),*/
        ),

        'provider' => array(
            /*'in_memory' => array(
                'moderator' => 'member',
                'admin'     => 'moderator',
                'guest',
                'member'    => 'guest',
            ),
            'doctrine_dbal' => array(
                'connection'         => 'doctrine.connection.orm_default',
                'table'              => 'role',
                'role_id_column'     => 'id',
                'role_name_column'   => 'name',
                'parent_join_column' => 'parent_role_id'
            )*/
        )
    ),
    'view_manager' => array(
        'helper_map' => array(
            'isGranted' => 'SpiffySecurity\View\Helper\IsGranted',
        ),
        'template_path_stack' => array(__DIR__ . '/../view'),
    )
);