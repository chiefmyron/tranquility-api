<?php 

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponse;

// Initialise the autoloader
define('TRANQUIL_PATH_BASE', realpath(__DIR__.'/../'));
require(TRANQUIL_PATH_BASE.'/vendor/autoload.php');

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

// Initialise application
AppFactory::setContainer($containerBuilder->build());
$app = AppFactory::create();

// Assign matched route arguments to Request attributes for PSR-15 handlers
$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponse(true));

// Register middleware
$middlewareLoader = require(TRANQUIL_PATH_BASE.'/src/application/middleware.php');
$middlewareLoader($app);

// Register routes
$routeLoader = require(TRANQUIL_PATH_BASE.'/src/application/routes.php');
$routeLoader($app);

// Run app
$app->run();