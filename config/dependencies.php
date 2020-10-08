<?php declare(strict_types=1);

// Library classes
use DI\ContainerBuilder;

// Application classes
use Tranquillity\Utility\Config;
use Tranquillity\ServiceProviders\AuthenticationServiceProvider;
use Tranquillity\ServiceProviders\EntityManagerServiceProvider;
use Tranquillity\ServiceProviders\JsonApiServiceProvider;
use Tranquillity\ServiceProviders\LoggerServiceProvider;
use Tranquillity\ServiceProviders\ValidationServiceProvider;

return static function (ContainerBuilder $containerBuilder, Config $config) {
    // Add configuration to container
    $containerBuilder->addDefinitions(['config' => $config]);

    // Use application service providers to register additional dependencies
    $serviceProviders = [
        'logger'    => LoggerServiceProvider::class,
        'em'        => EntityManagerServiceProvider::class,
        'auth'      => AuthenticationServiceProvider::class,
        'jsonapi'   => JsonApiServiceProvider::class,
        'validator' => ValidationServiceProvider::class
    ];
    foreach ($serviceProviders as $name => $providerClassname) {
        $provider = new $providerClassname();
        $provider->register($containerBuilder, $name);
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