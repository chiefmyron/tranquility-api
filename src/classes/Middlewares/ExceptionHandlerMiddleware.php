<?php namespace Tranquility\Middlewares;

/**
 * Exception handler at the top of the request processing stack to handle
 * any other internal exceptions thrown by the application.
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 */
class ExceptionHandlerMiddleware extends AbstractMiddleware {

    /**
     * Exception handler at the top of the request processing stack to handle
     * any other internal exceptions thrown by the application.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {
        try {
            return $next($request, $response);
        } catch (\Exception $e) {
            return $this->withException($response, $e);
        }
    }
}