<?php namespace Tranquility\Services;

abstract class AbstractService {
    /**
     * The application instance
     * 
     * @var \Slim\App
     */
    protected $app;

    /** 
     * Create a new service provider interface
     * 
     * @param  \Slim\App  $app
     * @return void
     */
    public function __construct(\Slim\App $app) {
        $this->app = $app;
    }

    /**
     * Registers the service with the application container
     * 
     * @param  string  $name  The name of the key used to address the service once it is registered
     * @return void
     */
    abstract public function register(string $name);
}