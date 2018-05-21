<?php

namespace Learningcenter;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'learningcenter' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/learning-center',
                    'defaults' => [
                        'controller' => Controller\LearningcenterController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
      'controllers' => [
        'factories' => [
            Controller\LearningcenterController::class => Controller\Factory\LearningcenterControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'learningcenter' => __DIR__ . '/../view',
        ],
    ],
];
