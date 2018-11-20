<?php namespace Tranquility\Services;

// Validation library
use Valitron\Validator;

// Custom validation classes
use Tranquility\Validators\EntityExistsValidator;
use Tranquility\Validators\UniqueUsernameValidator;

class ValidationService extends AbstractService {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();
        $em = $container->get('em');

        // Register custom validation rules with the main validation class
        Validator::addRule('entityExists', [new EntityExistsValidator($em), 'validate'], "Error occurred in EntityExistsValidator class.");
        Validator::addRule('uniqueUsername', [new UniqueUsernameValidator($em), 'validate'], "Error occurred in UniqueUsernameValidator class.");
    }
}