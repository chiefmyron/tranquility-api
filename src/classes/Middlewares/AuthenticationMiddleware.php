<?php namespace Tranquility\Middlewares;

// PSR standards interfaces
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

// Library classes
use Slim\Psr7\Response;
use OAuth2\Request as OAuthRequest;

// Tranquility classes
use Tranquility\System\Utility as Utility;

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
     * @param Request         $request  PSR-7 request
     * @param RequestHandler  $handler  PSR-15 request handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response {
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
        $response = $handler->handle($request);
        return $response;
    }
}