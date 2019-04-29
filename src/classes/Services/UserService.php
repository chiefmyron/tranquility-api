<?php namespace Tranquility\Services;

// ORM class libraries
use Carbon\Carbon as Carbon;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Tranquility data entities
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;
use Tranquility\Data\Entities\SystemObjects\AuditTrailSystemObject as AuditTrail;
use Tranquility\Data\Entities\ReferenceDataObjects\TimezoneReferenceDataObject as TimezoneReferenceData;
use Tranquility\Data\Entities\ReferenceDataObjects\LocaleReferenceDataObject as LocaleReferenceData;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\MessageCodeEnum as MessageCodes;

class UserService extends AbstractService {

    /**
     * Registers the validation rules that are specific to this entity.
     * 
     * @return void
     */
    public function registerValidationRules() {
        // Common validation rules for a User entity
        $this->validationRuleGroups['default'][] = array('field' => 'username', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'timezoneCode', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'timezoneCode', 'ruleType' => 'referenceDataCode', 'params' => [TimezoneReferenceData::class], 'message' => MessageCodes::ValidationInvalidCodeValue);
        $this->validationRuleGroups['default'][] = array('field' => 'localeCode', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'localeCode', 'ruleType' => 'referenceDataCode', 'params' => [LocaleReferenceData::class], 'message' => MessageCodes::ValidationInvalidCodeValue);
        $this->validationRuleGroups['default'][] = array('field' => 'active', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'active', 'ruleType' => 'boolean', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'securityGroupId', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);
        $this->validationRuleGroups['default'][] = array('field' => 'securityGroupId', 'ruleType' => 'integer', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);

        // Validation rules for a User that should be run when creating the entity
        $this->validationRuleGroups['create'][] = array('field' => 'username', 'ruleType' => 'uniqueUsername', 'params' => [], 'message' => MessageCodes::ValidationUsernameInUse);
        $this->validationRuleGroups['create'][] = array('field' => 'password', 'ruleType' => 'required', 'params' => [], 'message' => MessageCodes::ValidationMandatoryFieldMissing);

        // Validation rules for a User that should be run when updating the entity
        $this->validationRuleGroups['update'][] = array('field' => 'username', 'ruleType' => 'uniqueUsername', 'params' => ['existing'], 'message' => MessageCodes::ValidationUsernameInUse);
        $this->validationRuleGroups['update'][] = array('field' => 'id', 'ruleType' => 'entityExists', 'message' => MessageCodes::RecordNotFound, 'params' => [User::class]);
    }

    /**
     * Create a new record for a User entity. Requires special logic to hash the user's password securely.
     * 
     * @var  array  $payload  Data used to create the new entity record
     * @return Tranquility\Data\Entities\AbstractEntity
     */
    public function create(array $payload) {
        // Get input attributes from data
        $data = Utility::extractValue($payload, 'data', array());
        $attributes = Utility::extractValue($data, 'attributes', array());

        // Replace plaintext password with hashed version
        $password = Utility::extractValue($attributes, 'password', '');
        if ($password !== '') {
            $hashOptions = ['cost' => 11];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT, $hashOptions);
            $attributes['password'] = $passwordHash;
            $payload['data']['attributes']['password'] = $passwordHash;
        }

        // Set registered timestamp to the current date
        $attributes['registeredDateTime'] = Carbon::now();

        // Continue with creating the entity
        $data['attributes'] = $attributes;
        $payload['data'] = $data;
        return parent::create($payload);
    }

    /**
     * Update an existing record for the User entity. Requires special logic to update the user's password.
     * 
     * @var  int         $id     Record ID for the entity to update
     * @var  array       $data   New data to update against the existing record
     * @return  Tranquility\Data\Entities\AbstractEntity
     */
    public function update(int $id, array $payload) {
        // Get input attributes from data
        $data = Utility::extractValue($payload, 'data', array());
        $attributes = Utility::extractValue($data, 'attributes', array());

        // Replace plaintext password with hashed version
        $password = Utility::extractValue($attributes, 'password', '');
        if ($password !== '') {
            $hashOptions = ['cost' => 11];
            $passwordHash = password_hash($password, PASSWORD_DEFAULT, $hashOptions);
            $attributes['password'] = $passwordHash;
        }

        // Continue with updating the entity
        $data['attributes'] = $attributes;
        $payload['data'] = $data;
        return parent::update($id, $payload);
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