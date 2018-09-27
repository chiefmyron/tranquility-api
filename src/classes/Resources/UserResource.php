<?php namespace Tranquility\Resources;

use \Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

class UserResource extends AbstractResource {

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Include standard entity validation rules
        parent::registerValidationRules();

        // Add validation rules specific for this resource
        $this->validationRuleGroups['default'][] = array('field' => 'username', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'timezoneCode', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'localeCode', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'active', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'active', 'ruleType' => 'boolean', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'securityGroupId', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'securityGroupId', 'ruleType' => 'integer', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
    }

    /**
     * Returns the classname for the Entity object associated with this instance of the resource
     * 
     * @return string
     */
    public function getEntityClassname() {
        return User::class;
    }
}