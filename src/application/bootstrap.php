<?php
use \Slim\App as App;
use \Slim\Container as Container;
use \Tranquility\App\Config as Config;

// Set up dependency injection container 
$container = new Container;

// Load configuration details
$dotenv = new Dotenv\Dotenv(TRANQUIL_PATH_BASE);
$dotenv->load();
$container['config'] = function($c) {
    $config = new Config();
    $config->load(TRANQUIL_PATH_BASE.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'config');
    return $config;
};

// Set framework settings
$settings = $container->get('settings');
$settings->replace($container->config->get('slim'));

// Start application
$app = new App($container);

// Register services
$services = $container->config->get('app.services', array());
foreach ($services as $name => $class) {
    $service = new $class($app);
    $service->register($name);
}

// Register application middleware
$middlewares = $container->config->get('app.middleware', array());
foreach ($middlewares as $middleware) {
    $app->add(new $middleware());
}

return $app;