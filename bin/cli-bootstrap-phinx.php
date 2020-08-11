<?php 

declare(strict_types=1);

// Initialise the autoloader
define('TRANQUIL_PATH_BASE', realpath(__DIR__.'/../'));
require(TRANQUIL_PATH_BASE.'/vendor/autoload.php');

// Load configuration
$configLoader = require(TRANQUIL_PATH_BASE.'/src/application/config.php');
$config = $configLoader();

// Return configuration details
$migrationConfig = [
    'paths' => $config['database']['migration']['paths'],
    'environments' => [
        'default_migration_table' => $config['database']['options']['table_prefix'].$config['database']['migration']['default_migration_table'],
        'default_database' => 'environment',
        'environment' => [
            'adapter' => 'mysql', // TODO: Change from hardcoded to read PDO driver from database config
            'host' => $config['database']['connection']['host'],
            'name' => $config['database']['connection']['dbname'],
            'user' => $config['database']['connection']['user'],
            'pass' => $config['database']['connection']['password'],
            'port' => $config['database']['connection']['port'],
            'table_prefix' => $config['database']['options']['table_prefix']
        ]
    ]
];
return $migrationConfig;