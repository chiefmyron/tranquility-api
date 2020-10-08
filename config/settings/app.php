<?php
return [
    // Application name
    'name' => env('APP_NAME', 'Tranquillity'),

    // Appliation environment
    'env' => env('APP_ENV', 'production'),

    // Application debug mode
    'debug' => env('APP_DEV_MODE', false),

    // Base URL
    'base_url' => env('APP_BASE_URL', 'https://api.tranquillity.com'),

    // System timezone
    'timezone' => 'UTC',

    // Default locale
    'locale' => 'en_AU',

    // Fallback locale
    'locale_fallback' => 'en',

    // Dependency injection compliation path
    'di_compilation_path' => env('APP_DI_COMPLILE_PATH', APP_BASE_PATH.'/var/cache/container'),

    // JSON:API details
    'jsonapi' => [
        'schemaVersion' => env('APP_JSONAPI_SCHEMA_VERSION', '1.0'),
        'validateIncludeRequestBodyInResponse' => env('APP_JSONAPI_VALIDATE_INCLUDE_REQUEST_IN_RESPONSE', true),
        'validateContentNegotiation' => env('APP_JSONAPI_VALIDATE_CONTENT_NEGOTIATION', true),
        'validateQueryParams' => env('APP_JSONAPI_VALIDATE_QUERY_PARAMS', true),
        'validateRequestBody' => env('APP_JSONAPI_VALIDATE_REQUEST', true),
        'validateResponseBody' => env('APP_JSONAPI_VALIDATE_RESPONSE', false)
    ],
];