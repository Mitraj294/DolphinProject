<?php
$FRONTEND_URL = env('FRONTEND_URL');
return [
    'paths' => [
        'api/*',
        'oauth/*',
        'sanctum/csrf-cookie',
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        $FRONTEND_URL
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
