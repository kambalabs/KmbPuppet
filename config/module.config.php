<?php
return [
    'router' => [
        'routes' => [
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /puppet/:controller/:action
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
            'KmbPuppet\Controller\Reports' => 'KmbPuppet\Controller\ReportsController'
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'reportLabelClass' => 'KmbPuppet\View\Helper\ReportLabelClass',
            'filterReportMessage' => 'KmbPuppet\View\Helper\FilterReportMessage',
            'shortHostname' => 'KmbPuppet\View\Helper\ShortHostname',
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_map' => [
            'kmb-puppet/reports/index' => __DIR__ . '/../view/kmb-puppet/reports/index.phtml',
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
                ]
            ]
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
