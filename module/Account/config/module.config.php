<?php

namespace Account;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'account' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/account',
                    'defaults' => [
                        'controller' => Controller\AccountController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'editstudent' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/account/edit/student[/studentId[/:studentId]]',
                    'constraints' => [
                        'studentId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\StudentController::class,
                        'action'     => 'edit',
                    ],
                ],
            ],
            'editinstructor' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/account/edit/instructor/instructorId/[/:instructorId]',
                    'constraints' => [
                        'instructorId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\InstructorController::class,
                        'action'     => 'edit',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'account' => __DIR__ . '/../view',
        ],
    ],
];
