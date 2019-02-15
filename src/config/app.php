<?php
return [
    // Application name
    'name' => env('APP_NAME', 'Tranquility'),

    // Appliation environment
    'env' => env('APP_ENV', 'production'),

    // Application debug mode
    'debug' => env('APP_DEV_MODE', false),

    // Base URL
    'base_url' => env('APP_BASE_URL', 'https://api.tranquility.com'),

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

    // JSON:API details
    'jsonapi' => [
        'schemaVersion' => env('APP_JSONAPI_SCHEMA_VERSION', '1.0'),
        'validateIncludeRequestBodyInResponse' => env('APP_JSONAPI_VALIDATE_INCLUDE_REQUEST_IN_RESPONSE', true),
        'validateContentNegotiation' => env('APP_JSONAPI_VALIDATE_CONTENT_NEGOTIATION', true),
        'validateQueryParams' => env('APP_JSONAPI_VALIDATE_QUERY_PARAMS', true),
        'validateRequestBody' => env('APP_JSONAPI_VALIDATE_REQUEST', true),
        'validateResponseBody' => env('APP_JSONAPI_VALIDATE_RESPONSE', false)
    ],

    // Services
    'service_providers' => [
        'logger'     => '\Tranquility\ServiceProviders\LoggingServiceProvider',
        'em'         => '\Tranquility\ServiceProviders\EntityManagerServiceProvider',
        'auth'       => '\Tranquility\ServiceProviders\AuthenticationServiceProvider',
        'controller' => '\Tranquility\ServiceProviders\ControllerServiceProvider',
        'validation' => '\Tranquility\ServiceProviders\ValidationServiceProvider',
        'jsonapi'    => '\Tranquility\ServiceProviders\JsonApiServiceProvider'
    ],

    // Application middleware
    // NOTE: Middlewares are executed on a LIFO (Last In, First Out) basis. Therefore, middlewares that need to be 
    // executed earlier in the dispatch process should be added towards the bottom of the array.
    'middleware' => [
        '\Tranquility\Middlewares\ExceptionHandlerMiddleware'
    ]
];