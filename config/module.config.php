<?php
return array(
    'security' => array(
        'role_providers' => array(
        ),

        'firewalls' => array(),
    ),
    'view_manager' => array(
        'helper_map' => array(
            'isGranted' => 'SpiffySecurity\View\Helper\IsGranted',
        ),
        'template_path_stack' => array(__DIR__ . '/../view'),
    )
);