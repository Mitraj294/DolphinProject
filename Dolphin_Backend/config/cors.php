<?php

return [
    'paths' => [
        'api/*',
        'oauth/*',
        'sanctum/csrf-cookie',
    ],
    'allowed_methods' => ['*'],
    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Support a single FRONTEND_URL or a comma-separated FRONTEND_URLS env var
    | so deployments (Netlify, Vercel, Render, etc.) can list multiple
    | allowed origins. The config below normalizes those values into an
    | array and always includes the local dev origin as a fallback.
    |
    */
    'allowed_origins' => array_values(array_filter(array_unique(array_merge(
        // origins supplied via FRONTEND_URLS (comma separated)
        array_map('trim', explode(',', env('FRONTEND_URLS', ''))),
        // single FRONTEND_URL for backwards compatibility
        [env('FRONTEND_URL', 'http://127.0.0.1:8080')],
        // local dev fallback
        ['http://127.0.0.1:8080']
    )))),
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
