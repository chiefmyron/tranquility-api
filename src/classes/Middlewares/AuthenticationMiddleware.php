<?php namespace Tranquility\Middlewares;

// Utility libraries
use Carbon\Carbon;

// OAuth2 server libraries
use OAuth2\Request as OAuthRequest;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
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
        $req = OAuthRequest::createFromGlobals();
        if ($this->server->verifyResourceRequest($req) != true) {
            $this->server->getResponse()->send();
            exit();
        }

        // Store the username for the authenticated user in the request
        $token = $this->server->getAccessTokenData($req);

        // Update request with audit trail information in the 'meta' section
        $body = $request->getParsedBody();
        $meta = Utility::extractValue($body, 'meta', array());
        $meta['user'] = Utility::extractValue($token, 'user_id', 0);
        $meta['client'] = Utility::extractValue($token, 'client_id', 'invalid_client_id');
        $body['meta'] = $meta;
        $request = $request->withParsedBody($body);

        // Move on to next middleware
        return $next($request, $response);
    }
}