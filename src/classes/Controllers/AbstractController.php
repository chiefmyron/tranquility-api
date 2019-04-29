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

// Tranquility error resources
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

    public function __construct(AbstractService $service, Router $router) {
        $this->service = $service;
        $this->router = $router;
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
}