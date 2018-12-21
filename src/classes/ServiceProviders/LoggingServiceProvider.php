<?php namespace Tranquility\ServiceProviders;

// Monolog library classes
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggingServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();

        // Register logger with the container
        $container[$name] = function($c) {
            // Get config details
            $config = $c['config']->get('app.logging', array());

            // Create logger
            $logger = new Logger($config['name']);
            $fileHandler = new StreamHandler($config['path']);
            $logger->pushHandler($fileHandler);
            return $logger;
        };
    }
}