<?php
return [
    'router' => [
        'routes' => [
            'puppet' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/puppet',
                    'defaults' => [
                        '__NAMESPACE__' => 'KmbPuppet\Controller',
                        'controller' => 'Reports',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
                            ],
                        ],
                    ],
                    'withid' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:controller[/:id][/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*',
                            ],
                            'defaults' => [
                            ],
                        ],
                    ],
                    'remove-user' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/environments/:id/users/:userId/remove',
                            'constraints' => [
                                'id' => '[0-9]*',
                                'userId' => '[0-9]*',
                            ],
                            'defaults' => [
                                '__NAMESPACE__' => 'KmbPuppet\Controller',
                                'controller' => 'Environments',
                                'action' => 'remove-user',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'KmbPuppet\Controller\Reports' => 'KmbPuppet\Controller\ReportsController',
        ],
        'factories' => [
            'KmbPuppet\Controller\Environments' => 'KmbPuppet\Service\EnvironmentsControllerFactory',
        ]
    ],
    'view_helpers' => [
        'invokables' => [
            'reportLabelClass' => 'KmbPuppet\View\Helper\ReportLabelClass',
            'filterReportMessage' => 'KmbPuppet\View\Helper\FilterReportMessage',
            'shortHostname' => 'KmbPuppet\View\Helper\ShortHostname',
            'formatAncestorsNames' => 'KmbPuppet\View\Helper\FormatAncestorsNames',
        ],
        'factories' => [
            'environmentSelect' => 'KmbPuppet\View\Helper\EnvironmentSelectFactory',
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_map' => [
            'kmb-puppet/reports/index' => __DIR__ . '/../view/kmb-puppet/reports/index.phtml',
            'kmb-puppet/environments/index' => __DIR__ . '/../view/kmb-puppet/environments/index.phtml',
            'kmb-puppet/environments/environments' => __DIR__ . '/../view/kmb-puppet/environments/environments.phtml',
            'kmb-puppet/environments/environments-options' => __DIR__ . '/../view/kmb-puppet/environments/environments-options.phtml',
            'kmb-puppet/environments/environments-select' => __DIR__ . '/../view/kmb-puppet/environments/environments-select.phtml',
            'kmb-puppet/environments/environments-form' => __DIR__ . '/../view/kmb-puppet/environments/environments-form.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\ControllerGuard' => [
                [
                    'controller' => 'KmbPuppet\Controller\Reports',
                    'actions' => ['index'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Environments',
                    'actions' => ['index'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Environments',
                    'actions' => ['create', 'remove', 'update', 'users', 'available-users', 'remove-user'],
                    'roles' => ['admin']
                ],
            ],
        ],
    ],
    'datatables' => [
        'reports_datatable' => [
            'id' => 'reports',
            'classes' => ['table', 'table-striped', 'table-hover', 'table-condensed', 'bootstrap-datatable'],
            'collectorFactory' => 'KmbPuppet\Service\ReportCollectorFactory',
            'columns' => [
                [
                    'decorator' => 'KmbPuppet\View\Decorator\ReportHourDecorator',
                    'key' => 'timestamp',
                ],
                [
                    'decorator' => 'KmbPuppet\View\Decorator\ReportTypeDecorator',
                    'key' => 'resource-type',
                ],
                [
                    'decorator' => 'KmbPuppet\View\Decorator\ReportTitleDecorator',
                    'key' => 'resource-title',
                ],
                [
                    'decorator' => 'KmbPuppet\View\Decorator\ReportMessageDecorator',
                    'key' => 'message',
                ],
                [
                    'decorator' => 'KmbPuppet\View\Decorator\ReportClassNameDecorator',
                    'key' => 'containing-class',
                ],
                [
                    'decorator' => 'KmbPuppet\View\Decorator\ReportNodeNameDecorator',
                    'key' => 'certname',
                ],
            ]
        ]
    ],
];
