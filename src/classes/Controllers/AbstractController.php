<?php namespace Tranquility\Controllers;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Tranquility class libraries
use Tranquility\System\Utility;
use Tranquility\Services\AbstractService;
use Tranquility\System\Enums\FilterOperatorEnum;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

// Tranquility error messaging
use Tranquility\App\Errors\Helpers\ErrorCollection;
use Tranquility\Documents\AbstractDocument;

class AbstractController {
    /**
     * Entity service
     * 
     * @var \Tranquility\Services\AbstractService
     */
    protected $service;
    
    /**
     * Constructor
     *
     * @param \Tranquility\Services\AbstractService  $service  Service used to interact with the primary entity data
     * @return void
     */
    public function __construct(AbstractService $service) {
        $this->service = $service;
    }

    /**
     * Retrieve a list of entities
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function list(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Retrieve users
        $params = $this->_parseQueryStringParams($request);
        $data = $this->service->all($params['filters'], $params['sorting'], $params['pagination']['pageNumber'], $params['pagination']['pageSize']);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Retrieve a single entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function show(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Retrieve an individual user
        $id = Utility::extractValue($args, 'id', null, 'string');
        $data = $this->service->find($id);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Create a new entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function create(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $payload = $request->getParsedBody();

        // Attempt to create the user entity
        $data = $this->service->create($payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::Created);
    }

    /**
     * Update an existing entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function update(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $payload = $request->getParsedBody();

        // Attempt to update the user entity
        $data = $this->service->update($id, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Delete an existing entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function delete(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $payload = $request->getParsedBody();

        // Attempt to update the user entity
        $data = $this->service->delete($id, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::NoContent);
    }
    
    /**
     * Retrieve an entity related to the main entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showRelated(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $resourceName = Utility::extractValue($args, 'resource', null, 'string');

        // Retrieve the related entity
        $data = $this->service->getRelatedEntity($id, $resourceName);
        return $this->_generateResponse($request, $response, $data, HttpStatus::OK);
    }

    /**
     * Show details of a relationship for an entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function showRelationship(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $resourceName = Utility::extractValue($args, 'resource', null, 'string');

        // Retrieve relationship data
        $data = $this->service->getRelatedEntity($id, $resourceName);
        if ($data instanceof ErrorCollection) {
            // Specified relationship does not exist for the entity - return error
            return $this->_generateResponse($request, $response, $data, HttpStatus::NotFound);
        }

        // Load the main entity, so that the relationship links can be generated relative to it
        $data = $this->service->find($id);
        return $this->_generateRelationshipResponse($request, $response, $data, $resourceName, HttpStatus::OK);
    }

    /**
     * Add one or more members to a relationship for an entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function addRelationship(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $resourceName = Utility::extractValue($args, 'resource', null, 'string');
        $payload = $request->getParsedBody();

        // Add relationship to the specified entity
        $data = $this->service->addRelationshipMembers($id, $resourceName, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::NoContent);
    }

    /**
     * Update / replace all members in a relationship for an entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function updateRelationship(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $resourceName = Utility::extractValue($args, 'resource', null, 'string');
        $payload = $request->getParsedBody();

        // Add relationship to the specified entity
        $data = $this->service->updateRelationshipMembers($id, $resourceName, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::NoContent);
    }

    /**
     * Delete the specified members in a relationship for an entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function deleteRelationship(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', null, 'string');
        $resourceName = Utility::extractValue($args, 'resource', null, 'string');
        $payload = $request->getParsedBody();

        // Add relationship to the specified entity
        $data = $this->service->deleteRelationshipMembers($id, $resourceName, $payload);
        return $this->_generateResponse($request, $response, $data, HttpStatus::NoContent);
    }

    // ************************************ Helper functions ************************************ //

    /**
     * Generates a JSON:API compliant response message for both normal data responses and errors
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request         PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response        PSR-7 HTTP response object
     * @param mixed                                     $data            Either an entity, array of entities, an error collection, or null value
     * @param int                                       $httpStatusCode  HTTP status code to return for normal data response. If an error collection is provided as $data, the status code will be automatically determined.
     * @param string                                    $documentType    [Optional] Force the document to generate for the supplied entity
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _generateResponse(ServerRequestInterface $request, ResponseInterface $response, $data, int $httpStatusCode, string $documentType = '') {
        // If we have received a null data object, return an empty document with the specified HTTP status code
        if (is_null($data)) {
            return $response->withStatus($httpStatusCode);
        }
        
        // Create a response document from the data supplied
        $document = AbstractDocument::createDocument($data, $request, $documentType);
        if ($data instanceof ErrorCollection) {
            $httpStatusCode = $data->getHttpStatusCode();
        }

        // Return encoded document in the response
        $json = json_encode($document->toArray());
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/vnd.api+json')->withStatus($httpStatusCode);
    }

    /**
     * Generates a JSON:API compliant response message for a relationship request on an entity
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request         PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response        PSR-7 HTTP response object
     * @param mixed                                     $data            Either an entity, array of entities, an error collection, or null value
     * @param string                                    $resourceName    Name of the related resource to show
     * @param int                                       $httpStatusCode  HTTP status code to return for normal data response. If an error collection is provided as $data, the status code will be automatically determined.
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _generateRelationshipResponse(ServerRequestInterface $request, ResponseInterface $response, $data, string $resourceName, int $httpStatusCode) {
        // If we have been given an error collection, then generate a regular error response (there can be no relationships in an error collection)
        if ($data instanceof ErrorCollection) {
            return $this->_generateResponse($request, $response, $data, $httpStatusCode);
        }

        // Create a response document from the supplied data
        $document = AbstractDocument::createDocument($data, $request);
        $documentData = $document->getMember('data');
        $relationships = $documentData->getMember('relationships');
        if (is_null($relationships) == true || $relationships->hasMember($resourceName) == false) {
            // TODO: Error messaging
        }

        // Return relationship object as the response
        $json = json_encode($relationships->getMember($resourceName, true));
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/vnd.api+json')->withStatus(HttpStatus::OK);

    }

    protected function _generateErrorResponse(ServerRequestInterface $request, ResponseInterface $response, int $applicationErrorCode, string $description = '') {
        
    }

    /**
     * Parse the request query string for filtering, sorting and pagination parameters
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR-7 HTTP request object
     * @return array
     */
    protected function _parseQueryStringParams(ServerRequestInterface $request) {
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
        $sortParams = explode(',', $sort);
        foreach($sortParams as $sortItem) {
            // If the item starts with a minus character, it indicates descending sort order for that field
            if (mb_substr($sortItem, 0, 1) == '-') {
                $sortItem = mb_substr($sortItem, 1);
                $sorting[] = [$sortItem, 'DESC'];
            } elseif (mb_strlen($sortItem) > 0) {
                $sorting[] = [$sortItem, 'ASC'];
            }
        }

        // Get pagination parameters
        $page = Utility::extractValue($queryStringParams, 'page', array());
        $pagination = [
            'pageNumber' => Utility::extractValue($page, 'number', 0),
            'pageSize' => Utility::extractValue($page, 'size', 0)
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
     * @param \Psr\Http\Message\ServerRequestInterface  $request       PSR-7 HTTP request object
     * @param string                                    $reasonReason  Text to use as the audit trail 'update reason'
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function _setAuditTrailReason(ServerRequestInterface $request, string $reasonReason) {
        $body = $request->getParsedBody();
        $meta = Utility::extractValue($body, 'meta', array());
        $meta['updateReason'] = $reasonReason;
        $body['meta'] = $meta;
        $request = $request->withParsedBody($body);
        return $request;
    }
}