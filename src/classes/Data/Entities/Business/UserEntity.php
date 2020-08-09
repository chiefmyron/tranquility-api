<?php namespace Tranquility\Data\Entities\Business;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\Business\PersonEntity as Person;
use Tranquility\Data\Repositories\Business\UserRepository;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum;

class UserEntity extends AbstractBusinessEntity {
    // Entity properties
    protected $username;
    protected $password;
    protected $timezoneCode;
    protected $localeCode;
    protected $active;
    protected $securityGroupId;
    protected $registeredDateTime;

    // Related entities
    protected $person;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'username'           => ['type' => 'string',   'visibility' => 'public',  'auditable' => true],
        'password'           => ['type' => 'string',   'visibility' => 'private', 'auditable' => false],
        'timezoneCode'       => ['type' => 'string',   'visibility' => 'public',  'auditable' => true],
        'localeCode'         => ['type' => 'string',   'visibility' => 'public',  'auditable' => true],
        'active'             => ['type' => 'boolean',  'visibility' => 'public',  'auditable' => true],
        'securityGroupId'    => ['type' => 'integer',  'visibility' => 'public',  'auditable' => true],
        'registeredDateTime' => ['type' => 'datetime', 'visibility' => 'public',  'auditable' => true],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'person' => ['type' => EntityTypeEnum::Person, 'visibility' => 'public', 'collection' => false]
    ];

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::User;
    }

    /** 
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$_commonFieldDefinitions, self::$_entityFieldDefinitions);
    }

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     */
    public static function getPublicRelationships() {
        return array_merge(self::$_commonRelationshipDefinitions, self::$_entityRelationshipDefinitions);
    }

    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
   public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        
        // Define table name
        $builder->setTable('bus_users');
        $builder->setCustomRepositoryClass(UserRepository::class);
        
        // Define fields
        $builder->addField('username', 'string');
        $builder->addField('password', 'string');
        $builder->addField('timezoneCode', 'string');
        $builder->addField('localeCode', 'string');
        $builder->addField('active', 'boolean');
        $builder->addField('securityGroupId', 'integer');
        $builder->addField('registeredDateTime', 'datetime');
        
        // Define relationships
        $builder->createOneToOne('person', Person::class)->mappedBy('user')->build();
    }

    /**
     * Get the password for the user. 
     * NOTE: Required for OAuth implementation.
     * @see Tranquility\Data\Repositories\BusinessObjects\UserRepository
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Verify that the supplied plaintext password matches the hashed password for this user
     * NOTE: Required for OAuth implementation
     * @see Tranquility\Data\Repositories\BusinessObjects\UserRepository
     *
     * @param string $password Plaintext password
     * @return boolean
     */
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    /**
     * Array representation of the User object
     * NOTE: The field 'user_id' is required for OAuth implementation
     * @see Tranquility\Data\Repositories\BusinessObjects\UserRepository
     *
     * @return array
     */
    public function toArray() {
        $data = parent::toArray();
        $data['user_id'] = $this->id;
        return $data;
    }
}