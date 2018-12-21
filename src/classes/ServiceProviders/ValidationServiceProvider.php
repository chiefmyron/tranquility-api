<?php namespace Tranquility\ServiceProviders;

// Validation library
use Valitron\Validator;

// Custom validation classes
use Tranquility\Validators\EntityExistsValidator;
use Tranquility\Validators\UniqueUsernameValidator;
use Tranquility\Validators\ReferenceDataValidator;

class ValidationServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();

        // ORM entity manager
        $em = $container->get('em');

        // Database connection
        //$db = $em->getConnection();
        //$options = $container['config']->get('database.options', array());

        // Register custom validation rules with the main validation class
        Validator::addRule('entityExists', [new EntityExistsValidator($em), 'validate'], "Error occurred in EntityExistsValidator class.");
        Validator::addRule('uniqueUsername', [new UniqueUsernameValidator($em), 'validate'], "Error occurred in UniqueUsernameValidator class.");
        //Validator::addRule('referenceDataCode', [new ReferenceDataValidator($db, $options), 'validate'], "Error occurred in ReferenceDataValidator class.");
        Validator::addRule('referenceDataCode', [new ReferenceDataValidator($em), 'validate'], "Error occurred in ReferenceDataValidator class.");
    }
}