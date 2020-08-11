<?php namespace Tranquility\Data\Entities\Business;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\Business\PersonEntity as Person;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum;

class AccountEntity extends AbstractBusinessEntity {
    // Entity properties
    protected $name;

    // Related entities
    protected $people;

    // Entity field definitions
    protected static $_entityFieldDefinitions = [
        'name' => ['type' => 'string', 'visibility' => 'public', 'auditable' => true]
    ];

    // Entity relationship definitions
    protected static $_entityRelationshipDefinitions = [
        'people'    => ['type' => EntityTypeEnum::Person,  'visibility' => 'public', 'collection' => true]
    ];

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return EntityTypeEnum::Account;
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
        $builder->setTable('bus_accounts');
        
        // Define fields
        $builder->addField('name', 'string');
        
        // Add relationships
        $builder->createOneToMany('people', Person::class)->mappedBy('account')->orphanRemoval(true)->cascadePersist()->cascadeRemove()->build();
    }
}