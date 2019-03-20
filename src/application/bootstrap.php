<?php
use Slim\App;
use Slim\Container;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Tranquility\App\Config;

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

// Register logger
$container['logger'] = function($c) {
    $loggingConfig = $c->config->get('app.logging');
    $logger = new Logger($loggingConfig['name']);
    $logger->pushHandler(new StreamHandler($loggingConfig['path'], $loggingConfig['level']));
    return $logger;
};

// Register error handlers
$handlers = $container->config->get('app.error_handlers', array());
foreach ($handlers as $type => $class) {
    $container[$type] = function($c) use ($class) {
        return new $class($c['logger'], $c->config->get('slim.displayErrorDetails'));
    };
}

// Set framework settings
$settings = $container->get('settings');
$settings->replace($container->config->get('slim'));

// Start application
$app = new App($container);

// Register service providers
$services = $container->config->get('app.service_providers', array());
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