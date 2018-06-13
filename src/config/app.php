<?php
return [
    // Application name
    'name' => env('APP_NAME', 'Tranquility'),

    // Appliation environment
    'env' => env('APP_ENV', 'production'),

    // Application debug mode
    'debug' => env('APP_DEV_MODE', false),

    // Base URL
    'base_url' => env('APP_BASE_URL', 'http://api.tranquility.com'),

    // System timezone
    'timezone' => 'UTC',

    // Default locale
    'locale' => 'en_AU',

    // Fallback locale
    'locale_fallback' => 'en',

    // Logging
    'logging' => [
        'level' => env('APP_LOG_LEVEL', 400),
        'path' => env('APP_LOG_PATH', TRANQUIL_PATH_BASE.'/logs/tranquil-api.log'),
        'name' => 'tranquil-api'
    ],

    // Services
    'services' => [
        'logger'     => '\Tranquility\Services\LoggingService',
        'em'         => '\Tranquility\Services\EntityManagerService',
        'controller' => '\Tranquility\Services\ControllerService'
    ]
];