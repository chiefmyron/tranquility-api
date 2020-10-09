<?php namespace Tranquillity\ServiceProvider;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Valitron\Validator;

// Custom validation classes
use Tranquillity\Validators\EntityExistsValidator;
use Tranquillity\Validators\UniqueUsernameValidator;
use Tranquillity\Validators\ReferenceDataValidator;

class ValidationServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder) {
        $containerBuilder->addDefinitions([
            Validator::class => function(ContainerInterface $c) {
                // ORM entity manager
                $em = $c->get(EntityManagerInterface::class);

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