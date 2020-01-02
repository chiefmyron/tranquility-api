<?php namespace Tranquility\ServiceProviders;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;

// OAuth server class libraries
use OAuth2\Server;

// Data resource libraries
use Tranquility\Services\UserService as UserService;
use Tranquility\Services\PersonService as PersonService;

// Controller libraries
use Tranquility\Controllers\AuthController as AuthController;
use Tranquility\Controllers\UserController as UserController;
use Tranquility\Controllers\PersonController as PersonController;

class ControllerServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder, string $name) {
        $containerBuilder->addDefinitions([
            AuthController::class => function(ContainerInterface $c) {
                $server = $c->get(Server::class);
                return new AuthController($server);
            },
            UserController::class => function(ContainerInterface $c) {
                $service = new UserService($c->get('em'));
                return new UserController($service);
            },
            PersonController::class => function(ContainerInterface $c) {
                $service = new PersonService($c->get('em'));
                return new PersonController($service);
            },
        ]);
    }
}