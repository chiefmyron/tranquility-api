<?php namespace Tranquility\Controllers;

// Utility libraries
use Carbon\Carbon;

// Framework libraries
use Slim\Router;

// Tranquility class libraries
use Tranquility\Services\AbstractService;
use Tranquility\Resources\AbstractResource;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class AbstractController {
    /**
     * Entity service
     * 
     * @var Tranquility\Services\AbstractService
     */
    protected $service;

    /**
     * Application router
     * 
     * @var Slim\Router
     */
    protected $router;

    /**
     * Version of the JSON API document being used
     *
     * @var string
     */
    private $_jsonapiVersion = "1.0";

    public function __construct(AbstractService $service, Router $router) {
        $this->service = $service;
        $this->router = $router;
    }

    public function generateJsonResponse($request, $response, $resource, $responseCode) {
        if ($resource instanceof AbstractResource) {
            $payload = $resource->toResponseArray($request);
        } elseif (is_array($resource) || is_iterable($resource)) {
            $payload = $resource;
        } else {
            throw new \Exception("Resource provided is not an instance of \Tranquility\AbstractResource, or an array.");
        }
        return $response->withJson($payload, $responseCode);
    }

    /**
     * Generates a JSON API response payload for a single error
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $error
     * @return void
     */
    protected function withError(\Psr\Http\Message\ResponseInterface $response, array $error) {
        $data = ["jsonapi" => ["version" => $this->_jsonapiVersion], "errors" => [$error]];
        $response = $response->withJson($data, $error['status']);
        return $response;
    }

    /**
     * Generates a JSON API response payload for a collection of errors
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $errorCollection
     * @param integer $httpResponseCode
     * @return void
     */
    protected function withErrorCollection(\Psr\Http\Message\ResponseInterface $response, array $errorCollection, int $httpResponseCode) {
        $data = ["jsonapi" => ["version" => $this->_jsonapiVersion], "errors" => $errorCollection];
        $response = $response->withJson($data, $httpResponseCode);
        return $response;
    }
}