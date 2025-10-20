<?php

return [
    'store' => [
        'driver' => 'devcycle',
        'sdk_key' => env('DEVCYCLE_SDK_KEY'),
        'default_scope' => [
            'user_id' => 'system',
            'email' => 'system@example.com',
        ],
        'options' => [
            'enable_edge_db' => false,
            'bucketing_api_hostname' => env('DEVCYCLE_BUCKETING_HOST'),
            'unix_socket_path' => null,
            'http' => [],
        ],
    ],
];
