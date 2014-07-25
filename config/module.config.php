<?php
return [
    'service_manager' => [
        'invokables' => [
            'KmbPuppet\Http\Client' => 'Zend\Http\Client',
            'KmbPuppet\Model\PmProxy\EnvironmentHydrator' => 'KmbPuppet\Model\PmProxy\EnvironmentHydrator',
        ],
        'factories' => [
            'KmbPuppet\Service\PmProxy' => 'KmbPuppet\Service\PmProxyFactory',
        ],
        'abstract_factories' => [
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
    ],
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
                    'actions' => ['index', 'create', 'remove', 'update'],
                    'roles' => ['admin']
                ],
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
    'zenddb_repositories' => [
        'EnvironmentRepository' => [
            'aggregate_root_class' => 'KmbPuppet\Model\Environment',
            'aggregate_root_proxy_factory' => 'KmbPuppet\Service\EnvironmentProxyFactory',
            'aggregate_root_hydrator_class' => 'KmbPuppet\Infrastructure\ZendDb\EnvironmentHydrator',
            'table_name' => 'environments',
            'table_sequence_name' => 'environment_id_seq',
            'paths_table_name' => 'environments_paths',
            'factory' => 'KmbPuppet\Infrastructure\ZendDb\EnvironmentRepositoryFactory',
            'repository_class' => 'KmbPuppet\Infrastructure\ZendDb\EnvironmentRepository',
        ]
    ],
];
