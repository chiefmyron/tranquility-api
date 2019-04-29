<?php namespace Tranquility\Resources;

use Carbon\Carbon;

abstract class AbstractResource {
    /**
     * The entity that the resource will represent
     * 
     * @var mixed
     */
    protected $data;

    /**
     * The additional data that should be added to the top-level resource array.
     *
     * @var array
     */
    protected $with = [];

    /**
     * The additional meta data that should be added to the resource response.
     *
     * Added during response construction by the developer.
     *
     * @var array
     */
    protected $additional = [];

    /**
     * Version of the JSON:API spec that output will conform to
     * 
     * @see https://jsonapi.org/format/
     *
     * @var string
     */
    protected $jsonApiVersion = "1.0";

    /**
     * Wrapping element around $data
     * 
     * @var string
     */
    protected $wrapper = 'data';

    /**
     * Application router
     * 
     * @var \Slim\Router
     */
    protected $router;

    /**
     * Create a new resource instance.
     *
     * @param  mixed         $data     The resource object or array of resource objects
     * @param  \Slim\Router  $router   Application router
     * @param  string        $wrapper  Wrapping element around $data
     * @return void
     */
    public function __construct($data, $router, $wrapper = 'data') {
        $this->data = $data;
        $this->router = $router;
        if ($wrapper != $this->wrapper) {
            $this->wrapper = $wrapper;
        }
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        if (is_null($this->data)) {
            return [];
        }

        if (is_array($this->data)) {
            return $this->data;
        }

        return $this->data->toArray();
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request) {
        return $this->with;
    }

    /**
     * Add additional meta data to the resource response.
     *
     * @param  array  $data
     * @return $this
     */
    public function additional(array $data) {
        $this->additional = $data;

        return $this;
    }

    /**
     * Add JSON:API spec version details to the resource response
     * 
     * @return array
     */
    public function jsonapi() {
        return ["jsonapi" => ["version" => $this->jsonApiVersion]];
    }

    /**
     * Transform the resource into a JSON:API compatible array.
     * 
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toResponseArray($request) {
        // Resolve resource
        $data = $this->resolve($request);

        // Apply filters and resolve any embedded resources
        $data = $this->filter($data, $request);

        // Add wrapping to data resource (if not already wrapped)
        if (array_key_exists($this->wrapper, $data) == false) {
            $data = [$this->wrapper => $data];
        }

        // Resolve additional related resource
        $with = $this->with($request);
        $additional = $this->additional;
        $jsonapi = $this->jsonapi();
        return array_merge_recursive($data, $with, $additional, $jsonapi);
    }

    /**
     * Resolve the resource to an array. Recursively processes any related resources.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    public function resolve($request) {
        // Convert resource into array
        $data = $this->toArray($request);

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        return $data;
    }

    public function filter($data, $request) {
        // Recursively resolve embedded resources and correctly format values
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // If value is an array, filter all elements of the array
                $data[$key] = $this->filter($value, $request);
            } elseif ($value instanceof \DateTime) {
                // If value is a DateTime value, convert to ISO8601 valid string
                $data[$key] = Carbon::instance($value)->toIso8601String();
            } elseif ($value instanceof AbstractResource) {
                // If value is another Resource, convert it to an array and filter
                $resource = $value->resolve($request);
                $data[$key] = $this->filter($resource, $request);
            }
        }

        return $data;
    }

    protected function generateUri($request, $routeName, $params = array()) {
        return $request->getUri()->getBaseUrl().$this->router->pathFor($routeName, $params);
    }
}