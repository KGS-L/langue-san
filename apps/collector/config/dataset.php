<?php

return [
    'version' => env('DATASET_VERSION', '0.1.0'),
    'source_salt' => env('DATASET_SOURCE_SALT', 'langue-san-local-dev-salt'),
    'splits' => [
        'train' => 80,
        'validation' => 10,
        'test' => 10,
    ],
];
