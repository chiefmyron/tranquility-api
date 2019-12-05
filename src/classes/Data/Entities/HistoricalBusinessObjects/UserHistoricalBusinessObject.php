<?php namespace Tranquility\Data\Entities\HistoricalBusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Repositories\BusinessObjects\UserBusinessObjectRepository;
use Tranquility\Data\Entities\BusinessObjects\UserBusinessObject as User;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

class UserHistoricalBusinessObject extends AbstractHistoricalBusinessObject {
    // Entity type
    protected $type = EntityTypeEnum::User;

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

    // Define the set of fields that are publicly accessible
    protected static $publicFields = array(
        'username',
        'timezoneCode',
        'localeCode',
        'active',
        'securityGroupId',
        'registeredDateTime'
    );

    // Define the set of related entities or entity collections that are publicly available
    protected static $publicRelationships = array(
        //'person' => ['entityType' => EntityTypeEnum::Person, 'relationshipType' => EntityRelationshipTypeEnum::Single]
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
        if ($data instanceof User) {
            $this->password = $data->getPassword();
        }
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
        $builder->setTable('history_entity_users');
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
     * Retrieves the set of publicly accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$entityPublicFields, self::$publicFields);
    }

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     */
    public static function getPublicRelationships() {
        return array_merge(self::$entityPublicRelationships, self::$publicRelationships);
    }
}