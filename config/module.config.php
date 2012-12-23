<?php
return array(
    'zfcrbac' => array(
        // put your options here
        // see ZfcRbac\Service\RbacOptions for options
    ),
    'view_manager' => array(
        'helper_map' => array(
            'isGranted' => 'ZfcRbac\View\Helper\IsGranted',
        ),
        'template_path_stack' => array(__DIR__ . '/../view'),
    ),

    'zenddevelopertools' => array(
        'profiler' => array(
            'collectors' => array(
                'zfcrbac' => 'ZfcRbac\Collector\RbacCollector',
            ),
        ),
        'toolbar' => array(
            'entries' => array(
                'zfcrbac' => 'zend-developer-tools/toolbar/zfcrbac',
            ),
        ),
    ),
);