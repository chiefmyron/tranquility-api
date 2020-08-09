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
    $services = $config->get('app.service_providers', []);
    foreach ($services as $name => $class) {
        $service = new $class();
        $service->register($containerBuilder, $name);
    }

    // Load entity services
    $defs = [];
    $entities = $config->get('entity', []);
    foreach ($entities as $entity) {
        // Add data service if defined
        if (is_null($entity['service']) == false) {
            $defs[$entity['service']] = DI\create()->constructor(DI\get('em'), DI\get('validator'));
        }

        // Add controller, if defined
        if (is_null($entity['controller']) == false && is_null($entity['service']) == false) {
            $defs[$entity['controller']] = DI\create()->constructor(DI\get($entity['service']));
        }
    }
    $containerBuilder->addDefinitions($defs);
};