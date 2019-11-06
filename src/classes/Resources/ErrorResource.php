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
}