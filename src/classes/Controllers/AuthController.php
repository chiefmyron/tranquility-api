<?php namespace Tranquility\Controllers;

use OAuth2\Server;
use OAuth2\Request;

class AuthController {
    /**
     * OAuth server
     * 
     * @var OAuth2\Server
     */
    private $server;

    public function __construct(Server $server) {
        $this->server = $server;
    }

    public function login($request, $response, $args) {
        // Attempt to login user
        $data = $this->parseRequestBody($request);
        $result = $this->resource->login($data);

        return $response->withJson($result, HttpStatus::Created);
    }

    public function token($request, $response, $args) {
        // Check token request against OAuth server
        $serverRequest = Request::createFromGlobals();
        $serverResponse = $this->server->handleTokenRequest($serverRequest);

        // Send back response
        $response = $response->withStatus($serverResponse->getStatusCode());
        foreach ($serverResponse->getHttpHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write($serverResponse->getResponseBody('json'));
    }
}