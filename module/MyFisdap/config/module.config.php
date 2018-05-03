<?php
namespace MyFisdap;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'my-fisdap' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/my-fisdap[/:action[/:id]]',
					'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\MyFisdapController::class,
                        'action'     => 'myfisdap',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'myfisdap' => __DIR__ . '/../view',
        ],
    ],
];
