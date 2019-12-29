<?php namespace Tranquility\ServiceProviders;

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
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();

        // Register controllers with the container
        $container[AuthController::class] = function($container) {
            $server = $container->get(Server::class);
            return new AuthController($server);
        };
        $container[UserController::class] = function($container) {
            $service = new UserService($container->get('em'));
            $router = $container->get('router');
            return new UserController($service, $router);
        };
        $container[PersonController::class] = function($container) {
            $service = new PersonService($container->get('em'));
            $router = $container->get('router');
            return new PersonController($service, $router);
        };
    }
}