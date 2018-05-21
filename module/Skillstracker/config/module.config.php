<?php
namespace Skillstracker;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'skills-tracker' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/skills-tracker[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\SkillstrackerController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
     'controllers' => [
        'factories' => [
            Controller\SkillstrackerController::class => Controller\Factory\SkillstrackerControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            'skillstracker' => __DIR__ . '/../view',
        ],
    ],
];
