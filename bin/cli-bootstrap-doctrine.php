<?php

// Define application root
define('TRANQUIL_PATH_BASE', realpath(__DIR__.'/../'));

// Include framework librarires
use Slim\Container;

// ORM class libraries
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Tools\Setup;

// Application classes
use Tranquillity\App\Config;
use Tranquillity\System\ORM\Extensions\TablePrefix\TablePrefixExtension;

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

// Register ORM Entity Manager with the container
$container[EntityManager::class] = function($c) {
    // Get connection and options from config
    $options = $c['config']->get('database.options', array());
    $connection = $c['config']->get('database.connection', array());

    // Create Doctrine configuration
    $config = Setup::createConfiguration(
        $options['auto_generate_proxies'],
        $options['proxy_dir'],
        $options['cache']
    );

    // Create Doctrine configuration
    $driver = new StaticPhpDriver($options['entity_dir']);
    $config->setMetadataDriverImpl($driver);

    // Add event listeners
    $eventManager = new EventManager;
    $tablePrefixEventManager = new TablePrefixExtension($options['table_prefix']);
    $eventManager->addEventListener(Events::loadClassMetadata, $tablePrefixEventManager);

    // Create Doctrine entity manager
    $entityManager = EntityManager::create($connection, $config, $eventManager);
    return $entityManager;
};

return $container;