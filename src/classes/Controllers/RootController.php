<?php namespace Tranquility\Controllers;

// PSR standards interfaces
use Psr\Container\ContainerInterface;

class RootController {

    protected $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    public function home($request, $response, $args) {
        $response->getBody()->write("Hello world!");
        return $response;
    }
}