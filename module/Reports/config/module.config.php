<?php

namespace Reports;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'reports' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/reports/index/splash',
                    'defaults' => [
                        'controller' => Controller\ReportsController::class,
                        'action'     => 'splash',
                    ],
                ],
            ],
             'allreports' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/reports',
                    'defaults' => [
                        'controller' => Controller\ReportsController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\ReportsController::class => Controller\Factory\ReportsControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'reports' => __DIR__ . '/../view',
        ],
    ],
];
