<?php
// Awfull hack to tell to poedit to translate navigation labels
$translate = function ($message) { return $message; };
return [
    'router' => [
        'routes' => [
            'puppet' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet[/[:controller[/:action]]]',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'KmbPuppet\Controller',
                        'controller' => 'Reports',
                        'action' => 'index',
                        'envId' => '0',
                    ],
                ],
            ],
            'puppet-environment' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/environment/:id/:action',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Environments',
                        'envId' => '0',
                    ],
                ],
            ],
            'puppet-environment-user-remove' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/environment/:id/user/:userId/remove',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'id' => '[0-9]+',
                        'userId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Environments',
                        'action' => 'remove-user',
                        'envId' => '0',
                    ],
                ],
            ],
            'puppet-module' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/module/:name[/:action]',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Modules',
                        'envId' => '0',
                        'action' => 'show',
                    ],
                ],
            ],
            'puppet-module-class' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/module/:moduleName/class/:className[/:action]',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'moduleName' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'className' => '[a-zA-Z][:a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Modules',
                        'envId' => '0',
                        'action' => 'show-class',
                    ],
                ],
            ],
            'puppet-group' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/group/:id[/:action]',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Group',
                        'envId' => '0',
                        'action' => 'show',
                    ],
                ],
            ],
            'puppet-group-remove-class' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/group/:id/remove-class/:className',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                        'className' => '[a-zA-Z][a-zA-Z0-9_:-]*',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Group',
                        'envId' => '0',
                        'action' => 'remove-class',
                    ],
                ],
            ],
            'puppet-group-parameter' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/group/:groupId/parameter/:id/:action',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'groupId' => '[0-9]+',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\GroupParameter',
                        'envId' => '0',
                    ],
                ],
            ],
            'puppet-group-class' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/group/:groupId/class/:id/:action',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\GroupClass',
                        'envId' => '0',
                    ],
                ],
            ],
            'puppet-revision' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/revision/:id/:action',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Revision',
                        'envId' => '0',
                    ],
                ],
            ],
            'puppet-server' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/puppet/server[s][/:hostname]',
                    'constraints' => [
                        'hostname' => '(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])',
                    ],
                    'defaults' => [
                        'controller' => 'KmbPuppet\Controller\Server',
                        'action' => 'show',
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
            'KmbPuppet\Controller\Modules' => 'KmbPuppet\Controller\ModulesController',
            'KmbPuppet\Controller\Groups' => 'KmbPuppet\Controller\GroupsController',
            'KmbPuppet\Controller\Group' => 'KmbPuppet\Controller\GroupController',
            'KmbPuppet\Controller\GroupClass' => 'KmbPuppet\Controller\GroupClassController',
            'KmbPuppet\Controller\GroupParameter' => 'KmbPuppet\Controller\GroupParameterController',
            'KmbPuppet\Controller\Revisions' => 'KmbPuppet\Controller\RevisionsController',
            'KmbPuppet\Controller\Revision' => 'KmbPuppet\Controller\RevisionController',
            'KmbPuppet\Controller\Server' => 'KmbPuppet\Controller\ServerController',
        ],
        'factories' => [
            'KmbPuppet\Controller\Reports' => 'KmbPuppet\Service\ReportsControllerFactory',
            'KmbPuppet\Controller\Environments' => 'KmbPuppet\Service\EnvironmentsControllerFactory',
        ]
    ],
    'controller_plugins' => [
        'factories' => [
            'writeRevisionLog' => 'KmbPuppet\Controller\Plugin\WriteRevisionLogFactory',
        ],
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            [
                'label' => $translate('Puppet'),
                'route' => 'puppet',
                'tabindex' => 60,
                'pages' => [
                    [
                        'label' => $translate('Day reports'),
                        'route' => 'puppet',
                        'controller' => 'reports',
                        'action' => 'index',
                        'useRouteMatch' => true,
                        'tabindex' => 61,
                    ],
                    [
                        'label' => $translate('Environments'),
                        'route' => 'puppet',
                        'controller' => 'environments',
                        'action' => 'index',
                        'useRouteMatch' => true,
                        'roles' => 'admin',
                        'tabindex' => 61,
                    ],
                    [
                        'label' => $translate('Modules'),
                        'route' => 'puppet',
                        'controller' => 'modules',
                        'action' => 'index',
                        'useRouteMatch' => true,
                        'tabindex' => 62,
                    ],
                    [
                        'label' => $translate('Groups'),
                        'route' => 'puppet',
                        'controller' => 'groups',
                        'action' => 'index',
                        'useRouteMatch' => true,
                        'tabindex' => 63,
                    ],
                    [
                        'label' => $translate('Changes'),
                        'route' => 'puppet',
                        'controller' => 'revisions',
                        'action' => 'index',
                        'useRouteMatch' => true,
                        'tabindex' => 64,
                    ],
                ],
            ],
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'reportLabelClass' => 'KmbPuppet\View\Helper\ReportLabelClass',
            'filterReportMessage' => 'KmbPuppet\View\Helper\FilterReportMessage',
            'shortHostname' => 'KmbPuppet\View\Helper\ShortHostname',
            'formatAncestorsNames' => 'KmbPuppet\View\Helper\FormatAncestorsNames',
            'printParameterType' => 'KmbPuppet\View\Helper\PrintParameterType',
            'puppetModuleLabelClass' => 'KmbPuppet\View\Helper\PuppetModuleLabelClass',
        ],
        'factories' => [
            'KmbPuppet\View\Helper\EnvironmentSelect' => 'KmbPuppet\View\Helper\EnvironmentSelectFactory',
        ],
        'aliases' => [
            'environmentSelect' => 'KmbPuppet\View\Helper\EnvironmentSelect',
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
        'template_map' => [
            'kmb-puppet/reports/index' => __DIR__ . '/../view/kmb-puppet/reports/index.phtml',
            'kmb-puppet/modules/index' => __DIR__ . '/../view/kmb-puppet/modules/index.phtml',
            'kmb-puppet/modules/show' => __DIR__ . '/../view/kmb-puppet/modules/show.phtml',
            'kmb-puppet/modules/parameters-templates' => __DIR__ . '/../view/kmb-puppet/modules/parameters-templates.phtml',
            'kmb-puppet/groups/index' => __DIR__ . '/../view/kmb-puppet/groups/index.phtml',
            'kmb-puppet/environments/index' => __DIR__ . '/../view/kmb-puppet/environments/index.phtml',
            'kmb-puppet/environments/environments' => __DIR__ . '/../view/kmb-puppet/environments/environments.phtml',
            'kmb-puppet/environments/environments-options' => __DIR__ . '/../view/kmb-puppet/environments/environments-options.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'KmbPuppet\Listener\RevisionLogsListener' => 'KmbPuppet\Listener\RevisionLogsListener',
        ],
        'factories' => [
            'KmbPuppet\Service\Node' => 'KmbPuppet\Service\NodeFactory',
            'KmbPuppet\Service\GroupClass' => 'KmbPuppet\Service\GroupClassFactory',
        ],
        'aliases' => [
            'kmbRevisionLogsListener' => 'KmbPuppet\Listener\RevisionLogsListener',
        ],
    ],
    'listeners' => [
        'kmbRevisionLogsListener',
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
                    'controller' => 'KmbPuppet\Controller\Modules',
                    'actions' => ['index', 'show', 'show-class'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Groups',
                    'actions' => ['update', 'create'],
                    'roles' => ['admin']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Groups',
                    'actions' => ['index'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Group',
                    'actions' => ['update', 'remove', 'add-class', 'remove-class'],
                    'roles' => ['admin']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Group',
                    'actions' => ['show', 'servers'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\GroupClass',
                    'actions' => ['add-parameter'],
                    'roles' => ['admin']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\GroupParameter',
                    'actions' => ['update', 'remove', 'add-child'],
                    'roles' => ['admin']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Environments',
                    'actions' => ['index', 'create', 'remove', 'update', 'users', 'available-users', 'remove-user', 'add-users'],
                    'roles' => ['admin']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Revisions',
                    'actions' => ['index', 'diff'],
                    'roles' => ['user']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Revisions',
                    'actions' => ['import'],
                    'roles' => ['admin']
                ],
                [
                    'controller' => 'KmbPuppet\Controller\Revision',
                    'actions' => ['release', 'remove', 'export'],
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
