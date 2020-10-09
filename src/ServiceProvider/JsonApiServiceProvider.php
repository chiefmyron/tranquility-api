<?php namespace Tranquillity\ServiceProvider;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator as ResponseValidatorJsonApi;

// Application classes
use Tranquillity\System\JsonApi\RequestValidatorJsonApi;
use Tranquillity\Middlewares\JsonApiRequestValidatorMiddleware;

class JsonApiServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder) {
        $containerBuilder->addDefinitions([
            JsonApiRequestValidatorMiddleware::class => function(ContainerInterface $c) {
                // Get related configuration settings
                $config = $c->get('config')->get('app.jsonapi', array());
    
                // Create the validator
                $jsonSerialiser = new JsonSerializer();
                $exceptionFactory = new DefaultExceptionFactory();
                $requestValidator = new RequestValidatorJsonApi($exceptionFactory, $config['validateIncludeRequestBodyInResponse']);
                $responseValidator = new ResponseValidatorJsonApi($jsonSerialiser, $exceptionFactory, $config['validateIncludeRequestBodyInResponse']);
                $middleware = new JsonApiRequestValidatorMiddleware($requestValidator, $responseValidator, $exceptionFactory, $config);
                return $middleware;
            }
        ]);
    }
}