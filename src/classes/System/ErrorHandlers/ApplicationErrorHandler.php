<?php namespace Tranquility\System\ErrorHandlers;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;
use Tranquility\System\Exceptions\ApplicationException;

class ApplicationErrorHandler extends AbstractErrorHandler {
    public function __invoke($request, $response, $exception) {
        // Log error
        $this->logErrorMessage($exception->getErrorLevel(), $exception->getMessage());
        
        // Format output for response
        $responseArray = array();
        $httpStatusCode = HttpStatus::InternalServerError;
        if ($exception instanceof ApplicationException) {
            // Application exceptions are responsible for structuring the 'details' array of the exception correctly
            $httpStatusCode = $exception->getHttpStatusCode();
            $responseArray = [
                'errors' => [$exception->getDetails()]
            ];
        } else {
            // Fallback handler for non-application exceptions
            $httpStatusCode = HttpStatus::InternalServerError;
            $responseArray = [
                'errors' => [
                    [
                        'status' => HttpStatus::InternalServerError,
                        'title' => "An internal error has occurred within the application.",
                        'detail' => $exception->getMessage()." [Code: ".$exception->getCode()."]"
                    ]
                ]
            ];
        }
        
        return $response->withJson($responseArray, $httpStatusCode);
    }
}