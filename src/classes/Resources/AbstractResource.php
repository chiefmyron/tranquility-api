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
     * Wrapping element around $data
     * 
     * @var string
     */
    protected $wrapper = 'data';

    /**
     * Version of the JSON:API spec that output will conform to
     * 
     * @see https://jsonapi.org/format/
     *
     * @var string
     */
    protected $jsonApiVersion = "1.0";

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
     * Generate 'data' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
        if (is_null($this->data)) {
            return [];
        }

        if (is_array($this->data)) {
            return $this->data;
        }

        return $this->data->toArray();
    }

    /**
     * Generate 'meta' top-level member for response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function meta($request) {
        return [];
    }

    /**
     * Generate 'links' top-level member for response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function links($request) {
        $links = [
            'self' => $this->generateBaseUrl($request).$request->getRequestTarget()
        ];
        return $links;
    }

    /**
     * Generate 'included' top-level member for response
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function included($request) {
        return [];
    }

    /**
     * Generate 'jsonapi' top-level member for response. Adds the 
     * JSON:API spec version details to the resource response
     * 
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return array
     */
    public function jsonapi($request) {
        return ["version" => $this->jsonApiVersion];
    }

    /**
     * Transform the resource into a JSON:API compatible array.
     * 
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toResponseArray($request) {
        $responseArray = [];

        // Generate 'data' top-level member
        $data = $this->toArray($request);
        $data = $this->resolve($data, $request);
        if (array_key_exists($this->wrapper, $data) == false) {
            // Add wrapping to data resource (if not already wrapped)
            $responseArray[$this->wrapper] = $data;
        } else {
            $responseArray = $data;
        }

        // Add other top-level members
        $memberNames = ['meta', 'links', 'included', 'jsonapi'];
        foreach ($memberNames as $name) {
            $value = $this->$name($request);
            if (is_array($value) && count($value) > 0) {
                $responseArray[$name] = $value;
            }
        }

        return $responseArray;
    }

    /**
     * Converts the resource data to an array
     *
     * @param  \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    public function toArray($request) {
        // Convert resource into array
        $data = $this->data($request);
        return $data;
    }

    /**
     * Iterates through the array representation of the resource data and recursively 
     * processes any related resources.
     *
     * @param array $data
     * @param \Psr\Http\Message\ServerRequestInterface  $request  PSR7 request
     * @return array
     */
    public function resolve($data, $request) {
        // Recursively resolve embedded resources and correctly format values
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // If value is an array, filter all elements of the array
                $data[$key] = $this->resolve($value, $request);
            } elseif ($value instanceof \DateTime) {
                // If value is a DateTime value, convert to ISO8601 valid string
                $data[$key] = Carbon::instance($value)->toIso8601String();
            } elseif ($value instanceof AbstractResource) {
                // If value is another Resource, convert it to an array and filter
                $resource = $value->toArray($request);
                $data[$key] = $this->resolve($resource, $request);
            }
        }

        return $data;
    }

    /**
     * Generate fully qualified URL from a route
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request    PSR7 request
     * @param string                                    $routeName  Name of the route to be generated
     * @param array                                     $params     Route parameter values
     * @return string
     */
    protected function generateUrl($request, $routeName, $params = array()) {
        $uri = $request->getUri();
        $routeParser = $request->getAttribute('routeParser');
        return $routeParser->fullUrlFor($uri, $routeName, $params);
    }

    /**
     * Generate a fully qualified base URL (including custom base path, if applicable)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return string
     */
    protected function generateBaseUrl($request) {
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();
        $basePath = $request->getAttribute('basePath', '');

        $urlString = ($scheme !== '' ? $scheme.':' : '') . ($authority ? '//'.$authority : '') . rtrim($basePath, '/');
        return $urlString;
    }
}