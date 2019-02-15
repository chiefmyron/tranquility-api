<?php namespace Tranquility\System\JsonApi;

// Yin libraries
use WoohooLabs\Yin\JsonApi\Negotiation\RequestValidator;
use WoohooLabs\Yin\JsonApi\Exception\JsonApiExceptionInterface;
use WoohooLabs\Yin\JsonApi\Exception\RequestBodyInvalidJson;
use WoohooLabs\Yin\JsonApi\Request\JsonApiRequestInterface;

/**
 * Utility class containing useful shortcut methods
 *
 * @package \Tranquility
 * @author  Andrew Patterson <patto@live.com.au>
 */
 
class RequestValidatorJsonApi extends RequestValidator {
    // JSON API body validation details
    private $_requestMethodsWithBody = array('POST', 'PUT', 'PATCH');
    private $_jsonapiTopLevelMemberNames = array('data', 'errors', 'meta', 'jsonapi', 'links', 'included');

    /**
     * Checks to make sure that a body has been included for the required HTTP methods
     *
     * @param JsonApiRequestInterface $request
     * @throws RequestBodyInvalidJsonApi|JsonApiExceptionInterface
     * @return void
     */
    public function validateBodyExistsForMethod(JsonApiRequestInterface $request) {
        // Check that request includes a body
        $body = $request->getBody();
        $method = $request->getMethod();
        
        if (in_array($method, $this->_requestMethodsWithBody) && $body == "") {
            throw $this->exceptionFactory->createRequestBodyInvalidJsonException(
                $request,
                "The request body for this type of request cannot be empty.",
                $this->includeOriginalMessage
            );
        }
    }


    /**
     * Checks that the necessary top-level nodes exist in the request document
     * @throws RequestBodyInvalidJsonApi|JsonApiExceptionInterface
     */
    public function validateBody(JsonApiRequestInterface $request) {
        // If the request does not have a body, no validation required
        $method = $request->getMethod();
        if (in_array($method, $this->_requestMethodsWithBody) == false) {
            return;
        }

        // Get the parsed representation of the request document
        $body = $request->getParsedBody();

        // Validate top level member names
        $topLevelMemberNames = array_keys($body);
        $invalidMemberNames = array_diff($topLevelMemberNames, $this->_jsonapiTopLevelMemberNames);
        if (count($invalidMemberNames) > 0) {
            $errors = [];
            foreach ($invalidMemberNames as $name) {
                $errors[] = 'Invalid top-level member in request message: '.$name;
            }

            throw $this->exceptionFactory->createRequestBodyInvalidJsonApiException(
                $request,
                $errors,
                $this->includeOriginalMessage
            );
        }
    }
}