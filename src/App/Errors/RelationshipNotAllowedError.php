<?php namespace Tranquillity\App\Errors;

// Tranquillity class libraries
use Tranquillity\System\Enums\HttpStatusCodeEnum as HttpStatusCodes;

class RelationshipNotAllowedError extends AbstractError {

    /**
     * Exception thrown if the specified data entity cannot be gound
     *
     * @param integer     $errorCode    Application error code
     * @param string      $description  [Optional] Publicly visible error message
     * @param string      $id           [Optional] Unique identifier for this occurance of the error. If not provided, a unique ID will be generated automatically.
     * @return Tranquillity\Services\Errors\AbstractError
     */
    public function __construct(int $errorCode, string $description = null, string $id = null) {
        parent::__construct($errorCode, $description, $id);

        // Set error-specific values
        $this->title = "Relationship not allowed";
        $this->codeHttpStatus = HttpStatusCodes::Forbidden;
        return $this;
    }
}