<?php namespace Tranquillity\App\Errors\Helpers;

use Countable;
use Tranquillity\App\Errors\AbstractError;
use Tranquillity\System\Enums\HttpStatusCodeEnum;

class ErrorCollection implements Countable {
    private $errors;

    public function __construct() {
        $this->errors = [];
    }

    public function count() {
        return count($this->errors);
    }

    public function addError(AbstractError $error) {
        $this->errors[] = $error;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getHttpStatusCode() {
        $numErrors = count($this->errors);
        if ($numErrors <= 0) {
            return "";
        } elseif ($numErrors == 1) {
            $error = $this->errors[0]->toArray();
            return $error['status'];
        } else {
            $statusCode = 0;
            foreach ($this->errors as $error) {
                $errorArray = $error->toArray();
                if ($errorArray['status'] > $statusCode) {
                    $statusCode = $errorArray['status'];
                }
            }
            return $statusCode;
        }
    }

    public function toArray() {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = $error->toArray();
        }
        return $errors;
    }
}