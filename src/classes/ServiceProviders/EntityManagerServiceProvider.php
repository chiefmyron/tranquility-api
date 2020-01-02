<?php namespace Tranquility\ServiceProviders;

// PSR standards interfaces
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;

// Doctrine library classes
use Doctrine\ORM\Events as Events;
use Doctrine\ORM\EntityManager as EntityManager;
use Doctrine\ORM\Tools\Setup as Setup;
use Doctrine\Common\EventManager as EventManager;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver as StaticPhpDriver;

// Tranquility classes
use Tranquility\System\ORM\Extensions\TablePrefix\TablePrefixExtension as TablePrefixExtension;

class EntityManagerServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder, string $name) {
        $containerBuilder->addDefinitions([
            $name => function(ContainerInterface $c) {
                // Get connection and options from config
                $options = $c->get('config')->get('database.options', array());
                $connection = $c->get('config')->get('database.connection', array());
    
                // Create Doctrine configuration
                $config = Setup::createConfiguration(
                    $options['auto_generate_proxies'],
                    $options['proxy_dir'],
                    $options['cache']
                );
    
                // Create Doctrine configuration
                $driver = new StaticPhpDriver($options['entity_dir']);
                $config->setMetadataDriverImpl($driver);
    
                // Add event listeners
                $eventManager = new EventManager;
                $tablePrefixEventManager = new TablePrefixExtension($options['table_prefix']);
                $eventManager->addEventListener(Events::loadClassMetadata, $tablePrefixEventManager);
    
                // Create Doctrine entity manager
                $entityManager = EntityManager::create($connection, $config, $eventManager);
                return $entityManager;
            }
        ]);
    }
}