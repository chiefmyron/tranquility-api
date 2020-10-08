<?php namespace Tranquillity\Middlewares;

// PSR standards interfaces
use Psr\Http\Message\ResponseInterface;

// Tranquillity class libraries
use Tranquillity\System\Enums\HttpStatusCodeEnum as HttpStatus;

/**
 * Abstract middleware used to maintain consistent structure for error responses
 *
 * @package Tranquillity\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 */
abstract class AbstractMiddleware {
    private $_jsonapiVersion = "1.0";

    /**
     * Generates a JSON API response payload for a single error
     *
     * @param ResponseInterface $response
     * @param array $error
     * @return void
     */
    protected function withError(ResponseInterface $response, array $error) {
        $data = ["jsonapi" => ["version" => $this->_jsonapiVersion], "errors" => [$error]];
        $response = $response->withJson($data, $error['status']);
        return $response;
    }

    /**
     * Generates a JSON API response payload for a collection of errors
     *
     * @param ResponseInterface $response
     * @param array $errorCollection
     * @param integer $httpResponseCode
     * @return void
     */
    protected function withErrorCollection(ResponseInterface $response, array $errorCollection, int $httpResponseCode) {
        $data = ["jsonapi" => ["version" => $this->_jsonapiVersion], "errors" => $errorCollection];
        $response = $response->withJson($data, $httpResponseCode);
        return $response;
    }

    /**
     * Generates a JSON API response payload for an Exception
     *
     * @param ResponseInterface $response
     * @param \Exception $e
     * @return void
     */
    protected function withException(ResponseInterface $response, \Exception $e) {
        $error = array();
        $error['source'] = ["pointer" => ""];
        $error['status'] = HttpStatus::InternalServerError;
        $error['title'] = "An internal error has occurred within the application.";
        // TODO: Include config settings in constructor, and only show detail here when in debug mode
        $error['detail'] = $e->getMessage()." [Code: ".$e->getCode()."]";
        return $this->withError($response, $error);
    }
}