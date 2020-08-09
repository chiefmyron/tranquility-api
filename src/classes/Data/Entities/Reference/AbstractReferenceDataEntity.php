<?php namespace Tranquility\Data\Entities\Reference;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Entity classes
use Tranquility\Data\Entities\AbstractEntity;
use Tranquility\Data\Repositories\Reference\GenericRepository as GenericReferenceRepository;

abstract class AbstractReferenceDataEntity extends AbstractEntity {
    // Entity properties
    protected $code;
    protected $description;
    protected $ordering;
    protected $effectiveFrom;
    protected $effectiveUntil;
    
    // Field definitions common to all entity types
    protected static $_commonFieldDefinitions = [
        'code'           => ['type' => 'string',  'visibility' => 'public', 'auditable' => false],
        'description'    => ['type' => 'string', 'visibility' => 'public', 'auditable' => false],
        'ordering'       => ['type' => 'integer',  'visibility' => 'public', 'auditable' => false],
        'effectiveFrom'  => ['type' => 'datetime', 'visibility' => 'public', 'auditable' => false],
        'effectiveUntil' => ['type' => 'datetime', 'visibility' => 'public', 'auditable' => false]
    ];

    // Relationship definitions common to all entity types
    protected static $_commonRelationshipDefinitions = [];

    /**
     * Retrieves the type code used to describe the entity throughout the system
     *
     * @return string
     */
    public static function getEntityType() {
        return "";
    }

    /** 
     * Retrieves the an array describing the related entities or entity collections for the entity
     * 
     * @return array
     */
    public static function getPublicRelationships() {
        return array();
    }

    /**
     * Metadata used to define object relationship to database
     *
     * @var \Doctrine\ORM\Mapping\ClassMetadata $metadata  Metadata to be passed to Doctrine
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata) {
        $builder = new ClassMetadataBuilder($metadata);
        // Set as a superclass (no table)
        $builder->setMappedSuperClass();
        $builder->setCustomRepositoryClass(GenericReferenceRepository::class);
        
        // Define fields
        $builder->createField('code', 'string')->makePrimaryKey()->build();
        $builder->addField('description', 'string');
        $builder->addField('ordering', 'integer');
        $builder->addField('effectiveFrom', 'datetime');
        $builder->addField('effectiveUntil', 'datetime');
    }
}