<?php namespace Tranquility\Controllers;

// Utility libraries
use Carbon\Carbon;

// Framework libraries
use Slim\Router;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

// Tranquility class libraries
use Tranquility\Services\AbstractService;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class AbstractController {
    /**
     * Output manager
     * 
     * @var League\Fractal\Manager
     */
    protected $manager;

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

    public function __construct(AbstractService $service, Manager $manager, Router $router) {
        $this->service = $service;
        $this->manager = $manager;
        $this->router = $router;
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