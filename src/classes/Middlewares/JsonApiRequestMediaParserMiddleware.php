<?php namespace Tranquility\Middlewares;

/**
 * Custom media type parser to correctly handle 'application/vnd.api+json' media type requests
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/objects/request.html#media-type-parsers
 * @see https://jsonapi.org/format/#content-negotiation
 */
class JsonApiRequestMediaParserMiddleware extends AbstractMiddleware {
    /**
     * Register custom media type parser to correctly handle 
     * 'application/vnd.api+json' media type requests
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {
        $request->registerMediaTypeParser('application/vnd.api+json', function($input) {
            $result = json_decode($input, true);
            if (!is_array($result)) {
                return null;
            }
            return $result;
        });

        return $next($request, $response);
    }
}