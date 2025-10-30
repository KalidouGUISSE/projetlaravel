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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'guisse/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],

    'allowed_origins' => ['*'],  // Permettre toutes les origines pour le développement

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],  // Permettre tous les headers

    'exposed_headers' => ['Authorization'],

    'max_age' => 86400,  // 24 heures

    'supports_credentials' => true,

];
