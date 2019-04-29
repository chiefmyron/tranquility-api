<?php namespace Tranquility\Resources;

class ErrorResource extends AbstractResource {
    /**
     * Create a new resource instance.
     *
     * @param  mixed         $data    The resource object or array of resource objects
     * @return void
     */
    public function __construct($data, $router, $wrapper = 'errors') {
        return parent::__construct($data, $router, $wrapper);
    }

    /**
     * Transform the resource into a JSON:API compatible array.
     * 
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function toResponseArray($request) {
        // Resolve resource
        $errors = $this->resolve($request);

        // Add wrapping to data resource (if not already wrapped)
        if (array_key_exists($this->wrapper, $errors) == false) {
            $errors = [$this->wrapper => $errors];
        }

        // Resolve additional related resource
        $with = $this->with($request);
        $additional = $this->additional;
        $jsonapi = $this->jsonapi();
        return array_merge_recursive($errors, $with, $additional, $jsonapi);
    }
}