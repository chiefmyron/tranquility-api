<?php

// Configuration file for Phinx migration tool - not part of main application configuration
require('vendor/autoload.php');
require('src/application/helpers.php');

// Load configuration details
$dotenv = new Dotenv\Dotenv('.');
$dotenv->load();

// Return configuration details
return [
    'paths' => [
        'migrations' => 'resources/database/migrations',
        'seeds' => 'resources/database/seeds'
    ],

    'environments' => [
        'default_migration_table' => 'migrations',
        'default_database' => 'environment',
        'environment' => [
            'adapter' => 'mysql',
            'host' => env('DB_HOSTNAME', 'localhost'),
            'name' => env('DB_DATABASE', 'tranquility'),
            'user' => env('DB_USERNAME', 'tranquility'),
            'pass' => env('DB_PASSWORD', 'secret'),
            'port' => env('DB_PORT', 3306),
            'table_prefix' => env('DB_TABLE_PREFIX', 'tql_')
        ]
    ],

    'version_order' => 'creation'
];