<?php namespace Tranquility\Middlewares;

// OAuth2 server libraries
use OAuth2\Request as OAuthRequest;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

/**
 * Middleware to ensure only authenticated users can access protected routes
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 */
class AuthenticationMiddleware extends AbstractMiddleware {
    // OAuth server instance
    private $server;

    // Authentication resource
    private $resource;

    public function __construct($server, $resource) {
        $this->server = $server;
        $this->resource = $resource;
    }

    /**
     * Validate that the request contains a valid access token
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {
        $req = OAuthRequest::createFromGlobals();

        if ($this->server->verifyResourceRequest($req) != true) {
            $this->server->getResponse()->send();
            exit();
        }

        // Store the username for the authenticated user in the request
        $token = $this->server->getAccessTokenData($req);

        // Add audit trail details to the request
        $audit = $this->resource->generateAuditTrail($token);
        $request = $request->withAttribute('audit', $audit);
        return $next($request, $response);
    }
}