<?php declare(strict_types=1);

// Import framework dependencies
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Handlers\Strategies\RequestResponse;

// Initialise the autoloader
define('APP_BASE_PATH', realpath(__DIR__.'/../'));
require(APP_BASE_PATH.'/vendor/autoload.php');

// Load application configuration
$configLoader = require(APP_BASE_PATH.'/config/config.php');
$config = $configLoader();

// Set up dependencies
$containerBuilder = new ContainerBuilder();
if ($config->has('app.di_compliation_path')) {
    $containerBuilder->enableCompilation($config->get('app.di_compilation_path'));
}
$dependencyLoader = require(APP_BASE_PATH.'/config/dependencies.php');
$dependencyLoader($containerBuilder, $config);

// Initialise application
AppFactory::setContainer($containerBuilder->build());
$app = AppFactory::create();

// Assign matched route arguments to Request attributes for PSR-15 handlers
$app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponse(true));

// Register middleware
$middlewareLoader = require(APP_BASE_PATH.'/config/middleware.php');
$middlewareLoader($app);

// Register routes
$routeLoader = require(APP_BASE_PATH.'/config/routes.php');
$routeLoader($app);

// Run app
$app->run();