<?php namespace Tranquility\ServiceProviders;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

// OAuth server class libraries
use OAuth2\Server;

// Data resource libraries
use Tranquility\Services\UserService as UserService;

// Controller libraries
use Tranquility\Controllers\AuthController as AuthController;
use Tranquility\Controllers\UserController as UserController;

class ControllerServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();
        $baseUrl = $container->config->get('app.base_url', "");

        // Register Fractal manager for use within controllers
        $manager = new Manager();
        $manager->setSerializer(new JsonApiSerializer($baseUrl));
        $container[Manager::class] = $manager;

        // Register controllers with the container
        $container[AuthController::class] = function($c) {
            $server = $c->get(Server::class);
            return new AuthController($server);
        };
        $container[UserController::class] = function($c) {
            $manager = $c->get(Manager::class);
            $service = new UserService($c->get('em'));
            $service->registerValidationRules();
            return new UserController($service, $manager);
        };
    }
}