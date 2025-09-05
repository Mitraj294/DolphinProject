<?php

return [
    'paths' => [
        'api/*',
        'oauth/*',
        'sanctum/csrf-cookie',
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://127.0.0.1:8080',
        'http://localhost:8080',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
