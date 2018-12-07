<?php namespace Tranquility\Services;

// Yin libraries
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Serializer\JsonSerializer;
use WoohooLabs\Yin\JsonApi\Negotiation\ResponseValidator as ResponseValidatorJsonApi;

// Tranquility JSON API request validator
use Tranquility\System\JsonApi\RequestValidatorJsonApi;

// Tranquility middlewares
use Tranquility\Middlewares\JsonApiRequestValidatorMiddleware;
// use Tranquility\Middlewares\JsonApiResponseValidatorMiddleware;

class JsonApiService extends AbstractService {
    /**
     * Registers the service with the application container
     * 
     * @return void
     */
    public function register(string $name) {
        // Get the dependency injection container
        $container = $this->app->getContainer();

        // Register JSON:API request / response validator with the container
        $container[JsonApiRequestValidatorMiddleware::class] = function($c) {
            // Get related configuration settings
            $config = $c['config']->get('app.jsonapi', array());

            // Create the validator
            $jsonSerialiser = new JsonSerializer();
            $exceptionFactory = new DefaultExceptionFactory();
            $requestValidator = new RequestValidatorJsonApi($exceptionFactory, $config['validateIncludeRequestBodyInResponse']);
            $responseValidator = new ResponseValidatorJsonApi($jsonSerialiser, $exceptionFactory, $config['validateIncludeRequestBodyInResponse']);
            $middleware = new JsonApiRequestValidatorMiddleware($requestValidator, $responseValidator, $exceptionFactory, $config);
            return $middleware;
        };
    }
}