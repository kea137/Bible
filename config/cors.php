<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:8081',    // Expo web dev
        'http://localhost:3000',    // Alternative dev
        'http://localhost:8000',    // Laravel local
        'http://127.0.0.1:8000',    // Postman local
        'http://127.0.0.1:8081',    // Mobile app
        // Add production URLs here when deploying
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'X-CSRF-TOKEN',
        'X-CSRF-NAME',
    ],

    'max_age' => 0,

    'supports_credentials' => true,  // ← CRITICAL: Must be true for cookies
];
