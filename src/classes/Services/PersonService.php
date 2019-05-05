<?php namespace Tranquility\Services;

// Tranquility data entities
use Tranquility\Data\Entities\BusinessObjects\PersonBusinessObject as Person;

// Tranquility class libraries
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class PersonService extends AbstractService {

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

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @return string
     */
    public function getEntityClassname() {
        return Person::class;
    }
}