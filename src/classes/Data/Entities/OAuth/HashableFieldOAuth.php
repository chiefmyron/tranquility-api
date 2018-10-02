<?php namespace Tranquility\Data\Entities\OAuthEntities;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;

class HashableFieldOAuth extends AbstractEntity {
    protected $hashOptions = ['cost' => 11];

    protected function hashField($value) {
        return password_hash($value, PASSWORD_DEFAULT, $this->hashOptions);
    }

    protected function verifyHashedFieldValue($hashedValue, $value) {
        return password_verify($value, $hashedValue);
    }
}