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
            'translatePlural' => 'KmbBaseTest\Controller\Plugin\FakeTranslatePluralFactory',
        ],
    ],
    'custom_groups' => [
        'fake' => [
            'label' => 'fake label',
            'description' => 'fake description',
            'required_modules' => ['fake-module'],
            'widget' => [
                'action' => 'Fake\Widget\FakeWidgetAction',
                'template' => 'fake.phtml',
            ],
        ],
    ],
];
