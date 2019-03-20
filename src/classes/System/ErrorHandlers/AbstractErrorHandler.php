<?php namespace Tranquility\System\ErrorHandlers;

abstract class AbstractErrorHandler {
    protected $logger;
    protected $displayErrors;

    public function __construct($logger = null, $displayErrors = false) {
        $this->logger = $logger;
        $this->displayErrors = $displayErrors;
    }

    abstract public function __invoke($request, $response, $exception);

    public function logErrorMessage($level, $message, $context = array()) {
        if (isset($this->logger)) {
            $this->logger->log($level, $message, $context);
        } else {
            error_log($message);
        }
    }
}