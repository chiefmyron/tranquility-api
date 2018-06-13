<?php namespace Tranquility\Services;

use \Tranquility\Services\AbstractService as AbstractService;

class LoggingService extends AbstractService {
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
            $logger = new \Monolog\Logger($config['name']);
            $fileHandler = new \Monolog\Handler\StreamHandler($config['path']);
            $logger->pushHandler($fileHandler);
            return $logger;
        };
    }
}