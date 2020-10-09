<?php namespace Tranquillity\ServiceProvider;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library clases
use DI;
use DI\ContainerBuilder;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\EntityManagerInterface;

class MigrationServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder) {
        $containerBuilder->addDefinitions([
            DependencyFactory::class => function(ContainerInterface $c) {
                $config = $c->get('config');
                $options = new ConfigurationArray($config->get('migration', []));
                $entityManager = $c->get(EntityManagerInterface::class);

                $dependencyFactory = DependencyFactory::fromEntityManager($options, new ExistingEntityManager($entityManager));
                return $dependencyFactory;
            },

            CurrentCommand::class      => DI\create()->constructor(DI\get(DependencyFactory::class)),
            DiffCommand::class         => DI\create()->constructor(DI\get(DependencyFactory::class)),
            DumpSchemaCommand::class   => DI\create()->constructor(DI\get(DependencyFactory::class)),
            ExecuteCommand::class      => DI\create()->constructor(DI\get(DependencyFactory::class)),
            GenerateCommand::class     => DI\create()->constructor(DI\get(DependencyFactory::class)),
            LatestCommand::class       => DI\create()->constructor(DI\get(DependencyFactory::class)),
            ListCommand::class         => DI\create()->constructor(DI\get(DependencyFactory::class)),
            MigrateCommand::class      => DI\create()->constructor(DI\get(DependencyFactory::class)),
            RollupCommand::class       => DI\create()->constructor(DI\get(DependencyFactory::class)),
            StatusCommand::class       => DI\create()->constructor(DI\get(DependencyFactory::class)),
            SyncMetadataCommand::class => DI\create()->constructor(DI\get(DependencyFactory::class)),
            UpToDateCommand::class     => DI\create()->constructor(DI\get(DependencyFactory::class)),
            VersionCommand::class      => DI\create()->constructor(DI\get(DependencyFactory::class)),
            StatusCommand::class       => DI\create()->constructor(DI\get(DependencyFactory::class)),
        ]);
    }
}