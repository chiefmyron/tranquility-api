<?php namespace Tranquility\Data\Entities\ReferenceDataObjects;

// ORM class libraries
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

// Tranquility class libraries
use Tranquility\Data\Entities\AbstractEntity as AbstractEntity;

// Entity repository
use Tranquility\Data\Repositories\ReferenceDataObjects\ReferenceDataObjectRepository;

abstract class AbstractReferenceDataObject extends AbstractEntity {
    // Entity properties
    protected $code;
    protected $description;
    protected $ordering;
    protected $effectiveFrom;
    protected $effectiveUntil;

    // Define the set of fields that are publicly accessible
    protected $referenceDataPublicFields = array(
        'code',
        'description',
        'ordering',
        'effectiveFrom',
        'effectiveUntil'
    );

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
        $builder->setCustomRepositoryClass(ReferenceDataObjectRepository::class);
        
        // Define fields
        $builder->createField('code', 'string')->isPrimaryKey()->build();
        $builder->addField('description', 'string');
        $builder->addField('ordering', 'integer');
        $builder->addField('effectiveFrom', 'datetime');
        $builder->addField('effectiveUntil', 'datetime');
    }
}