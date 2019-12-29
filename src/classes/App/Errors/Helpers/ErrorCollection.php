<?php namespace Tranquility\App\Errors\Helpers;

use Countable;
use Tranquility\App\Errors\AbstractError;

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
        return "";
    }

    public function toArray() {
        $errors = [];
        foreach ($this->errors as $error) {
            $errors[] = $error->toArray();
        }
        return $errors;
    }
}