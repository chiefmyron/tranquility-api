<?php namespace Tranquility\Controllers;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// Tranquility class libraries
use Tranquility\System\Utility;
use Tranquility\Services\AbstractService;
use Tranquility\Resources\AbstractResource;
use Tranquility\System\Enums\FilterOperatorEnum;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

// Tranquility resources
use Tranquility\Resources\ErrorResource;
use Tranquility\Resources\ResourceCollection;
use Tranquility\Resources\ResourceItem;

// Tranquility error messaging
use Tranquility\App\Errors\Helpers\ErrorCollection;

class AbstractController {
    /**
     * Entity service
     * 
     * @var Tranquility\Services\AbstractService
     */
    protected $service;
    
    /**
     * Constructor
     *
     * @param AbstractService  $service  Service used to interact with the primary entity data
     * @return void
     */
    public function __construct(AbstractService $service) {
        $this->service = $service;
    }

    /**
     * Retrieve a list of entities
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function list($request, $response, $args) {
        // Retrieve users
        $params = $this->_parseQueryStringParams($request);
        $data = $this->service->all($params['filters'], $params['sorting'], $params['pagination']['pageNumber'], $params['pagination']['pageSize']);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Retrieve a single entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function show($request, $response, $args) {
        // Retrieve an individual user
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $data = $this->service->find($id);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Create a new entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create($request, $response, $args) {
        // Get data from request
        $payload = $request->getParsedBody();

        // Attempt to create the user entity
        $data = $this->service->create($payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::Created);
    }

    /**
     * Update an existing entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();

        // Attempt to update the user entity
        $data = $this->service->update($id, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Delete an existing entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();

        // Attempt to update the user entity
        $data = $this->service->delete($id, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::NoContent);
    }
    
    /**
     * Retrieve an entity related to the main entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showRelated($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $resourceName = Utility::extractValue($args, 'resource', '', 'string');

        // Retrieve the related entity
        $data = $this->service->getRelatedEntity($id, $resourceName);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Show details of a relationship for an entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface       $response
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showRelationship($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $resourceName = Utility::extractValue($args, 'resource', '', 'string');

        // Retrieve entity data
        $data = $this->service->find($id);
        if ($data instanceof ErrorCollection) {
            return $this->_generateResponse($request, $response, $data, HttpStatus::InternalServerError);
        }

        // Generate response document for entity
        $resource = new ResourceItem($data, $this->router);
        $relationships = $resource->getRelationships($request);

        // Extract only the relationship for the specified resource, and return as the response
        if (array_key_exists($resourceName, $relationships) === false) {
            return $this->_generateResponse($request, $response, null, HttpStatus::InternalServerError);
        }

        // Return resource array
        $json = json_encode($relationships[$resourceName]);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/vnd.api+json')->withStatus(HttpStatus::OK);
    }

    // ************************************ Helper functions ************************************ //

    /**
     * Generates a JSON:API compliant response message for both normal data responses and errors
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request         HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response        HTTP response object
     * @param mixed                                     $data            Either an entity, array of entities, an error collection, or null value
     * @param string                                    $httpStatusCode  HTTP status code to return for normal data response. If an error collection is provided as $data, the status code will be automatically determined.
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _generateResponse($request, $response, $data, $httpStatusCode) {
        // Get the route from the request
        $route = $request->getAttribute('route');

        // Create resource representation of data
        $resource = null;
        if ($data instanceof ErrorCollection) {
            $resource = new ErrorResource($data, $route);
            //$httpStatusCode = $data->getHttpStatusCode();  // @todo Implement this logic in error collection
        } elseif (is_array($data) || is_iterable($data)) {
            $resource = new ResourceCollection($data, $route);
        } elseif (is_null($data) == false) {
            $resource = new ResourceItem($data, $route);
        } else {
            throw new \Exception("Resource provided is not an instance of '" . AbstractResource::class . "', an array or null.");
        }

        // Cast resource to array
        $payload = null;
        if (is_null($resource) == false) {
            $payload = $resource->toResponseArray($request);
        }

        // Return resource array
        $json = json_encode($payload);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/vnd.api+json')->withStatus($httpStatusCode);
    }

    /**
     * Parse the request query string for filtering, sorting and pagination parameters
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    protected function _parseQueryStringParams($request) {
        // Get array of query string parameters from request
        $queryStringParams = $request->getQueryParams();

        // Get filtering parameters
        $filters = [];
        $filter = Utility::extractValue($queryStringParams, 'filter', array());
        foreach ($filter as $field => $value) {
            // Check to see if the value is prefixed with an operator
            $valueParams = explode(':', $value);
            if (count($valueParams) == 1) {
                // Check to see if the value parameter is a NULL operator, or just a value
                if ($valueParams[0] == FilterOperatorEnum::IsNull || $valueParams[0] == FilterOperatorEnum::IsNotNull) {
                    // NULL and NOT NULL operators will not have a value parameter
                    $filters[] = [$field, $valueParams[0]];
                } else {
                    // Not prefixed with an operator - assume equality
                    $filters[] = [$field, FilterOperatorEnum::Equals, $valueParams[0]];
                }
            } else {
                // Use the prefixed operator
                $filters[] = [$field, $valueParams[0], $valueParams[1]];
            }
        }

        // Get sorting parameters
        $sorting = [];
        $sort = Utility::extractValue($queryStringParams, 'sort', '');
        $sortParams = explode(",", $sort);
        foreach($sortParams as $sortItem) {
            // If the item starts with a minus character, it indicates descending sort order for that field
            if (mb_substr($sortItem, 0, 1) == "-") {
                $sortItem = mb_substr($sortItem, 1);
                $sorting[] = [$sortItem, "DESC"];
            } elseif (mb_strlen($sortItem) > 0) {
                $sorting[] = [$sortItem, "ASC"];
            }
        }

        // Get pagination parameters
        $page = Utility::extractValue($queryStringParams, 'page', array());
        $pagination = [
            'pageNumber' => Utility::extractValue($page, "number", 0),
            'pageSize' => Utility::extractValue($page, "size", 0)
        ];

        // Return parsed parameters
        $params = [
            'pagination' => $pagination,
            'filters' => $filters,
            'sorting' => $sorting
        ];

        return $params;
    }

    /**
     * Insert audit trail information into the HTTP request
     *
     * @param \Slim\Http\Request   $request
     * @param string               $reasonReason  Text to use as the audit trail 'update reason'
     * @return \Slim\Http\Request
     */
    protected function _setAuditTrailReason($request, $reasonReason) {
        $body = $request->getParsedBody();
        $meta = Utility::extractValue($body, 'meta', array());
        $meta['updateReason'] = $reasonReason;
        $body['meta'] = $meta;
        $request = $request->withParsedBody($body);
        return $request;
    }
}