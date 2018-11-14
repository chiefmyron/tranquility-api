<?php namespace Tranquility\Resources;

// ORM class libraries
use Carbon\Carbon as Carbon;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility data entities
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class UserResource extends AbstractResource {

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Include standard entity validation rules
        parent::registerValidationRules();

        // Common validation rules for a User entity
        $this->validationRuleGroups['default'][] = array('field' => 'username', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'timezoneCode', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        // TODO: Check timezone code is a valid value
        $this->validationRuleGroups['default'][] = array('field' => 'localeCode', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        // TODO: Check locale code is a valid value
        $this->validationRuleGroups['default'][] = array('field' => 'active', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'active', 'ruleType' => 'boolean', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'securityGroupId', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'securityGroupId', 'ruleType' => 'integer', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);

        // Validation rules for a User that should be run when creating the entity
        $this->validationRuleGroups['create'][] = array('field' => 'password', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        // TODO: Check username is unique
        // TODO: Check password strength
    }

    /**
     * Create a new record for a User entity. Requires special logic to hash the user's password securely.
     * 
     * @var  array       $data   Data used to create the new entity record
     * @var  AuditTrail  $audit  Audit trail object 
     * @return Tranquility\Data\Entities\AbstractEntity
     */
    public function create(array $data, AuditTrail $audit) {
        // Get input attributes from data
        $attributes = $data['attributes'];

        // Replace plaintext password with hashed version
        $password = Utility::extractValue($attributes, 'password', '');
        if ($password !== '') {
            $hashOptions = ['cost' => 11];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT, $hashOptions);
            $data['attributes']['password'] = $passwordHash;
        }

        // Set registered timestamp to the current date
        $data['attributes']['registeredDateTime'] = Carbon::now();

        // Continue with creating the entity
        return parent::create($data, $audit);
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