<?php
return [
    'service_manager' => [
        'invokables' => [
            'KmbPmProxy\Http\Client' => 'KmbPuppetTest\FakeHttpClient',
        ],
    ],
    'router' => [
        'routes' => [
            'index' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/',
                    'defaults' => [
                    ],
                ],
            ],
            'dashboard' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/dashboard[/]',
                    'defaults' => [
                    ],
                ],
            ],
            'servers' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/servers[/[:action]]',
                    'defaults' => [
                    ],
                ],
            ],
            'server' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/server/:hostname[/[:action]]',
                    'defaults' => [
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
