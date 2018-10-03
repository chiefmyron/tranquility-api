<?php namespace Tranquility\Data\Entities\OAuth;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;

abstract class HashableFieldOAuth extends AbstractEntity {
    protected $hashOptions = ['cost' => 11];

    protected function hashField($value) {
        return password_hash($value, PASSWORD_DEFAULT, $this->hashOptions);
    }

    protected function verifyHashedFieldValue($hashedValue, $value) {
        return password_verify($value, $hashedValue);
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     * @abstract
     */
    abstract public function getPublicFields();
}