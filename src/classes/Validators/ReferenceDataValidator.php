<?php namespace Tranquility\Validators;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

class ReferenceDataValidator extends AbstractValidator {
    /**
     * Doctrine Entity Manager
     * 
     * @var Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $prefix  String to use as database table name prefix
     * @return void
     */
    public function __construct(EntityManagerInterface $em) {
        // Create entity manager for interface to repositories and entities
        $this->entityManager = $em;
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
        // Check to see that the reference data value is valid and current
        $entity = $this->getRepository($params[0])->findByCode($value);

        if ($entity == null) {
            return false;
        }
        return true;
    }

    /**
     * Get the Repository associated with the Entity for this validator
     * 
     * @return Tranquility\Data\Repositories\Repository
     */
    protected function getRepository($entityType) {
        return $this->entityManager->getRepository($entityType);
    }
}