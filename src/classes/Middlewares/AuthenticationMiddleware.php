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
    private $_server;

    public function __construct($server) {
        $this->server = $server;
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
        $server = $this->server;
        $req = OAuthRequest::createFromGlobals();

        if ($server->verifyResourceRequest($req) != true) {
            $server->getResponse()->send();
            exit();
        }

        // Store the username for the authenticated user in the request
        $token = $server->getAccessTokenData($req);
        $request = $request->withAttribute('username', $token['user_id']);
        return $next($request, $response);
    }
}