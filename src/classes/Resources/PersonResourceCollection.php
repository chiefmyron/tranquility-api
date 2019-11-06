<?php namespace Tranquility\Resources;

class PersonResourceCollection extends AbstractResourceCollection {

    /**
     * Create a new resource instance.
     *
     * @param  mixed         $data     The resource object or array of resource objects
     * @param  \Slim\Router  $router   Application router
     * @return void
     */
    public function __construct($data, $router) {
        parent::__construct($data, $router);
        $this->resourceClassname = PersonResource::class;
    }
}