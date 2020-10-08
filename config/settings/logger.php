<?php

// PSR standards interfaces
use Psr\Log\LogLevel;

// Logger settings

return [
    'name' => env('LOG_NAME', 'tranquillity-api'),
    'level' => env('LOG_LEVEL', LogLevel::DEBUG),
    'type' => env('LOG_TYPE', 'file-rotating'),
    'options' => [
        'path' => env('LOG_PATH', APP_BASE_PATH.'/var/logs'),
        'filename' => env('LOG_FILENAME', 'tranquillity-api.log'),
        'maxFiles' => env('LOG_MAX_FILES', 10)
    ]
];