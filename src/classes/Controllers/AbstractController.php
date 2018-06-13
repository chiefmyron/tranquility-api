<?php namespace Tranquility\Controllers;

// Fractal class libraries
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;

// Tranquility class libraries
use Tranquility\Resources\AbstractResource;

class AbstractController {
    /**
     * Output manager
     * 
     * @var \League\Fractal\Manager
     */
    protected $manager;

    /**
     * Entity resource
     * 
     * @var \Tranquility\Resources\AbstractResource
     */
    protected $resource;

    public function __construct(AbstractResource $resource, Manager $manager) {
        $this->resource = $resource;
        $this->manager = $manager;
    }

    public function generateValidationErrorResponse($response, array $errors) {
        return $response->withJson($errors, 500);
    }
}