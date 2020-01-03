<?php namespace Tranquility\Validators;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

class EntityExistsValidator extends AbstractValidator {
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
     * Actual valididation rule - check to see that specified entity exists
     *
     * @param string $field
     * @param string $value
     * @param mixed $params
     * @param array $fields
     * @return void
     */
    public function validate($field, $value, $params, array $fields) {
        // Try to find specified entity
        // If the entity type has been specified as a parameter, use this to restrict the search
        $search = array("id" => $value);
        $entity = $this->getRepository($params[0])->findBy($search);

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