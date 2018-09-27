<?php namespace Tranquility\Controllers;

// Utility libraries
use Carbon\Carbon;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

// Tranquility class libraries
use Tranquility\Resources\AbstractResource;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class AbstractController {
    /**
     * Output manager
     * 
     * @var League\Fractal\Manager
     */
    protected $manager;

    /**
     * Entity resource
     * 
     * @var Tranquility\Resources\AbstractResource
     */
    protected $resource;

    /**
     * Version of the JSON API document being used
     *
     * @var string
     */
    private $_jsonapiVersion = "1.0";

    public function __construct(AbstractResource $resource, Manager $manager) {
        $this->resource = $resource;
        $this->manager = $manager;
    }

    protected function parseRequestBody($request) {
        // Get body from request
        $body = $request->getParsedBody();
        $data = $body['data'];

        // Inject system-generated audit trail values
        $data['attributes']['updateUserId'] = 1;
        $data['attributes']['updateDateTime'] = Carbon::now();
        return $data;
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