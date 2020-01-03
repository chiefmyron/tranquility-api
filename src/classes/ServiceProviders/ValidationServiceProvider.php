<?php namespace Tranquility\ServiceProviders;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use Valitron\Validator;

// Tranquility services
use Tranquility\Services\UserService;

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
    public function register(ContainerBuilder $containerBuilder, string $name) {
        $containerBuilder->addDefinitions([
            $name => function(ContainerInterface $c) {
                // ORM entity manager
                $em = $c->get('em');

                // Register custom validation rules with the main validation class
                Validator::addRule('entityExists', [new EntityExistsValidator($em), 'validate'], "Error occurred in EntityExistsValidator class.");
                Validator::addRule('referenceDataCode', [new ReferenceDataValidator($em), 'validate'], "Error occurred in ReferenceDataValidator class.");
                Validator::addRule('uniqueUsername', [new UniqueUsernameValidator($em), 'validate'], "Error occurred in UniqueUsernameValidator class.");

                // Return instance of validator with no data (calling classes should use the 'withData()' method on the validator object)
                return new Validator([]);
            }
        ]);
    }
}