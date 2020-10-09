<?php declare(strict_types=1);

// Library classes
use DI\ContainerBuilder;

// Application classes
use Tranquillity\ServiceProvider\MigrationServiceProvider;

return static function (ContainerBuilder $containerBuilder) {
    // Use application service providers to register additional dependencies that are specific to Console applications
    $serviceProviders = [
        MigrationServiceProvider::class
    ];
    foreach ($serviceProviders as $name => $providerClassname) {
        $provider = new $providerClassname();
        $provider->register($containerBuilder, $name);
    }
};