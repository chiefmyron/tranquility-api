<?php

declare(strict_types=1);

// Initialise the autoloader
define('TRANQUIL_PATH_BASE', realpath(__DIR__.'/../'));
require(TRANQUIL_PATH_BASE.'/vendor/autoload.php');

use DI\ContainerBuilder;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Tools\Console\Command;
use Symfony\Component\Console\Application;

// Load configuration
$configLoader = require(TRANQUIL_PATH_BASE.'/src/application/config.php');
$config = $configLoader();

// Set up dependencies
$containerBuilder = new ContainerBuilder();
if ($config->has('app.di_compliation_path')) {
    $containerBuilder->enableCompilation($config->get('app.di_compilation_path'));
}
$dependencyLoader = require(TRANQUIL_PATH_BASE.'/src/application/dependencies.php');
$dependencyLoader($containerBuilder, $config);
$container = $containerBuilder->build();

// Load dependencies for migration tool
$migrationConfig = new ConfigurationArray($config->get('migration'));
$em = $container->get('em');
$dependencyFactory = DependencyFactory::fromEntityManager($migrationConfig, new ExistingEntityManager($em));

// Setup command line application
$cli = new Application('Tranquility DB Migrations');
$cli->setCatchExceptions(true);
$cli->addCommands([
    new Command\DiffCommand($dependencyFactory),
    new Command\DumpSchemaCommand($dependencyFactory),
    new Command\ExecuteCommand($dependencyFactory),
    new Command\GenerateCommand($dependencyFactory),
    new Command\LatestCommand($dependencyFactory),
    new Command\ListCommand($dependencyFactory),
    new Command\MigrateCommand($dependencyFactory),
    new Command\RollupCommand($dependencyFactory),
    new Command\StatusCommand($dependencyFactory),
    new Command\SyncMetadataCommand($dependencyFactory),
    new Command\VersionCommand($dependencyFactory),
]);
$cli->run();