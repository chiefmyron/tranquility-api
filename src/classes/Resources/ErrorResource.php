<?php namespace Tranquility\Resources;

use Tranquility\App\Errors\Helpers\ErrorCollection;

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
     * Generate 'error' representation for the resource
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @return array
     */
    public function data($request) {
        if ($this->data instanceof ErrorCollection) {
            return $this->data->toArray();
        }
    }
}