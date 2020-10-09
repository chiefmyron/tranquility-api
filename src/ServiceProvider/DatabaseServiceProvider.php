<?php namespace Tranquillity\ServiceProvider;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library clases
use DI\ContainerBuilder;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\DBAL\Types\Type;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\Mapping\Driver\StaticPHPDriver;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;

// Application classes
use Tranquillity\System\Database\ORM\TablePrefix\TablePrefixExtension as TablePrefixExtension;

class DatabaseServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder) {
        $containerBuilder->addDefinitions([
            EntityManagerInterface::class => function(ContainerInterface $c) {
                // Get connection and options from config
                $config = $c->get('config');
                $options = $config->get('database.options', []);
                $connection = $config->get('database.connection', []);
    
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

                // Register UUID data type
                Type::addType(UuidBinaryOrderedTimeType::NAME, UuidBinaryOrderedTimeType::class);
                $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping(UuidBinaryOrderedTimeType::NAME, 'binary');

                return $entityManager;
            },

            Connection::class => function(ContainerInterface $c) {
                $entityManager = $c->get(EntityManagerInterface::class);
                return $entityManager->getConnection();
            }
        ]);
    }
}