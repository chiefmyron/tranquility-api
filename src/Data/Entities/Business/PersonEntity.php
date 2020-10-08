<?php namespace Tranquillity\Data\Entities\Business;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquillity\Data\Entities\Business\UserEntity as User;
use Tranquillity\Data\Repositories\Business\GenericRepository;

// Tranquillity class libraries
use Tranquillity\System\Enums\EntityTypeEnum;

class PersonEntity extends AbstractBusinessEntity {
    // Entity properties
    protected $title;
    protected $firstName;
    protected $lastName;
    protected $position;

    // Related entities
    protected $user;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'title'     => ['type' => 'string', 'visibility' => 'public', 'auditable' => true],
        'firstName' => ['type' => 'string', 'visibility' => 'public', 'auditable' => true],
        'lastName'  => ['type' => 'string', 'visibility' => 'public', 'auditable' => true],
        'position'  => ['type' => 'string', 'visibility' => 'public', 'auditable' => true],
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'user' => ['type' => EntityTypeEnum::User, 'class' => User::class, 'visibility' => 'public', 'collection' => false]
    ];

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::Person;
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
        $builder->setCustomRepositoryClass(GenericRepository::class);
        
        // Define table name
        $builder->setTable('bus_people');
        
        // Define fields
        $builder->createField('title', 'string')->nullable()->build();
        $builder->addField('firstName', 'string');
        $builder->addField('lastName', 'string');
        $builder->createField('position', 'string')->nullable()->build();
        
        // Add relationships
        $builder->createOneToOne('user', User::class)->addJoinColumn('userId', 'id')->cascadePersist()->fetchLazy()->build();
    }
}