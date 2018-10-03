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
        'path' => env('APP_LOG_PATH', TRANQUIL_PATH_BASE.'/logs/tranquility-api.log'),
        'name' => 'tranquility-api'
    ],

    // Services
    'services' => [
        'logger'     => '\Tranquility\Services\LoggingService',
        'em'         => '\Tranquility\Services\EntityManagerService',
        'auth'       => '\Tranquility\Services\AuthenticationService',
        'controller' => '\Tranquility\Services\ControllerService',
        'validation' => '\Tranquility\Services\ValidationService',
    ],

    // Application middleware
    // NOTE: Middlewares are executed on a LIFO (Last In, First Out) basis. Therefore, middlewares that need to be 
    // executed earlier in the dispatch process should be added towards the bottom of the array.
    'middleware' => [
        '\Tranquility\Middlewares\ExceptionHandlerMiddleware',
    //    '\Tranquility\Middlewares\JsonContentTypeMiddleware'
    ]
];