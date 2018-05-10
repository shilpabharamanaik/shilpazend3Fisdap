<?php

namespace Community;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'community' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/community',
                    'defaults' => [
                        'controller' => Controller\CommunityController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'community' => __DIR__ . '/../view',
        ],
    ],
];
