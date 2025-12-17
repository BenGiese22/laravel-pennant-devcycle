<?php

return [
    'register_routes' => false,

    'mgmt' => [
        'client_id' => env('DEVCYCLE_MGMT_CLIENT_ID'),
        'client_secret' => env('DEVCYCLE_MGMT_CLIENT_SECRET'),
        'project_key' => env('DEVCYCLE_MGMT_PROJECT_KEY'),
        'api_base' => env('DEVCYCLE_MGMT_API_BASE', 'https://api.devcycle.com'),
        'auth_base' => env('DEVCYCLE_MGMT_AUTH_BASE', 'https://auth.devcycle.com'),
    ],
];
