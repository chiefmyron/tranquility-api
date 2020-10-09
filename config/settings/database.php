<?php
return [
    'options' => [
        'auto_generate_proxies' => env('APP_DEV_MODE', false),
        'proxy_dir' => APP_BASE_PATH.'/var/cache/proxies',
        'entity_dir' => [
            APP_BASE_PATH.'/src/Data/Entities'
        ],
        'cache' => null,
        'table_prefix' => env('DB_TABLE_PREFIX', '')
    ],
    'connection' => [
        'driver' => 'pdo_mysql',
        'host' => env('DB_HOSTNAME', 'localhost'),
        'dbname' => env('DB_DATABASE', 'tranquility'),
        'user' => env('DB_USERNAME', 'tranquility'),
        'password' => env('DB_PASSWORD', 'secret'),
        'port' => env('DB_PORT', 3306)
    ]
];