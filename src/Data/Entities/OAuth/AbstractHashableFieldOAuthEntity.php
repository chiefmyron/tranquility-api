<?php declare(strict_types=1);
namespace Tranquillity\Data\Entities\OAuth;

abstract class AbstractHashableFieldOAuthEntity extends AbstractOAuthEntity {
    
    /**
     * @var array
     */
    protected $hashOptions = ['cost' => 11];

    /**
     * Use built-in PHP password hashing to hash the supplied value
     *
     * @param string $value
     * @return string
     */
    protected function hashField(string $value) : string {
        return password_hash($value, PASSWORD_DEFAULT, $this->hashOptions);
    }

    /**
     * Checks if the given hash matches the value
     *
     * @param string $hashedValue
     * @param string $value
     * @return bool
     */
    protected function verifyHashedFieldValue(string $hashedValue, string $value) : bool {
        return password_verify($value, $hashedValue);
    }
}