<?php namespace Tranquility\Validators;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;

// Tranquility data entities
use Tranquility\Services\UserService;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class UniqueUsernameValidator extends AbstractValidator {
    /**
     * User service
     * 
     * @var Tranquility\Services\UserService
     */
    protected $service;

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  Tranquility\Services\UserService  $service  User service
     * @return void
     */
    public function __construct(UserService $service) {
        // Use User service for validation of usernames
        $this->service = $service;
    }

    /**
     * Actual valididation rule - check to see that specified entity exists
     *
     * @param string $field
     * @param string $value
     * @param mixed $params
     * @param array $fields
     * @return void
     */
    public function validate($field, $value, $params, array $fields) {
        // Check if this is already an existing user
        $existingUser = false;
        if (Utility::extractValue($params, 0, '') == 'existing') {
            $existingUser = true;
        }

        // Try to find specified entity by username
        $results = $this->service->findBy('username', $value);
        if (count($results) <= 0 && $existingUser == false) {
            // Creating a new user and username does not exist
            return true;
        } elseif (count($results) == 1 && $existingUser == true && $results[0]->id == $fields['id']) {
            // Updating an existing user and the username only exists once (i.e. for the current user)
            return true;
        }

        // Username already in use
        return false;
    }
}