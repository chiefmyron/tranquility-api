<?php namespace Tranquility\Data\Entities\OAuth;

abstract class AbstractHashableFieldOAuthEntity extends AbstractOAuthEntity {
    protected $hashOptions = ['cost' => 11];

    protected function hashField($value) {
        return password_hash($value, PASSWORD_DEFAULT, $this->hashOptions);
    }

    protected function verifyHashedFieldValue($hashedValue, $value) {
        return password_verify($value, $hashedValue);
    }
}