<?php namespace Tranquility\Middlewares;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

/**
 * Abstract middleware used to maintain consistent structure for error responses
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 */
abstract class AbstractMiddleware {
    private $_jsonapiVersion = "1.0";

    /**
     * Generates a JSON API response payload for a single error
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $error
     * @return void
     */
    protected function withError(\Psr\Http\Message\ResponseInterface $response, array $error) {
        $data = ["jsonapi" => ["version" => $this->_jsonapiVersion], "errors" => [$error]];
        $response = $response->withJson($data, $error['status']);
        return $response;
    }

    /**
     * Generates a JSON API response payload for a collection of errors
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array $errorCollection
     * @param integer $httpResponseCode
     * @return void
     */
    protected function withErrorCollection(\Psr\Http\Message\ResponseInterface $response, array $errorCollection, int $httpResponseCode) {
        $data = ["jsonapi" => ["version" => $this->_jsonapiVersion], "errors" => $errorCollection];
        $response = $response->withJson($data, $httpResponseCode);
        return $response;
    }

    /**
     * Generates a JSON API response payload for an Exception
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Exception $e
     * @return void
     */
    protected function withException(\Psr\Http\Message\ResponseInterface $response, \Exception $e) {
        $error = array();
        $error['source'] = ["pointer" => ""];
        $error['status'] = HttpStatus::InternalServerError;
        $error['title'] = "An internal error has occurred within the application.";
        // TODO: Include config settings in constructor, and only show detail here when in debug mode
        $error['detail'] = $e->getMessage()." [Code: ".$e->getCode()."]";
        return $this->withError($response, $error);
    }
}