<?php namespace Tranquility\Controllers;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Library classes
use OAuth2\Server;
use OAuth2\Request;

class AuthController {
    /**
     * OAuth server
     * 
     * @var OAuth2\Server
     */
    private $server;

    /**
     * Constructor
     *
     * @param Server $server OAuth server
     */
    public function __construct(Server $server) {
        $this->server = $server;
    }

    /**
     * Generates an OAuth token
     *
     * @param \Psr\Http\Message\ServerRequestInterface  $request   PSR-7 HTTP request object
     * @param \Psr\Http\Message\ResponseInterface       $response  PSR-7 HTTP response object
     * @param array                                     $args      Route arguments array
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function token(ServerRequestInterface $request, ResponseInterface $response, array $args) {
        // Check token request against OAuth server
        $serverRequest = Request::createFromGlobals();
        $serverResponse = $this->server->handleTokenRequest($serverRequest);

        // Send back response
        $response = $response->withStatus($serverResponse->getStatusCode());
        foreach ($serverResponse->getHttpHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($serverResponse->getResponseBody('json'));
        return $response;
    }
}