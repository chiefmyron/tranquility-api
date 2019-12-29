<?php namespace Tranquility\Data\Entities\HistoricalBusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository;
use Tranquility\Data\Entities\HistoricalBusinessObjects\PersonHistoricalBusinessObject as HistoricalPerson;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;
use Tranquility\System\Enums\EntityRelationshipTypeEnum as EntityRelationshipTypeEnum;

class AccountHistoricalBusinessObject extends AbstractHistoricalBusinessObject {
    // Entity type
    protected $type = EntityTypeEnum::Account;
    protected static $historicalEntityClass = HistoricalPerson::class;

    // Entity properties
    protected $name;

    // Define the set of fields that are publicly accessible
    protected static $publicFields = array(
        'name'
    );

    // Define the set of related entities or entity collections that are publicly available
    protected static $publicRelationships = array(
        //'people' => ['entityType' => EntityTypeEnum::Person, 'relationshipType' => EntityRelationshipTypeEnum::Collection]
    );

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
        $builder->setTable('entity_accounts');
        $builder->setCustomRepositoryClass(BusinessObjectRepository::class);
        
        // Define fields
        $builder->addField('name', 'string');
        
        // Add relationships
        //$builder->createOneToMany('contacts', Contact::class)->mappedBy('account')->orphanRemoval(true)->cascadePersist()->cascadeRemove()->build();
    }
}