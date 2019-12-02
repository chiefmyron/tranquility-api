<?php namespace Tranquility\Services;

// Utility libraries
use Carbon\Carbon as Carbon;

// ORM class libraries
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility data entities
use Tranquility\Data\Entities\BusinessObjects\PersonBusinessObject as Person;

// Tranquility class libraries
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class PersonService extends AbstractService {

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $prefix  String to use as database table name prefix
     * @return void
     */
    public function __construct(EntityManagerInterface $em) {
        parent::__construct($em);

        // Set the entity classname for this service
        $this->entityClassname = Person::class;
    }

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Common validation rules for a Person entity
        $this->validationRuleGroups['default'][] = array('field' => 'firstName', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'lastName', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
    }
}