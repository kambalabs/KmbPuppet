<?php
return [
    'router' => [
        'routes' => [
            'index' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'KmbDashboard\Controller\Index',
                        'action' => 'index',
                    ],
                ],
            ],
            'dashboard' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/dashboard[/][:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbDashboard\Controller\Index',
                        'action' => 'index',
                    ],
                ],
            ],
            'servers' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/servers[/]',
                    'defaults' => [
                        'controller' => 'KmbServers\Controller\Index',
                        'action' => 'index',
                    ],
                ],
            ],
            'server' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/servers/:hostname[/:action]',
                    'constraints' => [
                        'hostname' => '(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])',
                    ],
                    'defaults' => [
                        'controller' => 'KmbServers\Controller\Index',
                        'action' => 'show',
                    ],
                ],
            ],
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'translate' => 'KmbBaseTest\Controller\Plugin\FakeTranslateFactory',
        ],
    ],
];
