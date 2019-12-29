<?php namespace Tranquility\App\Errors;

// Tranquility class libraries
use Tranquility\System\Enums\HttpStatusCodeEnum as HttpStatusCodes;

class ValidationInvalidQueryParamError extends AbstractError {

    /**
     * Exception thrown if an unexpected query parameter has been provided and cannot be handled.
     *
     * @param integer     $errorCode    Application error code
     * @param string      $description  [Optional] Publicly visible error message
     * @param string      $id           [Optional] Unique identifier for this occurance of the error. If not provided, a unique ID will be generated automatically.
     * @return Tranquility\Services\Errors\AbstractError
     */
    public function __construct(int $errorCode, string $description = null, string $id = null) {
        parent::__construct($errorCode, $description, $id);

        // Set error-specific values
        $this->title = "Invalid query parameter";
        $this->codeHttpStatus = HttpStatusCodes::BadRequest;
        return $this;
    }
}