<?php namespace Tranquility\Data\Entities\BusinessObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\System\Enums\EntityTypeEnum as EntityTypeEnum;

// Entity repository
use Tranquility\Data\Repositories\BusinessObjects\BusinessObjectRepository;
use Tranquility\Data\Entities\BusinessOBjects\UserBusinessObject as User;
use Tranquility\Data\Entities\HistoricalBusinessObjects\PersonHistoricalBusinessObject as HistoricalPerson;

class PersonBusinessObject extends AbstractBusinessObject {
    // Entity type
    protected $type = EntityTypeEnum::Person;
    protected static $historicalEntityClass = HistoricalPerson::class;

    // Entity properties
    protected $title;
    protected $firstName;
    protected $lastName;
    protected $position;

    // Related entities
    protected $user;

    // Define the set of fields that are publically accessible
    protected static $publicFields = array(
        'title',
        'firstName',
        'lastName',
        'position'
    );

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
        $builder->setTable('entity_people');
        $builder->setCustomRepositoryClass(BusinessObjectRepository::class);
        
        // Define fields
        $builder->createField('title', 'string')->nullable()->build();
        $builder->addField('firstName', 'string');
        $builder->addField('lastName', 'string');
        $builder->createField('position', 'string')->nullable()->build();
        
        // Add relationships
        $builder->createOneToOne('user', User::class)->inversedBy(EntityTypeEnum::Person)->addJoinColumn('userId','id')->orphanRemoval(true)->cascadePersist()->cascadeRemove()->fetchLazy()->build();
    }

    /** 
     * Retrieves the set of publically accessible fields for the entity
     * 
     * @return array
     */
    public static function getPublicFields() {
        return array_merge(self::$entityPublicFields, self::$publicFields);
    }
}