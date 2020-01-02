<?php

declare(strict_types=1);

// PSR standards interfaces
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

// Application classes
use Tranquility\App\Config;

return static function (ContainerBuilder $containerBuilder, Config $config) {
    // Add configuration to container
    $containerBuilder->addDefinitions(['config' => $config]);

    // Register service providers
    $services = $config->get('app.service_providers', array());
    foreach ($services as $name => $class) {
        $service = new $class();
        $service->register($containerBuilder, $name);
    }


    /*$containerBuilder->addDefinitions([
        // Register configuration
        'config' => $config,

        // Load dependencies defined in service providers


        

        // Register logging library
        LoggerInterface::class => function(ContainerInterface $c) {
            $config = $c->get('config')->get('app.logging');
            $logger = new Logger($config['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($config['path'], $config['level']);
            $logger->pushHandler($handler);

            return $logger;
        }
    ]);*/
};