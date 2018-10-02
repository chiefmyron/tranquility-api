<?php namespace Tranquility\Services;

// Fractal class libraries
use League\Fractal\Manager;

// Data resource libraries
use Tranquility\Resources\AuthResource as AuthResource;
use Tranquility\Resources\UserResource as UserResource;
use Tranquility\Resources\AccountResource as AccountResource;

// Controller libraries
use Tranquility\Controllers\AuthController as AuthController;
use Tranquility\Controllers\UserController as UserController;
use Tranquility\Controllers\AccountsController as AccountsController;

class ControllerService extends AbstractService {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();

        // Register Fractal manager for use within controllers
        $manager = new Manager();
        $container[Manager::class] = $manager;

        // Register controllers with the container
        $container[AuthController::class] = function($c) {
            $manager = $c->get(Manager::class);
            $resource = new AuthResource($c->get('em'));
            $resource->registerValidationRules();
            return new AuthController($resource, $manager);
        };
        $container[UserController::class] = function($c) {
            $manager = $c->get(Manager::class);
            $resource = new UserResource($c->get('em'));
            $resource->registerValidationRules();
            return new UserController($resource, $manager);
        };
        $container[AccountsController::class] = function($c) {
            $manager = $c->get(Manager::class);
            $resource = new AccountResource($c->get('em'));
            $resource->registerValidationRules();
            return new AccountsController($resource, $manager);
        };
    }
}