<?php namespace Tranquility\ServiceProviders;

// Library classes
use DI\ContainerBuilder;

abstract class AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @param  string  $name  The name of the key used to address the service once it is registered
     * @return void
     */
    abstract public function register(ContainerBuilder $containerBuilder, string $name);
}