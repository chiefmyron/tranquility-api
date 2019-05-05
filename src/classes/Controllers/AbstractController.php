<?php namespace Tranquility\Controllers;

// Utility libraries
use Carbon\Carbon;

// Framework libraries
use Slim\Router;

// Tranquility class libraries
use Tranquility\System\Utility;
use Tranquility\Services\AbstractService;
use Tranquility\Resources\AbstractResource;
use Tranquility\System\Enums\FilterOperatorEnum;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

// Tranquility entity classes
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

// Tranquility resources
use Tranquility\Resources\UserResource;
use Tranquility\Resources\ErrorNotFoundResource;
use Tranquility\Resources\ErrorValidationResource;

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
     * Class name of the primary entity for the controller
     * 
     * @var string
     */
    protected $entityClassname;
    
    /**
     * Class name of the resource used to represent a single entity
     * 
     * @var string
     */
    protected $entityResourceClassname;

    /**
     * Class name of the resource used to represent a collection of entities
     * 
     * @var string
     */
    protected $entityResourceCollectionClassname;

    /**
     * Constructor
     *
     * @param AbstractService  $service  Service used to interact with the primary entity data
     * @param Router           $router   Application router
     */
    public function __construct(AbstractService $service, Router $router) {
        $this->service = $service;
        $this->router = $router;
    }

    public function list($request, $response, $args) {
        // Retrieve users
        $params = $this->_parseQueryStringParams($request);
        $data = $this->service->all($params['filters'], $params['sorting'], $params['pagination']['pageNumber'], $params['pagination']['pageSize']);

        // Check that service has returned an array of data
        if (is_array($data) && count($data) > 0 && $this->_checkInstanceOfEntity($data[0]) === false) {
            // Service has encountered an error
            return $this->_generateJsonErrorResponse($request, $response, null, $data);
        }

        // Data is a collection of users
        $resource = new $this->entityResourceCollectionClassname($data, $this->router);
        return $this->_generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    public function show($request, $response, $args) {
        // Retrieve an individual user
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $data = $this->service->find($id);
        if ($this->_checkInstanceOfEntity($data) === false) {
            return $this->_generateJsonErrorResponse($request, $response, $id, $data);
        }

        // Data is an instance of a user
        $resource = new $this->entityResourceClassname($data, $this->router);
        return $this->_generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    public function create($request, $response, $args) {
        // Get data from request
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_create_new_record';

        // Attempt to create the user entity
        $data = $this->service->create($payload);
        if ($this->_checkInstanceOfEntity($data) === false) {
            return $this->_generateJsonErrorResponse($request, $response, null, $data);
        }

        // Data is an instance of a user
        $resource = new $this->entityResourceClassname($data, $this->router);
        return $this->_generateJsonResponse($request, $response, $resource, HttpStatus::Created);
    }

    public function update($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_update_existing_record';

        // Attempt to update the user entity
        $data = $this->service->update($id, $payload);
        if ($this->_checkInstanceOfEntity($data) === false) {
            return $this->_generateJsonErrorResponse($request, $response, $id, $data);
        }

        // Transform for output
        $resource = new $this->entityResourceClassname($data, $this->router);
        return $this->_generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    public function delete($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $payload = $request->getParsedBody();
        $payload['meta']['updateReason'] = 'user_delete_existing_record';

        // Attempt to update the user entity
        $data = $this->service->delete($id, $payload);
        if ($this->_checkInstanceOfEntity($data) === false) {
            return $this->_generateJsonErrorResponse($request, $response, $id, $data);
        }

        // Transform for output
        return $this->_generateJsonResponse($request, $response, null, HttpStatus::NoContent);
    }
    
    public function related($request, $response, $args) {
        // Get data from request
        $id = Utility::extractValue($args, 'id', 0, 'int');
        $related = Utility::extractValue($args, 'resource', '', 'string');

        // Retrieve the related entity
        $entity = $this->service->getRelatedEntity($id, $related);
        if ($entity === false) {
            return $this->_generateJsonErrorResponse($request, $response, $id, $entity);
        }

        // Return related entity
        $relatedEntityClass = new \ReflectionClass($entity);
        $relatedEntityClassname = $relatedEntityClass->getName();
        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            // Handle lazy-loaded related entities
            $relatedEntityClassname = $relatedEntityClass->getParentClass()->getName();
        }

        switch ($relatedEntityClassname) {
            case User::class:
                $resource = new UserResource($entity, $this->router);
                break;
        }

        // Transform for output
        return $this->_generateJsonResponse($request, $response, $resource, HttpStatus::OK);
    }

    protected function _generateJsonResponse($request, $response, $resource, $responseCode) {
        if ($resource instanceof AbstractResource) {
            $payload = $resource->toResponseArray($request);
        } elseif (is_array($resource) || is_iterable($resource)) {
            $payload = $resource;
        } elseif (is_null($resource)) {
            $payload = null;   
        } else {
            throw new \Exception("Resource provided is not an instance of \Tranquility\AbstractResource, an array or null.");
        }
        return $response->withJson($payload, $responseCode);
    }

    protected function _generateJsonErrorResponse($request, $response, $id, $data) {
        $resource = null;
        $httpStatusCode = null;
        
        if ($data === false) {
            // Entity does not exist
            $resource = new ErrorNotFoundResource($id, $this->router);
            $httpStatusCode = HttpStatus::NotFound;
        } elseif (!($data instanceof User)) {
            // Service has encountered an error
            $resource = new ErrorValidationResource($data, $this->router);
            $httpStatusCode = HttpStatus::UnprocessableEntity;
        }

        return $this->_generateJsonResponse($request, $response, $resource, $httpStatusCode);
    }

    protected function _parseQueryStringParams($request) {
        // Get filtering parameters
        $filters = [];
        $filter = $request->getQueryParam("filter", array());
        foreach ($filter as $field => $value) {
            // Check to see if the value is prefixed with an operator
            $valueParams = explode(":", $value);
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
        $sort = $request->getQueryParam("sort", "");
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
        $page = $request->getQueryParam("page", array());
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

    protected function _checkInstanceOfEntity($data, $entityClassname = "") {
        // Check that the data provided is an object
        if (!is_object($data)) {
            return false;
        }

        // Get class name of the data object provided
        $dataClass = new \ReflectionClass($data);
        $dataClassname = $dataClass->getName();
        if ($data instanceof \Doctrine\ORM\Proxy\Proxy) {
            // Handle lazy-loaded related entities
            $dataClassname = $dataClass->getParentClass()->getName();
        }

        // If no entity class type has been provided, use the default for the controller
        if ($entityClassname == "") {
            $entityClassname = $this->entityClassname;
        }

        // Check to see if the data object is an instance of the specified entity
        if ($entityClassname !== $dataClassname) {
            return false;
        }
        return true;
    }

    protected function _setAuditTrailReason($request, $reasonCode) {
        $body = $request->getParsedBody();
        $meta = Utility::extractValue($body, 'meta', array());
        $meta['updateReason'] = $reasonCode;
        $body['meta'] = $meta;
        $request = $request->withParsedBody($body);
        return $request;
    }
}