<?php namespace Tranquility\Middlewares;

// Yin libraries
use WoohooLabs\Yin\JsonApi\Request\Request as JsonRequest;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;

// Tranquility class libraries
use Tranquility\System\Utility;

/**
 * Validate that the body of the request message conforms to the JSON API 
 * document structure.
 *
 * @package Tranquility\Middleware
 * @author  Andrew Patterson <patto@live.com.au>
 * @see https://www.slimframework.com/docs/v3/concepts/middleware.html
 * @see http://jsonapi.org/format/#document-top-level
 * @see https://github.com/woohoolabs/yin-middleware
 */
class JsonApiRequestValidatorMiddleware extends AbstractMiddleware {

    // Yin request validator
    private $requestValidator;

    // Yin response validator
    private $responseValidator;

    // Yin exception factory
    private $exceptionFactory;

    // Validation mode options
    private $negotiate;
    private $validateQueryParams;
    private $validateJsonRequestBody;
    private $validateJsonResponseBody;

    // Constructor
    public function __construct($requestValidator, $responseValidator, $exceptionFactory, $options) { // $negotiate, $validateQueryParams, $validateJsonBody) {
        // Set validators
        $this->requestValidator = $requestValidator;
        $this->responseValidator = $responseValidator;
        $this->exceptionFactory = $exceptionFactory;

        // Set options
        $this->negotiate = Utility::extractValue($options, 'validateContentNegotiation', true);
        $this->validateQueryParams = Utility::extractValue($options, 'validateQueryParams', true);
        $this->validateJsonRequestBody = Utility::extractValue($options, 'validateRequestBody', true);
        $this->validateJsonResponseBody = Utility::extractValue($options, 'validateResponseBody', true);
    }

    /**
     * Validates the HTTP request against JSON and JSON:API schemas.
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next) {
        // Convert Slim request to JsonApi request
        $jsonRequest = new JsonRequest($request, $this->exceptionFactory);

        // If any of the validations fail, the validator will throw an exception
        try {
            // Run request validations
            if ($this->negotiate) {
                $this->requestValidator->negotiate($jsonRequest);
            }
            if ($this->validateQueryParams) {
                $this->requestValidator->validateQueryParams($jsonRequest);
            }
            if ($this->validateJsonBody) {
                // Validate body exists for required HTTP method types
                $this->requestValidator->validateBodyExistsForMethod($jsonRequest);
                $this->requestValidator->lintBody($jsonRequest);
                $this->requestValidator->validateBody($jsonRequest);
            }
        } catch (JsonApiExceptionInterface $ex) {
            // Generate error response and return immediately
            // TODO: Error document generation
            echo($ex->getMessage());exit();
            return $response;
        }

        // Continue dispatching the request
        $response = $next($request, $response);

        // Validate the response
        try {
            if ($this->validateJsonResponseBody) {
                $this->responseValidator->lintBody($response);
                $this->responseValidator->validateBody($response);
            }
        } catch (JsonApiExceptionInterface $ex) {
            // Generate error response and return immediately
            // TODO: Error document generation
            echo($ex->getMessage());exit();
            return $response;
        }

        return $response;
    }
}