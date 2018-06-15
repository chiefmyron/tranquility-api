<?php namespace Tranquility\Middlewares;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

/**
 * Checks to make sure that the request and response are both using the 
 * 'application/vnd.api+json' content type.
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 */
class JsonContentTypeMiddleware extends AbstractMiddleware {

    private $_allowedContentType = 'application/vnd.api+json';
    
    /**
     * Checks to make sure that the request and response are both using the 
     * 'application/vnd.api+json' content type.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {
        // Validate that the request's 'Content-Type' header is allowed
        $requestContentTypeHeader = $request->getHeaderLine('Content-Type');
        if ($requestContentTypeHeader != $this->_allowedContentType) {
            // Return error response immediately
            $error = array();
            $error['source'] = ["pointer" => ""];
            $error['status'] = HttpStatus::UnsupportedMediaType;
            $error['title'] = "The 'Content-Type' header in your request must be '".$this->_allowedContentType."'.";
            $error['detail'] = "The 'Content-Type' header for this request was: ".$requestContentTypeHeader;
            $response = $response->withHeader('Content-Type', $this->_allowedContentType);
            return $this->withError($response, $error);
        }

        // Validate that the request's 'Accept' header does not specify any media type parameters
        $requestAcceptHeader = $request->getHeaderLine('Accept');
        $requestAcceptHeaderParts = explode(";", $requestAcceptHeader);
        if (trim($requestAcceptHeaderParts[0]) == $this->_allowedContentType && count($requestAcceptHeaderParts) > 1) {
            // Return error response immediately
            $error = array();
            $error['source'] = ["pointer" => ""];
            $error['status'] = HttpStatus::NotAcceptable;
            $error['title'] = "If the 'Accept' header in your request is '".$this->_allowedContentType."', it must not include any media type parameters.";
            $error['detail'] = "The 'Accept' header for this request was: ".$requestAcceptHeader;
            $response = $response->withHeader('Content-Type', $this->_allowedContentType);
            return $this->withError($response, $error);
        }

        // Call next middleware
        $response = $next($request, $response);

        // Set content type header for response
        $response = $response->withHeader('Content-Type', $this->_allowedContentType);
        return $response;
    }
}