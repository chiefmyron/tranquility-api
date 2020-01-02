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
};