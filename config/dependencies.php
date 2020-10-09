<?php declare(strict_types=1);

// Library classes
use DI\ContainerBuilder;

// Application classes
use Tranquillity\Utility\Config;
use Tranquillity\ServiceProvider\AuthenticationServiceProvider;
use Tranquillity\ServiceProvider\DatabaseServiceProvider;
use Tranquillity\ServiceProvider\JsonApiServiceProvider;
use Tranquillity\ServiceProvider\LoggerServiceProvider;
use Tranquillity\ServiceProvider\ValidationServiceProvider;

return static function (ContainerBuilder $containerBuilder, Config $config) {
    // Add configuration to container
    $containerBuilder->addDefinitions(['config' => $config]);

    // Use application service providers to register additional dependencies
    $serviceProviders = [
        LoggerServiceProvider::class,
        DatabaseServiceProvider::class,
        AuthenticationServiceProvider::class,
        JsonApiServiceProvider::class,
        ValidationServiceProvider::class
    ];
    foreach ($serviceProviders as $name => $providerClassname) {
        $provider = new $providerClassname();
        $provider->register($containerBuilder, $name);
    }
};