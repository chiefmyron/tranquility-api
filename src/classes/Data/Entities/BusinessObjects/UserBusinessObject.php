<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\System\Utility as Utility;
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;

// Entity repository
use Tranquility\Data\Repositories\BusinessObjects\UserBusinessObjectRepository;
use Tranquility\Data\Entities\HistoricalBusinessObjects\UserHistoricalBusinessObject as HistoricalUser;

class UserBusinessObject extends AbstractBusinessObject {
    // Entity type
    protected $type = EntityTypeEnum::User;
    protected static $historicalEntityClass = HistoricalUser::class;

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

    // Related extension data objects
    protected $userTokens;

    // Define the set of fields that are publically accessible
    protected static $publicFields = array(
        'username',
        'timezoneCode',
        'localeCode',
        'active',
        'securityGroupId',
        'registeredDateTime'
    );

    /**
     * Create a new instance of the entity
     *
     * @var array $data     [Optional] Initial values for entity fields
     * @var array $options  [Optional] Configuration options for the object
     * @return void
     */
    public function __construct($data = array(), $options = array()) {
        // Perform initial entity construction
        parent::__construct($data, $options);

        // If the password has been provided, set property value now
        // Not handled as part of default construction as password should not be a public field
        if ($data instanceof UserBusinessObject) {
            $this->password = $data->getPassword();
        }
    }

    /**
     * Returns the name of the class used to model the historical records for this business object
     *
     * @return string
     */
    public static function getHistoricalEntityClass() {
        return self::$historicalEntityClass;
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
        $builder->setTable('entity_users');
        $builder->setCustomRepositoryClass(UserBusinessObjectRepository::class);
        
        // Define fields
        $builder->addField('username', 'string');
        $builder->addField('password', 'string');
        $builder->addField('timezoneCode', 'string');
        $builder->addField('localeCode', 'string');
        $builder->addField('active', 'boolean');
        $builder->addField('securityGroupId', 'integer');
        $builder->addField('registeredDateTime', 'datetime');
        
        // Add relationships
        //$builder->createOneToOne('person', Person::class)->mappedBy('user')->build();
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$entityPublicFields, self::$publicFields);
    }

    /**
     * Get the password for the user. 
     * NOTE: Required for OAuth implementation.
     * @see Tranquility\Data\Repositories\BusinessObjects\UserBusinessObjectRepository
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Verify that the supplied plaintext password matches the hashed password for this user
     * NOTE: Required for OAuth implementation
     * @see Tranquility\Data\Repositories\BusinessObjects\UserBusinessObjectRepository
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
     * @see Tranquility\Data\Repositories\BusinessObjects\UserBusinessObjectRepository
     *
     * @return array
     */
    public function toArray() {
        $data = parent::toArray();
        $data['user_id'] = $this->id;
        return $data;
    }
}