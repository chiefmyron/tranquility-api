<?php declare(strict_types=1);
namespace Tranquillity\Middlewares;

// PSR standards interfaces
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Library classes
use OAuth2\Server as OAuth2Server;
use OAuth2\Request as OAuthRequest;

// Application classes
use Tranquillity\System\Utility as Utility;

/**
 * Middleware to ensure only authenticated users can access protected routes
 *
 * @package Tranquillity\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 */
class AuthenticationMiddleware extends AbstractMiddleware {
    // OAuth server instance
    private $server;

    public function __construct(OAuth2Server $server) {
        $this->server = $server;
    }

    /**
     * Validate that the request contains a valid access token
     *
     * @param ServerRequestInterface         $request  PSR-7 request
     * @param RequestHandler  $handler  PSR-15 request handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
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