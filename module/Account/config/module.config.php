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
                        'action'     => 'student',
                    ],
                ],
            ],
            'editinstructor' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/account/edit/instructor/instructorId/:instructorId',
                    'constraints' => [
                        'instructorId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\InstructorController::class,
                        'action'     => 'edit',
                    ],
                ],
            ],
            'newinstructor' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/account/new/instructor',
                    'defaults' => [
                        'controller' => Controller\InstructorController::class,
                        'action'     => 'newinstructor',
                    ],
                ],
            ],
            'research-consent' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/account/new/research-consent',
                    'constraints' => [
                        'studentId' => '[0-9]*',
                    ],
                    'defaults' => [
                        'controller' => Controller\StudentController::class,
                        'action'     => 'researchconsent',
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AccountController::class => Controller\Factory\AccountControllerFactory::class,
            Controller\InstructorController::class => Controller\Factory\InstructorControllerFactory::class,
            Controller\StudentController::class => Controller\Factory\StudentControllerFactory::class,
        ],
    ],

    // The 'access_filter' key is used by the User module to restrict or permit
    // access to certain controller actions for unauthorized visitors.
    'access_filter' => [
        'controllers' => [
            Controller\InstructorController::class => [
                // Give access to  "edit",  actions to authorized users only.
                ['actions' => ['edit', ], 'allow' => '@']
            ],
        ]
    ],

    'view_manager' => [
        'template_path_stack' => [
            'account' => __DIR__ . '/../view',
        ],
    ],
];
