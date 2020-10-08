<?php namespace Tranquillity\Services\Business;

// Vendor class libraries
use Valitron\Validator;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Framework class libraries
use Tranquillity\Data\Entities\Business\PersonEntity as Person;
use Tranquillity\System\Enums\MessageCodeEnum as MessageCodes;

class PersonService extends AbstractBusinessService {

    /** 
     * Creates an instance of a resource that handles business logic for a data entity
     * 
     * @param  \Doctrine\ORM\EntityManagerInterface  $em         ORM entity manager
     * @param  \Valitron\Validator                   $validator  Validation engine 
     * @return void
     */
    public function __construct(EntityManagerInterface $em, Validator $validator) {
        parent::__construct($em, $validator);

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