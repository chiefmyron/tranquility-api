<?php namespace Tranquillity\System\Exceptions;

class ApplicationException extends \RuntimeException {
    private $httpStatusCode;
    private $errorLevel;
    private $details;

    public function __construct(int $httpStatusCode, int $errorLevel, int $errorCode, string $message, array $details, \Exception $previous = null) {
        $this->httpStatusCode = $httpStatusCode;
        $this->errorLevel = $errorLevel;
        $this->details = $details;

        parent::__construct($message, $errorCode, $previous);
    }

    public function getHttpStatusCode() {
        return $this->httpStatusCode;
    }

    public function getErrorLevel() {
        return $this->errorLevel;
    }

    public function getDetails() {
        return $this->details;
    }

    public function toArray() {
        return $this->details;
    }
}