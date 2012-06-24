<?php
return array(
    'security' => array(
        // put your options here
        // see SpiffySecurity\Service\SecurityOptions for options
    ),
    'view_manager' => array(
        'helper_map' => array(
            'isGranted' => 'SpiffySecurity\View\Helper\IsGranted',
        ),
        'template_path_stack' => array(__DIR__ . '/../view'),
    )
);