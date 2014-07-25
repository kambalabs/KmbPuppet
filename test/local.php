<?php
return [
    'log' => [
        'Logger' => [
            'writers' => [
                'null' => [
                    'name' => 'null',
                ],
            ],
        ],
    ],
    'db' => [
        'driver' => 'Pdo',
        'dsn' => 'sqlite::memory:'
    ],
    'pmproxy' => [
        'base_uri' => 'http://localhost:3000',
    ]
];
