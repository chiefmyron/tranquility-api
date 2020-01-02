<?php namespace Tranquility\ServiceProviders;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

// Library classes
use DI\ContainerBuilder;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator as ResponseValidatorJsonApi;

// Tranquility classes
use Tranquility\System\JsonApi\RequestValidatorJsonApi;
use Tranquility\Middlewares\JsonApiRequestValidatorMiddleware;

class JsonApiServiceProvider extends AbstractServiceProvider {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(ContainerBuilder $containerBuilder, string $name) {
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