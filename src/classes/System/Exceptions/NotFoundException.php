<?php namespace Tranquility\System\Exceptions;

use Monolog\Logger;
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatus;

class NotFoundException extends ApplicationException {
    /**
     * Exception thrown if an unexpected query parameter has been provided and cannot be handled.
     *
     * @param integer     $errorCode  Application error code
     * @param string      $message    Publicly visible error message
     * @param array       $details    Array containing additional details relating to the error
     * @param \Exception  $previous   The previous exception (if being rethrown)
     */
    public function __construct(int $errorCode, string $message, string $invalidParameterName, \Exception $previous = null) {
        // Create standard details array for error messaging
        $details = [
            'source' => null,
            'code'   => $errorCode,
            'title'  => 'The requested resource cannot be found',
            'detail' => $message,
            'status' => HttpStatus::NotFound
        ];

        parent::__construct((int)$details['status'], Logger::DEBUG, $errorCode, $message, $details, $previous);
    }
}