<?php
return [
    'service_manager' => [
        'invokables' => [
            'KmbPmProxy\Http\Client' => 'KmbPuppetTest\FakeHttpClient',
        ],
    ],
    'router' => [
        'routes' => [
            'server' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/server/:hostname[/[:action]]',
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
