<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // 'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'paths' => ['api/*', 'oauth/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],             // toutes les méthodes GET, POST, PUT, DELETE, etc.

    'allowed_origins' => ['http://localhost:3000'],             // toutes les origines, ou liste comme ['http://localhost:3000']

    'allowed_origins_patterns' => [],

    // 'allowed_headers' => ['*'],             // tous les headers
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,        // true si vous envoyez cookies ou token

];
