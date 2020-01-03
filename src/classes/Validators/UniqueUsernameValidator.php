<?php namespace Tranquility\Validators;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\EntityTypeEnum;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

class UniqueUsernameValidator extends AbstractValidator {
    /**
     * Doctrine Entity Manager
     * 
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /** 
     * Constructor
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $em  ORM entity manager
     * @return void
     */
    public function __construct(EntityManagerInterface $em) {
        // Create entity manager for interface to repositories and entities
        $this->entityManager = $em;
    }

    /**
     * Actual valididation rule - check to see that spcecified username is unique
     *
     * @param string $field
     * @param string $value
     * @param mixed $params
     * @param array $fields
     * @return void
     */
    public function validate($field, $value, $params, array $fields) {
        // Get user repository
        $userClassname = EntityTypeEnum::getEntityClassname(EntityTypeEnum::User);
        $repository = $this->entityManager->getRepository($userClassname);

        // Check if this is already an existing user
        $existingUser = false;
        if (Utility::extractValue($params, 0, '') == 'existing') {
            $existingUser = true;
        }

        // Try to find user entity by username
        $searchOptions = ['username' => $value, 'deleted' => false];
        $results = $repository->findBy($searchOptions);
        if (count($results) <= 0) {
            // Username does not exist
            return true;
        } elseif (count($results) == 1 && $existingUser == true && $results[0]->id == $fields['id']) {
            // Updating an existing user and the username only exists once (i.e. for the current user)
            return true;
        }

        // Username already in use
        return false;
    }
}