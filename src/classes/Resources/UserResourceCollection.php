<?php namespace Tranquility\Resources;

class UserResourceCollection extends AbstractResourceCollection {
    
    /**
     * Create a new resource instance.
     *
     * @param  mixed         $data     The resource object or array of resource objects
     * @param  \Slim\Router  $router   Application router
     * @return void
     */
    public function __construct($data, $router) {
        parent::__construct($data, $router);
        $this->resourceClassname = UserResource::class;
    }
}