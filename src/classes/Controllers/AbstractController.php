<?php namespace Tranquility\Controllers;

// Utility libraries
use Carbon\Carbon;

// Framework libraries
use Slim\Router;

// Tranquility class libraries
use Tranquility\System\Utility;
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

    protected function _parseQueryStringParams($request) {
        // Get filtering parameters
        // TODO: Populate this properly
        $filters = [];

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