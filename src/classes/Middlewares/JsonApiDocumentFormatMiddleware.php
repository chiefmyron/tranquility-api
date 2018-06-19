<?php namespace Tranquility\Middlewares;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

/**
 * Validate that the body of the request message conforms to the JSON API 
     * document structure.
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 * @see http://jsonapi.org/format/#document-top-level
 */
class JsonApiDocumentFormatMiddleware extends AbstractMiddleware {

    private $_requestMethodsWithBody = array('POST', 'PUT', 'PATCH');
    private $_jsonapiTopLevelMemberNames = array('data', 'errors', 'meta', 'jsonapi', 'links', 'included');
    
    /**
     * Validate that the body of the request message conforms to the JSON API 
     * document structure.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {
        // If the request type should include a body, make sure the high level members conform
        // to the JSON API structure rules
        $method = $request->getMethod();
        if (in_array($method, $this->_requestMethodsWithBody)) {
            // Check that request includes a body
            $body = $request->getParsedBody();
            if ($body == null) {
                $error = array();
                $error['source'] = ["pointer" => ""];
                $error['status'] = HttpStatus::BadRequest;
                $error['title']  = "The request body for this type of request cannot be empty.";
                return $this->withError($response, $error);
            }

            // Check that only valid top-level members have been supplied in the request body
            $topLevelMemberNames = array_keys($body);
            $invalidMemberNames = array_diff($topLevelMemberNames, $this->_jsonapiTopLevelMemberNames);
            if (count($invalidMemberNames) > 0) {
                $error = array();
                $error['source'] = ["pointer" => ""];
                $error['status'] = HttpStatus::BadRequest;
                $error['title']  = "One or more top level member elements in the request body are invalid.";
                $error['detail'] = "Invalid member names are: ".implode(", ", $invalidMemberNames);
                return $this->withError($response, $error);
            }
        }

        // Call next middleware
        $response = $next($request, $response);
        return $response;
    }
}