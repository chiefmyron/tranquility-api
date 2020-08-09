<?php
return [
    'options' => [
        'auto_generate_proxies' => env('APP_DEV_MODE', false),
        'proxy_dir' => TRANQUIL_PATH_BASE.'/cache/proxies',
        'entity_dir' => [
            TRANQUIL_PATH_BASE.'/src/classes/Data/Entities'
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
    ],
    'migration' => [
        'paths' => [
            'migrations' => TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'resources/database/migrations',
            'seeds' => TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'resources/database/seeds'
        ],
        'default_migration_table' => 'db_migrations',
        'version_order' => 'creation'
    ]
];