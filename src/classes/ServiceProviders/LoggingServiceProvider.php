<?php namespace Tranquility\ServiceProviders;

// PSR standards interfaces
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;

class LoggingServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder, string $name) {
        $containerBuilder->addDefinitions([
            // Register logging library
            LoggerInterface::class => function(ContainerInterface $c) {
                $config = $c->get('config')->get('app.logging');
                $logger = new Logger($config['name']);
            
                $processor = new UidProcessor();
                $logger->pushProcessor($processor);
            
                $handler = new StreamHandler($config['path'], $config['level']);
                $logger->pushHandler($handler);
            
                return $logger;
            }
        ]);
    }
}




