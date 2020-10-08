<?php namespace Tranquillity\Services\Business;

// Vendor class libraries
use Carbon\Carbon as Carbon;
use Valitron\Validator;
use Doctrine\ORM\EntityManagerInterface as EntityManagerInterface;

// Framework class libraries
use Tranquillity\Data\Entities\Business\UserEntity as User;
use Tranquillity\Data\Entities\Reference\TimezoneReferenceDataEntity as TimezoneReferenceData;
use Tranquillity\Data\Entities\Reference\LocaleReferenceDataEntity as LocaleReferenceData;
use Tranquillity\System\Utility as Utility;
use Tranquillity\System\Enums\MessageCodeEnum as MessageCodes;

class UserService extends AbstractBusinessService {

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
        $this->entityClassname = User::class;
    }

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
     * @return Tranquillity\Data\Entities\AbstractEntity
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
     * @var  string  $id    Record ID for the entity to update
     * @var  array   $data  New data to update against the existing record
     * @return  Tranquillity\Data\Entities\AbstractEntity
     */
    public function update(string $id, array $payload) {
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
}