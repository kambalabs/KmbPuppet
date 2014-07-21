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
];
