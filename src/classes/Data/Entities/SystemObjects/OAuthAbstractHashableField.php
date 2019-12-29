<?php namespace Tranquility\Data\Entities\SystemObjects;

abstract class OAuthAbstractHashableField extends AbstractSystemObject {
    protected $hashOptions = ['cost' => 11];

    protected function hashField($value) {
        return password_hash($value, PASSWORD_DEFAULT, $this->hashOptions);
    }

    protected function verifyHashedFieldValue($hashedValue, $value) {
        return password_verify($value, $hashedValue);
    }
}