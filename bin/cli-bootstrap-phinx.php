<?php

// Define application root
define('TRANQUIL_PATH_BASE', realpath(__DIR__.'/../'));

// Include framework librarires
use Slim\Container;

// Tranquility classes
use Tranquility\App\Config;

// Setup autoloader
require_once( TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php' );

// Create DI container
$container = new Container;

// Load configuration details
$dotenv = new Dotenv\Dotenv(TRANQUIL_PATH_BASE);
$dotenv->load();
$container['config'] = function($c) {
    $config = new Config();
    $config->load(TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'config');
    return $config;
};

// Return configuration details
return [
    'paths' => [
        'migrations' => TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'resources/database/migrations',
        'seeds' => TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'resources/database/seeds'
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