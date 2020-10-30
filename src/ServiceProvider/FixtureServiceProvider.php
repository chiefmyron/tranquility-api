<?php namespace Tranquillity\ServiceProvider;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library clases
use DI;
use DI\ContainerBuilder;
use Doctrine\Common\DataFixtures\Loader;
use Tranquillity\Utility\ArrayHelper;

class FixtureServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder) {
        $containerBuilder->addDefinitions([
            Loader::class => function(ContainerInterface $c) {
                $config = $c->get('config')->get('fixture');
                $paths = ArrayHelper::get($config, 'fixture_paths', []);

                // Instantiate loader
                $loader = new Loader();
                
                // Load fixtures from specified paths
                foreach ($paths as $path) {
                    $loader->loadFromDirectory($path);
                }

                return $loader;
            }
        ]);
    }
}