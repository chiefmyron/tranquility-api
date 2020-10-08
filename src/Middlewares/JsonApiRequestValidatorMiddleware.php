<?php namespace Tranquillity\Middlewares;

// PSR standards interfaces
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

// Library classes
use Slim\Psr7\Response;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequest as JsonRequest;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;

// Application classes
use Tranquillity\System\Utility;

/**
 * Validate that the body of the request message conforms to the JSON API 
 * document structure.
 *
 * @package Tranquillity\Middleware
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
     * Validates the HTTP request and response payloads against JSON and JSON:API schemas.
     *
     * @param ServerRequestInterface   $request  PSR-7 request
     * @param RequestHandlerInterface  $handler  PSR-15 request handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
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
            if ($this->validateJsonRequestBody) {
                // Validate body exists for required HTTP method types
                $this->requestValidator->validateBodyExistsForMethod($jsonRequest);
                $this->requestValidator->validateJsonBody($jsonRequest);
            }
        } catch (JsonApiExceptionInterface $ex) {
            // Generate error response and return immediately
            throw $ex;
        }

        // Continue dispatching the request
        $response = $handler->handle($request);

        // Validate the response
        try {
            if ($this->validateJsonResponseBody) {
                $this->responseValidator->validateJsonBody($response);
            }
        } catch (JsonApiExceptionInterface $ex) {
            // Generate error response and return immediately
            // TODO: Error document generation
            $response = new Response();
            $response->getBody()->write($ex->getMessage());
            return $response;
        }

        return $response;
    }
}